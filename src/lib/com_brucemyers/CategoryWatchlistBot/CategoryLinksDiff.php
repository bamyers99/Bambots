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
use com_brucemyers\Util\Logger;
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
    	$dbh_wiki = $this->serviceMgr->getDBConnection($wikiname);
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
    	}

    	// Get the current rev_id
    	$sth = $dbh_wiki->query('SELECT rev_id, rev_timestamp FROM revision ORDER BY rev_id DESC LIMIT 1');
    	$row = $sth->fetch(PDO::FETCH_ASSOC);
    	$cur_rev_id = (int)$row['rev_id'];
    	$cur_timestamp = MediaWiki::wikiTimestampToUnixTimestamp($row['rev_timestamp']);

    	// Limit to 2 hours max revisions
    	$prev_timestamp = strtotime('-2 hours', $cur_timestamp);
    	$prev_timestamp = MediaWiki::unixTimestampToWikiTimestamp($prev_timestamp);

    	$sth = $dbh_wiki->query("SELECT rev_id FROM revision WHERE rev_timestamp > '$prev_timestamp' ORDER BY rev_timestamp LIMIT 1");
    	$row = $sth->fetch(PDO::FETCH_ASSOC);
    	$prev_rev_id = (int)$row['rev_id'];
    	$sth = null;

    	// Get the previous rev_id
    	$sth = $dbh_tools->prepare('SELECT rev_id FROM runs WHERE wikiname = ? ORDER BY rundate DESC LIMIT 1');
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    		$run_rev_id = (int)$row['rev_id'];
    		if ($run_rev_id > $prev_rev_id) $prev_rev_id = $run_rev_id;
    	}

    	$sth = null;
    	$dbh_tools = null;

    	// Get the changed pages since the last run
    	$sth = $dbh_wiki->prepare('SELECT page_namespace, page_title, rev_id, rev_parent_id FROM revision, page WHERE rev_id > ? AND rev_id <= ? AND rev_page = page_id');
    	$sth->bindParam(1, $prev_rev_id);
    	$sth->bindParam(2, $cur_rev_id);
    	$sth->execute();
    	$sth->setFetchMode(PDO::FETCH_NUM);

        // Want the highest rev_id and the lowest rev_parent_id

    	$pages = array();
		while ($row = $sth->fetch()) {
			$pagekey = str_pad($row[0], 4, '0', STR_PAD_LEFT) . $row[1]; // Sort by namespace, title
			$rev_id = (int)$row[2];
			$parent_id = (int)$row[3];

			if (! isset($pages[$pagekey])) {
				$pages[$pagekey] = array('h' => $rev_id, 'l' => $parent_id);
			} else {
				$page = $pages[$pagekey];
				if ($rev_id > $page['h']) $pages[$pagekey]['h'] = $rev_id;
				if ($parent_id < $page['l']) $pages[$pagekey]['l'] = $parent_id;
			}
		}

		$sth->closeCursor();
		$sth = null;
		$dbh_wiki = null;

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

    	$dbh_tools = $this->serviceMgr->getDBConnection('tools');
    	$dbh_tools->beginTransaction();
		$isth = $dbh_tools->prepare("INSERT INTO {$wikiname}_diffs (diffdate, plusminus, pagetitle, cat_template, category, flags) VALUES (?,?,?,?,?,?)");
		$insert_count = 0;

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
			$sortedrevs[$pagekey] = $rev;
		}

		unset($revisions);

		krsort($sortedrevs); // Reverse so that recent changes has articles first

		foreach ($sortedrevs as $rev) {
			$pagetitle = $rev['t'];
			$revid1 = (int)$rev[0];
			$revtext1 = $rev[1];
			if (empty($revtext1)) continue;
			$revid2 = 0;
			$revtext2 = '';

			if (count($rev) == 5) {
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
			$this->followTemplateRedirects($wikiname, $prevtemplates, $currtemplates);
			// Remove dups after redirect replacement
			$prevtemplates = array_unique($prevtemplates);
			$currtemplates = array_unique($currtemplates);

			// Write diffs
			$catchanges = array();
			$catchanges['+|T'] = array_diff($currtemplates, $prevtemplates); // Want pluses first so that recent changes shows minuses first
			$catchanges['+|C'] = array_diff($currcats, $prevcats);
			$catchanges['-|T'] = array_diff($prevtemplates, $currtemplates);
			$catchanges['-|C'] = array_diff($prevcats, $currcats);

			// Write pseudo category if all categories were removed
			if (count($currcats) == 0 && count($prevcats) != 0) {
				$catchanges['-|C'][] = '<allcategoriesremoved>';
			}

			// Detect if currently a redirect
			$flags = 0;
			if (preg_match(CommonRegex::REDIRECT_REGEX, $revtext1)) $flags |= 1;

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
		}

    	$dbh_tools->commit();
    	$dbh_tools = null;
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

    protected function followTemplateRedirects($wikiname, &$prevtemplates, &$currtemplates)
    {
    	$dbh_wiki = $this->serviceMgr->getDBConnection($wikiname);

    	$alltemplates = array();

        foreach ($prevtemplates as $templatename) {
    		$alltemplates[$templatename] = true; // Removes dups
    	}

        foreach ($currtemplates as $templatename) {
    		$alltemplates[$templatename] = true; // Removes dups
    	}

    	if (empty($alltemplates)) return;

    	$escapednames = array();

		foreach ($alltemplates as $templatename => $dummy) {
			$tempname = str_replace(' ', '_', $templatename);
			$escapednames[] = $dbh_wiki->quote($tempname);
		}

		$escapednames = implode(',', $escapednames);

		$sql = "SELECT page_title, rd_title FROM page " .
			" LEFT JOIN redirect ON page_id = rd_from " .
			" WHERE page_namespace = 10 AND page_title IN ($escapednames)";

    	$results = $dbh_wiki->query($sql);
    	$results->setFetchMode(PDO::FETCH_NUM);

    	$existing = array();

    	while ($row = $results->fetch()) {
    		$oldname = str_replace('_', ' ', $row[0]);
    		$existing[$oldname] = $row[1];
    	}

    	$results->closeCursor();
    	$results = null;

    	// Strip non-existant templates and replace redirects
        foreach ($prevtemplates as $oldname => $dummy) {
        	// Can't use isset because an array value of null considers the key unset.
    		if (! array_key_exists($oldname, $existing)) unset($prevtemplates[$oldname]);
    		elseif ($existing[$oldname] !== null) $prevtemplates[$oldname] = str_replace('_', ' ', $existing[$oldname]);
    	}

    	foreach ($currtemplates as $oldname => $dummy) {
    		if (! array_key_exists($oldname, $existing)) unset($currtemplates[$oldname]);
    		elseif ($existing[$oldname] !== null) $currtemplates[$oldname] = str_replace('_', ' ', $existing[$oldname]);
    	}
    }
}
