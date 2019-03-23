<?php
/**
 Copyright 2014 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

namespace com_brucemyers\CategoryWatchlistBot;

use PDO;
use com_brucemyers\Util\MySQLDate;
use com_brucemyers\Util\CommonRegex;
use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\TemplateParamParser;

class CategoryLinksDiff
{
	var $outputdir;
	var $asof;
	var $serviceMgr;
	var $categoryNS;

    /**
     * Constructor
     *
     * @param ServiceManager $serviceMgr
     * @param string $outputdir
     * @param date $asof
     */
     public function __construct(ServiceManager $serviceMgr, $outputdir, $asof)
    {
    	$this->serviceMgr = $serviceMgr;
    	$this->outputdir = $outputdir;
    	$this->asof = MySQLDate::toMySQLDatetime($asof);
    }

	/**
     * Process a wiki.
     *
     * @param string $wikiname
     * @param array $wikidata
     * @return int Category count
     */
    function processWiki($wikiname, $wikidata)
    {
    	$this->categoryNS = $wikidata['catNS'];
    	$wiki = $this->serviceMgr->getMediaWiki($wikidata['domain']);
    	$dbh_tools = $this->serviceMgr->getDBConnection('tools');

    	$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_diffs` (
		  	   `id` int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
    		   `diffdate` timestamp,
		       `plusminus` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			   `pagetitle` varchar(255) binary NOT NULL,
		       `cat_template` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			   `category` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			   `flags` tinyint NOT NULL DEFAULT 0
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $dbh_tools->exec($sql);

        // Add the wiki table entry if needed
    	$sth = $dbh_tools->prepare("SELECT * FROM wikis WHERE wikiname = ?");
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if (! $sth->fetch(PDO::FETCH_ASSOC)) {
    		$sth = $dbh_tools->prepare('INSERT INTO wikis (wikiname, wikititle, wikidomain, lang) VALUES (?,?,?,?)');
    		$sth->execute(array($wikiname, $wikidata['title'], $wikidata['domain'], $wikidata['lang']));
    		$sth = null;
    	}

    	// Recalc oldest 5 category trees if catcount >= 0 and lastrecalc < 7 days ago
    	$sth = $dbh_tools->prepare('SELECT id FROM querys WHERE wikiname = ? AND catcount >= 0 AND lastrecalc < DATE_SUB(?, INTERVAL 1 WEEK) ORDER BY lastrecalc LIMIT 5');
   		$sth->bindParam(1, $wikiname);
   		$sth->bindParam(2, $this->asof);

    	$sth->execute();

    	$results = $sth->fetchAll(PDO::FETCH_ASSOC);
    	$ids = array();
    	foreach ($results as $row) {
    		$ids[] = $row['id'];
    	}
    	$sth = null;

    	if (! empty($ids)) {
    		$ids = implode(',', $ids);

    		$sth = $dbh_tools->prepare("UPDATE querys SET catcount = ? WHERE id IN ($ids)");
    		$sth->bindValue(1, QueryCats::CATEGORY_COUNT_RECALC);
    		$sth->execute();
    		$sth = null;
    	}

    	// Calc category tress for QueryCats::CATEGORY_COUNT_RECALC and QueryCats::CATEGORY_COUNT_UNKNOWN
    	$dbh_tools2 = $this->serviceMgr->getDBConnection('tools');
    	$querycats = new QueryCats($wiki, $dbh_tools2);

    	$sth = $dbh_tools->prepare('SELECT id, params, catcount FROM querys WHERE wikiname = ? AND catcount IN (?,?)');
    	$sth->bindParam(1, $wikiname);
    	$sth->bindValue(2, QueryCats::CATEGORY_COUNT_RECALC);
    	$sth->bindValue(3, QueryCats::CATEGORY_COUNT_UNKNOWN);
    	$sth->execute();
    	$sth->setFetchMode(PDO::FETCH_ASSOC);

    	while ($row = $sth->fetch()) {
    		$id = $row['id'];
    		$params = unserialize($row['params']);
    		$catcount = $row['catcount'];

    		$cats = $querycats->calcCats($params, $catcount == QueryCats::CATEGORY_COUNT_RECALC);
    		$catcount = $cats['catcount'];
    		$querycats->saveCats($id, $cats['cats']);

    		$isth = $dbh_tools2->prepare("UPDATE querys SET catcount = $catcount, lastrecalc = ? WHERE id = $id");
    		$isth->bindParam(1, $this->asof);
    		$isth->execute();
    		$isth = null;
    	}

    	$sth->closeCursor();
    	$sth = null;
    	$dbh_tools2 = null;

    	// Get the current rev_id
    	$ret = $wiki->getList('recentchanges', ['rclimit' => 1]);
    	$cur_rev_id = (int)$ret['query']['recentchanges'][0]['revid'];
    	$cur_timestamp = MediaWiki::ISO8601TimestampToUnixTimestamp($ret['query']['recentchanges'][0]['timestamp']);

    	// Limit to 3 hours max revisions
    	$prev_timestamp = strtotime('-3 hours', $cur_timestamp);
    	$prev_timestamp = MediaWiki::unixTimestampToISO8601Timestamp($prev_timestamp);

    	$ret = $wiki->getList('recentchanges', ['rclimit' => 1, 'rcstart' => $prev_timestamp]);
    	$prev_rev_id = (int)$ret['query']['recentchanges'][0]['revid'];

    	// Get the previous rev_id
    	$sth = $dbh_tools->prepare('SELECT rev_id FROM runs WHERE wikiname = ? ORDER BY rundate DESC LIMIT 1');
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    		$run_rev_id = (int)$row['rev_id'];
    		if ($run_rev_id > $prev_rev_id) {
    		    $prev_rev_id = $run_rev_id;
    		    $ret = $wiki->getRevisionInfo($prev_rev_id);
    		    $prev_timestamp = $ret['timestamp'];
    		    echo "prev timestamp1 =" . $prev_timestamp . "\n";
    		}
    	}

    	$sth = null;
    	$dbh_tools = null;

        // Want the highest rev_id and the lowest rev_parent_id

    	$pages = [];
    	echo "prev timestamp2 =" . $prev_timestamp . "\n";
    	echo "cur timestamp =" . MediaWiki::unixTimestampToISO8601Timestamp($cur_timestamp) . "\n";

    	$lister = new RecentChangeLister($wiki, $prev_timestamp, MediaWiki::unixTimestampToISO8601Timestamp($cur_timestamp));

    	while (($rcpages = $lister->getNextBatch()) !== false) {
    	    foreach ($rcpages as $rcpage) {
    	        $ns = $rcpage['ns'];
    	        $pagetitle = $rcpage['title'];
    	        if ($ns != 0) {
    	            list($dummy, $pagetitle) = explode(':', $pagetitle, 2);
    	        }

    	        $pagekey = str_pad($ns, 4, '0', STR_PAD_LEFT) . $pagetitle; // Sort by namespace, title
    	        $rev_id = $rcpage['revid'];
    	        $parent_id = $rcpage['old_revid'];

    	        if (! isset($pages[$pagekey])) {
    	            $pages[$pagekey] = array('h' => $rev_id, 'l' => $parent_id);
    	        } else {
    	            $page = $pages[$pagekey];
    	            if ($rev_id > $page['h']) $pages[$pagekey]['h'] = $rev_id;
    	            if ($parent_id < $page['l']) $pages[$pagekey]['l'] = $parent_id;
    	        }
    	    }
    	}

    	echo "page count =" . count($pages) . "\n";

		krsort($pages); // Reverse so that recent changes has articles first

		// Retrieve the revision text

	    $wiki = $this->serviceMgr->getMediaWiki($wikidata['domain']);

		$revids = array();
		// Chunk so don't run out of memory, -2 so that a pages 2 revs don't get split across requests
		$maxids = Config::get(MediaWiki::WIKIPAGEINCREMENT) - 2;
		$idcnt = 0;

		foreach ($pages as $page) {
			$revids[] = $page['h'];
			++$idcnt;

			if ($page['l'] != 0) { // 0 = new page
				$revids[] = $page['l'];
				++$idcnt;
			}

			if ($idcnt > $maxids) {
				$this->processRevisions($wiki, $revids, $wikiname);
				$revids = array();
				$idcnt = 0;
			}
		}

		if ($idcnt) $this->processRevisions($wiki, $revids, $wikiname);;

		// Update the runs table
    	$dbh_tools = $this->serviceMgr->getDBConnection('tools');

		$isth = $dbh_tools->prepare("INSERT INTO runs (wikiname, rundate, rev_id) VALUES (?,?,?)");
		$isth->bindParam(1, $wikiname);
		$isth->bindParam(2, $this->asof);
		$isth->bindParam(3, $cur_rev_id);
		$isth->execute();
    }

    /**
     * Process a chunk of revisions.
     *
     * @param MediaWiki $wiki
     * @param array $revids Revision ids
     * @param string $wikiname Wikiname
     */
    protected function processRevisions(MediaWiki $wiki, $revids, $wikiname)
    {
    	$revisions = $wiki->getRevisionsText($revids);

		// Resort so in reverse namespace, title order

		$sortedrevs = array();

		foreach ($revisions as $pagename => $rev) {
			$nsname = MediaWiki::getNamespaceName($pagename);
			$ns = (string)$rev[0];
			array_shift($rev);
			$pagetitle = $pagename;

			// Strip namespace
			$nsnamelen = strlen($nsname);
			if ($nsnamelen > 0) {
				$pagetitle = substr($pagetitle, $nsnamelen + 1);
			}

			$pagekey = str_pad($ns, 4, '0', STR_PAD_LEFT) . $pagetitle; // Sort by namespace, title
			$rev['t'] = $pagename;
			$rev['ns'] = $ns;
			$sortedrevs[$pagekey] = $rev;
		}

		unset($revisions);

		krsort($sortedrevs); // Reverse so that recent changes has articles first

		foreach ($sortedrevs as $rev) {
			$insert_count = 0;

			$pagetitle = $rev['t'];
			$ns = $rev['ns'];
			$revid1 = (int)$rev[0];
			$revtext1 = $rev[1];
			if (empty($revtext1)) continue;
			$revid2 = 0;
			$revtext2 = '';

			if (count($rev) == 6) {
				$revid2 = (int)$rev[2];
				$revtext2 = $rev[3];
				if (empty($revtext2)) continue;
			}

			// Want newest (highest) id first
			if ($revid1 < $revid2) {
				$temp = $revid1;
				$revid1 = $revid2;
				$revid2 = $temp;

				$temp = $revtext1;
				$revtext1 = $revtext2;
				$revtext2 = $temp;
			}

			$currcats = array();
			$prevcats = array();
			$currtemplates = array();
			$prevtemplates = array();

			$this->parseCategoriesTemplates($revtext1, $currcats, $currtemplates, $this->categoryNS);
			$this->parseCategoriesTemplates($revtext2, $prevcats, $prevtemplates, $this->categoryNS);

			// Write diffs
			$catchanges = array();
			$catchanges['+|T'] = array_diff($currtemplates, $prevtemplates); // Want pluses first so that recent changes shows minuses first
			$catchanges['+|C'] = array_diff($currcats, $prevcats);
			$catchanges['-|T'] = array_diff($prevtemplates, $currtemplates);
			$catchanges['-|C'] = array_diff($prevcats, $currcats);

			// Write pseudo category if all categories were removed and not Draft/Talk ns and not subpage
			if (count($currcats) == 0 && count($prevcats) != 0 && $ns != 118 && ($ns % 2) == 0 && strpos($pagetitle, '/') === false) {
				$catchanges['-|C'][] = '<allcategoriesremoved>';
			}

			// Detect if currently a redirect
			$flags = 0;
			if (preg_match(CommonRegex::REDIRECT_REGEX, $revtext1)) $flags |= 1;

			// Prevent MySQL server has gone away
			$dbh_tools = $this->serviceMgr->getDBConnection('tools');
			$dbh_tools->beginTransaction();
			$isth = $dbh_tools->prepare("INSERT INTO {$wikiname}_diffs (diffdate, plusminus, pagetitle, cat_template, category, flags) VALUES (?,?,?,?,?,?)");

			foreach ($catchanges as $plusminus => $categories) {
				list($plusminus, $watchtype) = explode('|', $plusminus);

				foreach ($categories as $category) {
					++$insert_count;
					if ($insert_count % 1000 == 0) {
						$dbh_tools->commit();
						$dbh_tools->beginTransaction();
					}

		    		$isth->bindValue(1, $this->asof);
		    		$isth->bindValue(2, $plusminus);
					$isth->bindValue(3, $pagetitle);
		    		$isth->bindValue(4, $watchtype);
		    		$isth->bindValue(5, $category);
		    		$isth->bindValue(6, $flags);
		    		$isth->execute();
				}
			}

    		$dbh_tools->commit();
    		$dbh_tools = null;
		}
    }

    /**
     * Parse categories and templates in text.
     *
     * @param string $text Text to parse
     * @param array $cats Add categories to
     * @param array $templates Add templates to
     * @param string $categoryNS Localized categery namespace
     */
    protected function parseCategoriesTemplates($text, &$cats, &$templates, $categoryNS)
    {
    	// Strip comments, etc
    	$cleandata = preg_replace(CommonRegex::REFERENCESTUB_REGEX, '', $text); // Must be first
    	if ($cleandata === null) $cleandata = $text;
   		else $cleandata = preg_replace(CommonRegex::REFERENCE_REGEX, '', $cleandata);
    	$cleandata = preg_replace(array(CommonRegex::COMMENT_REGEX, CommonRegex::NOWIKI_REGEX), '', $cleandata);

    	// Get the explicit categories

		if (preg_match_all(CommonRegex::CATEGORY_REGEX, $cleandata, $matches)) {
			foreach ($matches[1] as $cat) {
				list($cat) = explode('|', $cat);
				$cat = str_replace('_', ' ', ucfirst(trim($cat)));
				$cats[$cat] = $cat; // Removes dups
			}
		}

		// Localized categories
		if ($categoryNS != 'Category') {
			$catregex = str_replace('Category', $categoryNS, CommonRegex::CATEGORY_REGEX);

			if (preg_match_all($catregex, $cleandata, $matches)) {
				foreach ($matches[1] as $cat) {
					list($cat) = explode('|', $cat);
					$cat = str_replace('_', ' ', ucfirst(trim($cat)));
					$cats[$cat] = $cat; // Removes dups
				}
			}
		}

       	// Get the templates

   		$templatedata = TemplateParamParser::getTemplates($cleandata);

   		foreach ($templatedata as $template) {
   			$templatename = $template['name'];
   			$templates[$templatename] = $templatename; // Removes dups
   		}
    }
}
