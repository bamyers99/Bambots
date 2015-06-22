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

namespace com_brucemyers\DatabaseReportBot\Reports;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\RenderedWiki\RenderedWiki;
use com_brucemyers\MediaWiki\WikidataItem;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use jbroadway\URLify;
use PDO;

class WikidataPotentialUnlinked extends DatabaseReport
{
	public function init($apis, $params)
	{
		if (empty($params)) return true;

		$option = $params[0];

		switch ($option) {
			case 'dumpmissingenwiki':
				$this->dumpmissingenwiki($apis['dbh_wikidata']);
				return false;
				break;

			case 'dumphasenwiki':
				$this->dumphasenwiki($apis['dbh_wikidata']);
				return false;
				break;

			case 'loadMissingEnwiki':
				$this->loadMissingEnwiki();
				return false;
				break;

			case 'analyzeMissingEnwiki':
				$this->analyzeMissingEnwiki();
				return false;
				break;

			case 'diacriticDabs':
				$this->diacriticDabs();
				return false;
				break;

			case 'diacriticDabsHtml':
				$this->diacriticDabsHtml();
				return false;
				break;

			case 'tempload':
				$this->tempload();
				return false;
				break;
		}

		return true;
	}

    public function getUsage()
    {
    	return " - Look for potential unlinked enwiki -> wikidata items" .
    	"\t\tdumpmissingenwiki - dump wikidata items missing enwiki link" .
    	"\t\tdumphasenwiki - dump wikidata items having enwiki link" .
    	"\t\tloadMissingEnwiki - load missing enwiki tables" .
    	"\t\tanalyzeMissingEnwiki - analyse missing enwiki for page matches";
    }

	public function getTitle()
	{
		return 'Wikidata potential unlinked';
	}

	public function getIntro()
	{
		return 'Wikidata potential unlinked; data as of <onlyinclude>%s</onlyinclude>.';
	}

	public function getHeadings()
	{
		return array('Article', 'Potential wikidata item', 'Existing wikidata item to merge');
	}

