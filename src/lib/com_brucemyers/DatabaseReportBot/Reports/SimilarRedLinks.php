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
use PDO;

class SimilarRedLinks extends DatabaseReport
{
	var $lcase = false;

	public function init(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, $params, PDO $dbh_wikidata)
	{
		if (empty($params)) return true;

		$option = $params[0];

		switch ($option) {
		    case 'loadpagenames':
		    	if (isset($params[1]) && $params[1] == 'lcase') $this->lcase = true;
		    	$this->loadpagenames($dbh_tools);
		    	return false;
		    	break;

		    case 'lcase':
		    	$this->lcase = true;
		    	break;
		}

		return true;
	}

	public function getUsage()
	{
		$loadpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'enwiki-all-titles-in-ns0';
		$dumppath = self::getDumpPath();

		return " - Check red links from $dumppath\n" .
			"\t\tloadpagenames - load wiki page names from $loadpath\n" .
			"\t\t\tlcase - lowercase check only";
	}

	public function getTitle()
	{
		return 'Similar Red Links';
	}

	public function getIntro()
	{
		return 'Similar red link matches; data as of <onlyinclude>%s</onlyinclude>.';
	}

	public function getHeadings()
	{
		return array('Red link', 'Matches', 'What links here (template count)');
	}

	public function getRows(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, RenderedWiki $renderedwiki, PDO $dbh_wikidata,
		$wiki_host, $user, $pass)
	{
		$count = 0;
		$results = array();
		$dumppath = self::getDumpPath();
		$hndl = fopen($dumppath, 'r');

		$sth = $dbh_tools->prepare('SELECT page_title FROM SRL_Metaphone_Map WHERE `metaphone` = ?');

		while (! feof($hndl)) {
			if (++$count % 100000 == 0) echo "Processed $count\n";
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;

			list($title, $linkcnt, $templatecnt) = explode("\t", $buffer);
			if (preg_match('!(_of_|_in_|_at_the_|\d{4})!', $title)) continue;

			if ($this->lcase) {
				$phrase = $this->lcasePhrase($title);
			} else {
				$phrase = $this->normalizePhrase($title);
				if (empty($phrase)) continue;

				$phrase = metaphone($phrase);

				if (strlen($phrase) < 7) continue;
			}

			$sth->bindParam(1, $phrase);
			$sth->execute();
			$matches = array();

			while ($row = $sth->fetch(PDO::FETCH_NUM)) {
				$match = $row[0];
				if (strcmp($match, $title) == 0) continue; // Freshly deleted article
				$matches[] = $match;
			}

			$sth->closeCursor();

			if (empty($matches)) continue;
			if (count($matches) > 3) continue;

			$matchlinks = array();
			foreach ($matches as $match) {
				$match = str_replace('_', ' ', $match);
				$matchlinks[] = "[[$match]]";
			}
			$matchlinks = implode('<br />', $matchlinks);
			$urltitle = urlencode($title);
			$whatlinkshere = "[https://en.wikipedia.org/wiki/Special:WhatLinksHere/$urltitle $linkcnt ($templatecnt)]";
			$displaytitle = str_replace('_', ' ', $title);
			//echo $phrase . ' - ' . $displaytitle . ' - ' . $matchlinks . "\n";

			$results[] =  array($displaytitle, $matchlinks, $whatlinkshere);
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
		$sql = "CREATE TABLE IF NOT EXISTS `SRL_Metaphone_Map` (
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `metaphone` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  KEY `metaphone` (`metaphone`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);
		$dbh_tools->exec('truncate SRL_Metaphone_Map');

		$filepath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'enwiki-all-titles-in-ns0';
		$hndl = fopen($filepath, 'r');

		$sql = 'INSERT INTO SRL_Metaphone_Map VALUES (?,?)';
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

			if ($this->lcase) {
				$phrase = $this->lcasePhrase($title);
			} else {
				$phrase = $this->normalizePhrase($title);

				$phrase = metaphone($phrase);
			}

			$sth->bindParam(1, $title);
			$sth->bindParam(2, $phrase);
			$sth->execute();
		}

		fclose($hndl);

    	$dbh_tools->commit();
	}

	function lcasePhrase($phrase)
	{
		$phrase = Convert::clearUTF($phrase);

		$phrase = preg_replace('!\W+!', '', $phrase);
		$phrase = strtolower($phrase);

		// Split into words
		$phrase = preg_split('!_+!', $phrase);
		$phrase = implode(' ', $phrase);

		return $phrase;
	}

	function normalizePhrase($phrase)
	{
		$phrase = Convert::clearUTF($phrase);

		// Strip qualifiers
		$phrase = preg_replace('!_\([^)]+?\)!', '', $phrase);

		$phrase = preg_replace('!\W+!', '', $phrase);
		$phrase = strtolower($phrase);

		// Split into words
		$phrase = preg_split('!_+!', $phrase);
		$phrase = implode(' ', $phrase);

		return $phrase;
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
}