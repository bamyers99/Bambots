<?php
/**
 Copyright 2015 Myers Enterprises II

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
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Convert;
use MediaWiki\Sanitizer;
use PDO;

class CircularCats extends DatabaseReport
{
	public function init($apis, $params)
	{
		if (empty($params)) return true;

		$option = $params[0];

		switch ($option) {
		    case 'loadcategorynames':
		    	$this->loadcategorynames($apis['dbh_tools']);
		    	return false;
		    	break;

		    case 'loadsubcats':
		    	$this->loadsubcats($apis['dbh_tools'], $apis);
		    	return false;
		    	break;

		    case 'mostwanted':
		    	$this->mostwanted($apis['mediawiki'], $apis['dbh_wiki']);
		    	return false;
		    	break;
		}

		return true;
	}

	public function getUsage()
	{
		$loadpath1 = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'categories.tsv';
		$loadpath2 = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'catlinks.tsv';
		$mostwantedpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'DatabaseReportBotDRL.mwp';

		return " - Check for circular category categorization (1 level deep)\n" .
			"\t\tloadcategorynames - load category names from $loadpath1\n" .
			"\t\tloadsubcats - load subcategories from $loadpath2\n" .
			"\t\tmostwanted - dump most wanted pages to $mostwantedpath";
	}

	public function getTitle()
	{
		return 'Diacritic Red Links';
	}

	public function getIntro()
	{
		return 'Diacritic red link matches; data as of <onlyinclude>%s</onlyinclude>.';
	}

	public function getHeadings()
	{
		return array('Red link', 'Matches', 'What links here (template count)');
	}

	public function getRows($apis)
	{
		$dbh_tools = $apis['dbh_tools'];

		$count = 0;
		$results = array();
		$dumppath = self::getDumpPath();
		$hndl = fopen($dumppath, 'r');

		$sth = $dbh_tools->prepare('SELECT page_title FROM DRL_Diacritic_Map WHERE ascii_title = ?');

		while (! feof($hndl)) {
			if (++$count % 100000 == 0) echo "Processed $count\n";
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;

			list($title, $linkcnt, $templatecnt) = explode("\t", $buffer);
			$ascii = Convert::clearUTF($title);
			if (empty($ascii)) continue;

			$sth->bindParam(1, $ascii);
			$sth->execute();
			$matches = array();

			while ($row = $sth->fetch(PDO::FETCH_NUM)) {
				$match = $row[0];
				if (strcmp($match, $title) == 0) continue; // Freshly deleted article
				$matches[] = $match;
			}

			$sth->closeCursor();

			if (! empty($matches)) {
				$matchlinks = array();
				foreach ($matches as $match) {
					$match = str_replace('_', ' ', $match);
					$matchlinks[] = "[[$match]]";
				}
				$matchlinks = implode('<br />', $matchlinks);
				$urltitle = urlencode($title);
				$whatlinkshere = "[https://en.wikipedia.org/wiki/Special:WhatLinksHere/$urltitle $linkcnt ($templatecnt)]";
				$displaytitle = str_replace('_', ' ', $title);

				$results[] =  array($displaytitle, $matchlinks, $whatlinkshere);
			}
		}

		fclose($hndl);

		// Sort descending by incoming link count
		usort($results, function($a, $b) {
			list($ignore, $acnt, $templatecnt) = explode(' ', $a[2]);
			list($ignore, $bcnt, $templatecnt) = explode(' ', $b[2]);
			$acnt = (int)$acnt;
			$bcnt = (int)$bcnt;
			if ($acnt < $bcnt) return 1; // Inverted because want descending sort
			if ($acnt > $bcnt) return -1;
			return strcmp($a[0], $b[0]);
		});

		return $results;
	}

	/**
	 * Load category names into tools DB from a file.
	 *
     * Download categorylinks.sql.gz
     * Download page.sql.gz
     * gunzip -c page.sql.gz | ./mysqlparse - - | awk -f categories.awk - >categories.tsv&
     * gunzip -c catlinks.sql.gz | ./mysqlparse - - | awk -f subcat.awk - >catlinks.tsv&
     * Copy tsv files to cache/DatabaseReportBot/
     *
     * php DatabaseReportBot.php CircularCats loadcategorynames
     * php DatabaseReportBot.php CircularCats loadsubcats >~/Downloads/missingcats.txt
     *
CREATE TABLE `s51454__DatabaseReportBot`.`CC_circular`
SELECT page_id1, page_id2 FROM `s51454__DatabaseReportBot`.`CC_subcats`
GROUP BY page_id1, page_id2
HAVING COUNT(*) > 1;

ALTER TABLE `s51454__DatabaseReportBot`.`CC_circular`
ADD COLUMN `page_title1` VARCHAR(255) NULL DEFAULT '' AFTER `page_id2`,
ADD COLUMN `page_title2` VARCHAR(255) NULL DEFAULT '' AFTER `page_title1`;

UPDATE s51454__DatabaseReportBot.CC_circular circ, s51454__DatabaseReportBot.CC_categories cats
SET circ.page_title1 = cats.page_title WHERE circ.page_id1 = cats.page_id;

UPDATE s51454__DatabaseReportBot.CC_circular circ, s51454__DatabaseReportBot.CC_categories cats
SET circ.page_title2 = cats.page_title WHERE circ.page_id2 = cats.page_id;
	 *
	 * @param PDO $dbh_tools
	 */
	function loadcategorynames(PDO $dbh_tools)
	{
		$sql = "CREATE TABLE IF NOT EXISTS `CC_categories` (
		  `page_id` int unsigned NOT NULL,
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  KEY `page_title` (`page_title`, `page_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);
		$dbh_tools->exec('truncate CC_categories');

		$filepath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'categories.tsv';
		$hndl = fopen($filepath, 'r');

		$sql = 'INSERT INTO CC_categories VALUES (?,?)';
		$sth = $dbh_tools->prepare($sql);

		$dbh_tools->beginTransaction();
		$count = 0;

		while (! feof($hndl)) {
		    ++$count;
    		if ($count % 5000 == 0) {
    			$dbh_tools->commit();
    			$dbh_tools->beginTransaction();
    		}

			$line = rtrim(fgets($hndl));
			if (empty($line)) continue;

			list($page_id, $page_title) = explode("\t", $line);

			$sth->bindParam(1, $page_id);
			$sth->bindParam(2, $page_title);
			$sth->execute();
		}

		fclose($hndl);

    	$dbh_tools->commit();
	}

	/**
	 * Load subcategories into tools DB from a file.
	 *
	 * @param PDO $dbh_tools
	 */
	function loadsubcats(PDO $dbh_tools, $apis)
	{
		$sql = "CREATE TABLE IF NOT EXISTS `CC_subcats` (
		  `page_id1` int unsigned NOT NULL,
		  `page_id2` int unsigned NOT NULL,
		  KEY `page_id` (`page_id1`, `page_id2`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);
		$dbh_tools->exec('truncate CC_subcats');

		$filepath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'catlinks.tsv';
		$hndl = fopen($filepath, 'r');

		$tools_host = $apis['tools_host'];
		$user = $apis['user'];
		$pass = $apis['pass'];
		$dbh_tools2 = new PDO("mysql:host=$tools_host;dbname=s51454__DatabaseReportBot;charset=utf8mb4", $user, $pass);
		$dbh_tools2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$sql = 'INSERT INTO CC_subcats VALUES (?,?),(?,?)';
		$sth = $dbh_tools->prepare($sql);
		$qh = $dbh_tools2->prepare("SELECT page_id FROM CC_categories WHERE page_title = ?");

		$dbh_tools->beginTransaction();
		$count = 0;

		while (! feof($hndl)) {
			++$count;
			if ($count % 5000 == 0) {
				$dbh_tools->commit();
				$dbh_tools->beginTransaction();
			}

			$line = rtrim(fgets($hndl));
			if (empty($line)) continue;

			list($page_id1, $page_title) = explode("\t", $line);

			$qh->bindValue(1, $page_title);
			$qh->execute();

			if ($row = $qh->fetch(PDO::FETCH_NUM)) {
				$page_id2 = $row[0];
			} else {
				echo "$page_title not found\n";
				continue;
			}

			$sth->bindParam(1, $page_id1);
			$sth->bindParam(2, $page_id2);
			$sth->bindParam(3, $page_id2); // Insert reversed
			$sth->bindParam(4, $page_id1);
			$sth->execute();
		}

		fclose($hndl);

		$dbh_tools->commit();
	}
}