	public function getRows($apis)
	{
		$dbh_wikidata = $apis['dbh_wikidata'];
		$dbh_wiki = $apis['dbh_wiki'];
		$datawiki = $apis['datawiki'];

		// Get the max epp_entity_id
		$sql = "SELECT MAX(epp_entity_id) FROM wb_entity_per_page";
		$sth = $dbh_wikidata->query($sql);
		$row = $sth->fetch(PDO::FETCH_NUM);
		$maxid = (int)$row[0];
		$sth->closeCursor();

		// Retrieve 10,000 ids at a time
		$startid = 0;
		$endid = 10000;

		$results = array();

		$sth3 = $dbh_wikidata->prepare("SELECT ips_item_id FROM wb_items_per_site WHERE ips_site_id = 'enwiki' AND ips_site_page = ?");
		$sth4 = $dbh_wikidata->prepare("SELECT ips_site_page FROM wb_items_per_site WHERE ips_item_id = ?");

		while ($startid < $maxid) {
			// Retrieve the wikidata items without enwiki links

			$sql = "SELECT epp_entity_id FROM wb_entity_per_page epp
					LEFT JOIN wb_items_per_site ips ON ips.ips_item_id = epp.epp_entity_id AND ips.ips_site_id = 'enwiki'
					WHERE epp_entity_id > $startid AND epp_entity_id <= $endid AND
						epp.epp_entity_type = 'item' AND ips.ips_row_id IS NULL";
			$sth = $dbh_wikidata->query($sql);
			$sth->setFetchMode(PDO::FETCH_NUM);
			$ids = array();

			while ($row = $sth->fetch()) {
				$ids[] = (int)$row[0];
			}
			$sth->closeCursor();

			// Get non-enwiki language links

			foreach ($ids as $id) {
				$sth4->bindValue(1, $id);
				$sth4->execute();
				$sth4->setFetchMode(PDO::FETCH_NUM);
				$titles = array();

				while ($row = $sth4->fetch()) {
					$title = str_replace(' ', '_', $row[0]);
					$titles[$title] = true; // Removes duplicates
				}
				$sth4->closeCursor();

				if (empty($titles)) continue;

				// See if enwiki has a page with one of the names
				$sql = "SELECT page_title, page_is_redirect FROM page WHERE page_namespace = 0 AND page_title IN ( " .
					implode(',', array_fill(0, count($titles), '?')) . ")";

				$sth2 = $dbh_wiki->prepare($sql);
				$sth2->execute(array_keys($titles));

				$enrows = $sth2->fetchAll(PDO::FETCH_NUM);
				$sth2->closeCursor();

				foreach ($enrows as $enrow) {
					$title = str_replace('_', ' ', $enrow[0]);
					if (strpos($title, ' ') === false) continue; // Skip single words
					$is_redirect = (int)$enrow[1];

					// See if this page already has a wikidata link
					$sth3->bindValue(1, $title);
					$sth3->execute();
					$already_id = '';
					if ($row = $sth3->fetch(PDO::FETCH_NUM)) $already_id = $row[0];
					$sth3->closeCursor();

					$already_url = '';
					if (! empty($already_id)) {
						if ($is_redirect) continue; // Skip if redirect
						$already_url = "[https://www.wikidata.org/wiki/Q$already_id Q$already_id]";
					}

					// See if this item is a dab page
					$item = $datawiki->getItemWithCache("Q$id");
					$instancesof = $item->getStatementsOfType(WikidataItem::TYPE_INSTANCE_OF);
					if (in_array(WikidataItem::INSTANCE_OF_DISAMBIGUATION, $instancesof)) continue 2; // Doing here so above continues reduce calls

					$firstcol = "[[$title]]";
					if ($is_redirect) $firstcol .= ' (redirect)';

					$results[] = array($firstcol, "[https://www.wikidata.org/wiki/Q$id Q$id]", $already_url);
					break;
				}
			}

			$startid = $endid;
			$endid += 10000;
		}

		usort($results, function($a, $b) {
			$diff = strcmp($a[2], $b[2]); // Sort by already url
			if ($diff != 0) return $diff;
			return strcmp($a[0], $b[0]); // Sort by title
		});

		$results['linktemplate'] = false;

		return $results;
	}

	/**
	 * Dump items missing enwiki link.
	 *
	 * @param unknown $dbh_wikidata
	 */
	function dumpmissingenwiki($dbh_wikidata)
	{
		// Retrieve 10,000 ids at a time
		$startid = 0;
		$endid = 10000;
		$tempfile = self::getMissingEnwikiPath();
		$hndl = fopen($tempfile, 'w');

		// Get the max epp_entity_id
		$sql = "SELECT MAX(epp_entity_id) FROM wb_entity_per_page";
		$sth = $dbh_wikidata->query($sql);
		$row = $sth->fetch(PDO::FETCH_NUM);
		$maxid = (int)$row[0];
		$sth->closeCursor();

		while ($startid < $maxid) {
			// Retrieve the wikidata items without enwiki links

			$sql = "SELECT DISTINCT epp.epp_entity_id, neips.ips_site_page as site_page FROM wb_entity_per_page epp
			LEFT JOIN wb_items_per_site ips ON ips.ips_item_id = epp.epp_entity_id AND ips.ips_site_id = 'enwiki'
			LEFT JOIN wb_items_per_site neips ON epp.epp_entity_id = neips.ips_item_id
			WHERE epp.epp_entity_id > $startid AND epp.epp_entity_id <= $endid AND
			epp.epp_entity_type = 'item' AND ips.ips_row_id IS NULL
			AND LOCATE(' ', neips.ips_site_page) > 0 AND LOCATE(':', neips.ips_site_page) = 0";
			$sth = $dbh_wikidata->query($sql);
			$sth->setFetchMode(PDO::FETCH_NUM);
			$ids = array();

			while ($row = $sth->fetch()) {
				fwrite($hndl, "{$row[0]}\t{$row[1]}\n");
			}

			$sth->closeCursor();
			$startid = $endid;
			$endid += 10000;
		}

		$sth->closeCursor();
		fclose($hndl);
	}

