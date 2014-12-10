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
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Convert;
use MediaWiki\Sanitizer;
use PDO;

class DiacriticRedLinks extends DatabaseReport
{
	public function init($apis, $params)
	{
		if (empty($params)) return true;

		$option = $params[0];

		switch ($option) {
		    case 'loadpagenames':
		    	$this->loadpagenames($apis['dbh_tools']);
		    	return false;
		    	break;

		    case 'dumpredlinks':
		    	$this->dumpredlinks($apis['dbh_wiki']);
		    	return false;
		    	break;

		    case 'mostwanted':
		    	$this->mostwanted($apis['mediawiki']);
		    	return false;
		    	break;
		}

		return true;
	}

	public function getUsage()
	{
		$loadpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'enwiki-all-titles-in-ns0';
		$dumppath = self::getDumpPath();
		$mostwantedpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'DatabaseReportBotDRL.mwp';

		return " - Check red links from $dumppath\n" .
			"\t\tloadpagenames - load wiki page names from $loadpath\n" .
			"\t\tdumpredlinks - dump red links to $dumppath\n" .
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
	 * Load article page names into tools DB from a file.
	 *
	 * @param PDO $dbh_tools
	 */
	function loadpagenames(PDO $dbh_tools)
	{
		$sql = "CREATE TABLE IF NOT EXISTS `DRL_Diacritic_Map` (
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `ascii_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  KEY `ascii_title` (`ascii_title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);
		$dbh_tools->exec('truncate DRL_Diacritic_Map');

		$filepath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'enwiki-all-titles-in-ns0';
		$hndl = fopen($filepath, 'r');

		$sql = 'INSERT INTO DRL_Diacritic_Map VALUES (?,?)';
		$sth = $dbh_tools->prepare($sql);

		$dbh_tools->beginTransaction();
		$count = 0;

		while (! feof($hndl)) {
		    ++$count;
    		if ($count % 1000 == 0) {
    			$dbh_tools->commit();
    			$dbh_tools->beginTransaction();
    		}

			$title = fgets($hndl);
			$title = rtrim($title);
			$ascii = Convert::clearUTF($title);

			$sth->bindParam(1, $title);
			$sth->bindParam(2, $ascii);
			$sth->execute();
		}

		fclose($hndl);

    	$dbh_tools->commit();
	}

	/**
	 * Get the red link dump path.
	 *
	 * @return string Dump path
	 */
	public static function getDumpPath()
	{
		return FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'DatabaseReportBotDRL.red';
	}

	/**
	 * Dump article page red links to a file.
	 *
	 * @param PDO $dbh_wiki
	 */
	function dumpredlinks(PDO $dbh_wiki)
	{
		$dbh_wiki->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		$dumppath = self::getDumpPath();

		// Get article and template (10) redlinks
		$sql = 'SELECT pl_title, COUNT(*), SUM(TRUNCATE(pl_from_namespace / 10, 0)) FROM pagelinks
			LEFT JOIN page ON page_title=pl_title AND page_namespace=pl_namespace
			WHERE pl_namespace = 0 AND pl_from_namespace IN (0,10)
			AND page_id IS NULL
			GROUP BY pl_title';

		$hndl = fopen($dumppath, 'w');
		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			fwrite($hndl, "{$row[0]}\t{$row[1]}\t{$row[2]}\n");
		}

		$sth->closeCursor();
		fclose($hndl);
		$dbh_wiki->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}

	/**
	 * Get the most wanted pages.
	 *
	 * @param MediaWiki $mediawiki
	 */
	function mostwanted(MediaWiki $mediawiki)
	{
		$count = 0;
		$mostwantedpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'DatabaseReportBotDRL.mwp';
		$curmostwanted = $mediawiki->getpage('Wikipedia:Most-wanted_articles');
		preg_match_all('!\[\[([^\]]+?)\]\]!', $curmostwanted, $matches, PREG_PATTERN_ORDER);

		$curlinks = array();
		foreach ($matches[1] as $match) {
			$curlinks[] = str_replace(' ', '_', $match);
		}

		$dumppath = self::getDumpPath();
		$hndl = fopen($dumppath, 'r');
		$results = array();

		while (! feof($hndl)) {
			if (++$count % 1000000 == 0) echo "Processed $count\n";
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;

			list($title, $linkcnt, $templatecnt) = explode("\t", $buffer);
			$linkcnt = (int)$linkcnt;
			$templatecnt = (int)$templatecnt;

			if ($templatecnt) continue;
			if ($linkcnt < 20) continue;
			if (in_array($title, $curlinks)) continue;
			if (preg_match('!(_of_|_in_|_at_the_|\d{4})!', $title)) continue;

			$results[] = array($title, $linkcnt);
		}

		fclose($hndl);

		// Sort descending by incoming link count
		usort($results, function($a, $b) {
			if ($a[1] < $b[1]) return 1; // Inverted because want descending sort
			if ($a[1] > $b[1]) return -1;
			return strcmp($a[0], $b[0]);
		});

		$hndl = fopen($mostwantedpath, 'w');

		$chunks = array_chunk($results, 100);
		$startnum = 1;
		$endnum = 100;

		foreach ($chunks as $chunk) {
			if ($startnum == 501) break;
			fwrite($hndl, "====$startnum-$endnum====\n");

			foreach ($chunk as $row) {
				$title = str_replace('_', ' ', $row[0]);
				fwrite($hndl, "*[[$title]] - {$row[1]}\n");
			}

			$startnum += 100;
			$endnum += 100;
		}

		fclose($hndl);
	}
}