	/**
	 * Dump items with enwiki link.
	 *
	 * @param unknown $dbh_wikidata
	 */
	function dumphasenwiki($dbh_wikidata)
	{
		$tempfile = self::getHasEnwikiPath();
		$hndl = fopen($tempfile, 'w');

		$sql = "SELECT ips_item_id, ips_site_page FROM wb_items_per_site ips WHERE ips.ips_site_id = 'enwiki'";
		$sth = $dbh_wikidata->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			fwrite($hndl, "{$row[0]}\t{$row[1]}\n");
		}

		$sth->closeCursor();
		fclose($hndl);
	}

	/**
	 * Load missing enwiki tables.
	 *
	 * @throws Exception
	 */
	function loadMissingEnwiki()
	{
     	$user = Config::get('DatabaseReportBot.labsdb_username');
    	$pass = Config::get('DatabaseReportBot.labsdb_password');
    	$wiki_host = Config::get('DatabaseReportBot.enwiki_host');

		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=missingenwiki;charset=utf8", $user, $pass);
    	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	// Load enwiki pagenames

// 		$sql = "CREATE TABLE IF NOT EXISTS `pagename` (
// 		  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
// 		  PRIMARY KEY (`title`)
// 		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// 		$dbh_wiki->exec($sql);
// 		$dbh_wiki->exec('TRUNCATE pagename');

// 		$hndl = fopen(FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'enwiki-all-titles-in-ns0', 'r');
// 		if ($hndl === false) throw new Exception('enwiki-all-titles-in-ns0 not found');

//     	$dbh_wiki->beginTransaction();
// 		$sth = $dbh_wiki->prepare('INSERT IGNORE INTO pagename VALUES (?)');
// 		$page_count = 0;

// 		while (! feof($hndl)) {
// 			$buffer = fgets($hndl);
// 			$buffer = rtrim($buffer);
// 			if (empty($buffer)) continue;
// 			$buffer = str_replace('_', ' ', $buffer);

// 			++$page_count;
//     		if ($page_count % 10000 == 0) {
//     			$dbh_wiki->commit();
//     			$dbh_wiki->beginTransaction();
//     		}

// 			$sth->bindValue(1, $buffer);
// 			$sth->execute();
// 		}

//     	$dbh_wiki->commit();
// 		fclose($hndl);

		// Load missing enwiki wikidata link

// 		$sql = "CREATE TABLE IF NOT EXISTS `missingenwiki` (
// 		  `wikidata_id` INT unsigned NOT NULL,
// 		  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
// 		  UNIQUE `title_id` (`title`,`wikidata_id`)
// 		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// 		$dbh_wiki->exec($sql);
// 		$dbh_wiki->exec('TRUNCATE missingenwiki');

// 		$hndl = fopen(self::getMissingEnwikiPath(), 'r');
// 		if ($hndl === false) throw new Exception('missing enwiki file not found');

//     	$dbh_wiki->beginTransaction();
// 		$sth = $dbh_wiki->prepare('INSERT IGNORE INTO missingenwiki VALUES (?,?)');
// 		$page_count = 0;

// 		while (! feof($hndl)) {
// 			$buffer = fgets($hndl);
// 			$buffer = rtrim($buffer);
// 			if (empty($buffer)) continue;
// 			list($pageid, $pagename) = explode("\t", $buffer);

// 			++$page_count;
//     		if ($page_count % 10000 == 0) {
//     			$dbh_wiki->commit();
//     			$dbh_wiki->beginTransaction();
//     		}

// 			$sth->bindValue(1, $pageid);
// 			$sth->bindValue(2, $pagename);
// 			$sth->execute();
// 		}

//     	$dbh_wiki->commit();
// 		fclose($hndl);

		// Load has enwiki wikidata link

		$sql = "CREATE TABLE IF NOT EXISTS `hasenwiki` (
		  `wikidata_id` INT unsigned NOT NULL,
		  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
		  PRIMARY KEY (`title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

		$dbh_wiki->exec($sql);
		$dbh_wiki->exec('TRUNCATE hasenwiki');

		$hndl = fopen(self::getHasEnwikiPath(), 'r');
		if ($hndl === false) throw new Exception('has enwiki file not found');

    	$dbh_wiki->beginTransaction();
		$sth = $dbh_wiki->prepare('INSERT IGNORE INTO hasenwiki VALUES (?,?)');
		$page_count = 0;

		while (! feof($hndl)) {
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;
			list($pageid, $pagename) = explode("\t", $buffer);

			++$page_count;
    		if ($page_count % 10000 == 0) {
    			$dbh_wiki->commit();
    			$dbh_wiki->beginTransaction();
    		}

			$sth->bindValue(1, $pageid);
			$sth->bindValue(2, $pagename);
			$sth->execute();
		}

    	$dbh_wiki->commit();
		fclose($hndl);

/*
		CREATE TABLE `missingenwiki`.`newpagename` (
		`title` varchar(255) NOT NULL,
		PRIMARY KEY (`title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		INSERT INTO `missingenwiki`.`newpagename`
		SELECT pn.title FROM `missingenwiki`.`pagename` pn LEFT JOIN `missingenwiki`.`hasenwiki` hew ON pn.title = hew.title
		WHERE hew.title IS NULL
*/
	}

	/**
	 * Analyze wikidata items missing enwiki links.
	 *
CREATE TABLE `hitlist` (
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO hitlist SELECT me.title FROM missingenwiki me, newpagename np
				WHERE me.title = np.title

UPDATE s51454__wikidata.hitlist SET title = REPLACE(title, ' ', '_')

INSERT IGNORE INTO newhitlist SELECT hitlist.title FROM hitlist, enwiki_p.page ep
WHERE hitlist.title = ep.page_title AND ep.page_namespace = 0 AND ep.page_is_redirect = 0

	 */
	function analyzeMissingEnwiki()
	{
		$user = Config::get('DatabaseReportBot.labsdb_username');
		$pass = Config::get('DatabaseReportBot.labsdb_password');
		$wiki_host = Config::get('DatabaseReportBot.enwiki_host');

		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=missingenwiki;charset=utf8", $user, $pass);
		$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$sql = 'SELECT title FROM hitlist ORDER by title';

		$hndl = fopen(FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'potentialenwiki.html', 'w');
		fwrite($hndl, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
		');

		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			$pagename = urlencode($row[0]);
			$encodedpage = htmlentities($row[0], ENT_COMPAT, 'UTF-8');
			fwrite($hndl, "<a href='https://tools.wmflabs.org/bambots/PageTools.php?wiki=enwiki&page=$pagename'>$encodedpage</a><br />\n");
		}

		fwrite($hndl, '</body>');
		fclose($hndl);
		$sth->closeCursor();
	}

	/**
	 * Get the missing enwiki file path
	 *
	 * @return string
	 */
	static function getMissingEnwikiPath()
	{
		return FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'missingenwiki';
	}

	/**
	 * Get the has enwiki file path
	 *
	 * @return string
	 */
	static function getHasEnwikiPath()
	{
		return FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'hasenwiki';
	}

	/**
	 * Convert diacritics to non diacritics.
	 */
	function diacriticDabs()
	{
		$user = Config::get('DatabaseReportBot.labsdb_username');
		$pass = Config::get('DatabaseReportBot.labsdb_password');
		$wiki_host = Config::get('DatabaseReportBot.enwiki_host');

		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=wikidatawiki_p;charset=utf8", $user, $pass);
		$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$startid = 0;
		$endid = 10000;

		// Get the max id
		$sql = "SELECT MAX(id) FROM wikidata_dabs";
		$sth = $dbh_wiki->query($sql);
		$row = $sth->fetch(PDO::FETCH_NUM);
		$maxid = (int)$row[0];
		$sth->closeCursor();

		while ($startid < $maxid) {
			$sql = "SELECT * FROM wikidata_dabs WHERE id > $startid AND id <= $endid";
			$sth = $dbh_wiki->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);

			$usth = $dbh_wiki->prepare('UPDATE wikidata_dabs SET undiacritic = ? WHERE id = ?');
			$dbh_wiki->beginTransaction();

			while ($row = $sth->fetch()) {
				$id = $row['id'];
				$page = $row['ips_site_page'];
        		// Strip qualifier
        		$stripped = preg_replace('! \([^\)]+\)!u', '', $page);
        		if (is_null($stripped)) echo "Error ($page) : " . array_flip(get_defined_constants(true)['pcre'])[preg_last_error()] . "\n";

				$undiacritic = URLify::downcode($stripped);

				$usth->bindValue(1, $undiacritic);
				$usth->bindValue(2, $id);
				$usth->execute();
			}

			$sth->closeCursor();
    		$dbh_wiki->commit();
			$startid = $endid;
			$endid += 10000;
			echo "Processed = $startid\n";
		}

		$sth->closeCursor();
	}

	/**
	 * Write Dab potential dups html file
	 */
	function diacriticDabsHtml()
	{
		$user = Config::get('DatabaseReportBot.labsdb_username');
		$pass = Config::get('DatabaseReportBot.labsdb_password');
		$wiki_host = Config::get('DatabaseReportBot.enwiki_host');

		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=wikidatawiki_p;charset=utf8", $user, $pass);
		$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$sql = "SELECT undiacritic, GROUP_CONCAT(ips_item_id SEPARATOR ',') AS ids
			FROM wikidata_dabs_unique
			GROUP BY undiacritic HAVING count(*) > 1";

		$hndl = fopen(FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'dabdups.html', 'w');
		fwrite($hndl, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	<body>
		');

		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			$encodedpage = htmlentities($row[0], ENT_COMPAT, 'UTF-8');
			$ids = explode(',', $row[1]);

			fwrite($hndl, $encodedpage);
			foreach ($ids as $id) {
				fwrite($hndl, " <a href='https://www.wikidata.org/wiki/Q$id'>Q$id</a>");
			}
			fwrite($hndl, "<br />\n");
		}

		fwrite($hndl, '</body>');
		fclose($hndl);
		$sth->closeCursor();
	}

	function tempload()
	{
		$user = Config::get('DatabaseReportBot.labsdb_username');
		$pass = Config::get('DatabaseReportBot.labsdb_password');
		$wiki_host = Config::get('DatabaseReportBot.enwiki_host');

		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=wikidatawiki_p;charset=utf8", $user, $pass);
		$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$hndl = fopen(FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'tempload', 'r');
		if ($hndl === false) throw new Exception('file not found');

    	$dbh_wiki->beginTransaction();
		$sth = $dbh_wiki->prepare('INSERT IGNORE INTO explicit_dabs VALUES (?,?)');
		$page_count = 0;

		while (! feof($hndl)) {
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;
			list($id, $pageid, $wiki, $pagename) = explode("\t", $buffer);

			++$page_count;
    		if ($page_count % 10000 == 0) {
    			$dbh_wiki->commit();
    			$dbh_wiki->beginTransaction();
    		}

        	// Strip qualifier
        	$stripped = preg_replace('! \([^\)]+\)!u', '', $pagename);

    		$sth->bindValue(1, $pageid);
			$sth->bindValue(2, $stripped);
			$sth->execute();
		}

    	$dbh_wiki->commit();
		fclose($hndl);
	}
}