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

class MisspelledRedLinks extends DatabaseReport
{
	public function init(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, $params, PDO $dbh_wikidata)
	{
		if (empty($params)) return true;

		$option = $params[0];

		switch ($option) {
		    case 'loaddict':
		    	if (! isset($params[1])) {
		    		echo "Dictionary path required\n";
		    		return false;
		    	}
		    	$this->loaddict($dbh_tools, $params[1]);
		    	return false;
		    	break;

		    case 'qualifiers':
		    	$this->qualifiers();
		    	return false;
		    	break;
		}

		return true;
	}

	public function getUsage()
	{
		$dumppath = self::getDumpPath();

		return " - Check red links from $dumppath\n" .
			"\t\tloaddict <dict path> - load dictionary words from <dict path>\n" .
			"\t\tqualifiers - Count qualifiers";
	}

	public function getTitle()
	{
		return 'Misspelled Red Links';
	}

	public function getIntro()
	{
		return 'Similar red link matches; data as of <onlyinclude>%s</onlyinclude>.';
	}

	public function getHeadings()
	{
		return array('Red link', ' Bad words', 'What links here (template count)');
	}

	public function getRows(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, RenderedWiki $renderedwiki, PDO $dbh_wikidata)
	{
		$count = 0;
		$results = array();
		$dumppath = self::getDumpPath();
		$hndl = fopen($dumppath, 'r');
		$allbadwords = array();

		$sth = $dbh_tools->prepare('SELECT `title` FROM MRL_Dictionary WHERE `title` = ?');

		while (! feof($hndl)) {
			if (++$count % 100000 == 0) echo "Processed $count\n";
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;

			list($title, $linkcnt, $templatecnt) = explode("\t", $buffer);
			if (preg_match('!(_of_|_in_|_at_the_|\d)!', $title)) continue;

			$badwords = array();
			$words = explode('_', $title);

			foreach ($words as $word) {
				if (strlen($word) < 4) continue;
				$lword = $this->lcasePhrase($word);

				$sth->bindParam(1, $lword);
				$sth->execute();
				if (! $sth->fetch(PDO::FETCH_NUM)) {
					if (ctype_alpha($word[0])) continue;
					$word = preg_replace('!\W+!', '', $word);

					$badwords[] = $word;
					if (! isset($allbadwords[$word])) $allbadwords[$word] = 0;
					++$allbadwords[$word];
				}
				$sth->closeCursor();
			}

			if (empty($badwords)) continue;

			$badwords = implode('<br />', $badwords);
			$urltitle = urlencode($title);
			$whatlinkshere = "[https://en.wikipedia.org/wiki/Special:WhatLinksHere/$urltitle $linkcnt ($templatecnt)]";
			$displaytitle = str_replace('_', ' ', $title);
			//echo $displaytitle . ' - ' . $badwords . " - $urltitle\n";

			$results[] =  array($displaytitle, $badwords, $whatlinkshere);
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

		arsort($allbadwords);
		$badlistpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'DatabaseReportBotMRL.txt';
		$hndl = fopen($badlistpath, 'w');
		foreach ($allbadwords as $word => $count) {
			fwrite($hndl, "$word\t$count\n");
		}
		fclose($hndl);

		return $results;
	}

	/**
	 * Count qualifiers
	 */
	function qualifiers()
	{
		$dumppath = self::getDumpPath();
		$hndl = fopen($dumppath, 'r');
		$qualifiers = array();
		$count = 0;

		while (! feof($hndl)) {
			if (++$count % 100000 == 0) echo "Processed $count\n";
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;

			list($title, $linkcnt, $templatecnt) = explode("\t", $buffer);

			if (preg_match('!_\(([^)]+?)\)!', $title, $matches)) {
				$qualifier = str_replace('_', ' ', $matches[1]);
				if (! isset($qualifiers[$qualifier])) $qualifiers[$qualifier] = array('count' => 0, 'link' => urlencode($title));
				++$qualifiers[$qualifier]['count'];
			}
		}

		fclose($hndl);

		uasort($qualifiers, function($a, $b) {
			$acnt = $a['count'];
			$bcnt = $b['count'];
			if ($acnt < $bcnt) return -1;
			if ($acnt > $bcnt) return 1;
			return 0;
		});

		$qualifierpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'DatabaseReportBotMRL.txt';
		$hndl = fopen($qualifierpath, 'w');
		foreach ($qualifiers as $qualifier => $info) {
			fwrite($hndl, "$qualifier\t{$info['count']}\t{$info['link']}\n");
		}
		fclose($hndl);
	}

	/**
	 * Load article page names into tools DB from a file.
	 *
	 * @param PDO $dbh_tools
	 * @param string $filepath
	 */
	function loaddict(PDO $dbh_tools, $filepath)
	{
		$sql = "CREATE TABLE IF NOT EXISTS `MRL_Dictionary` (
		  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  UNIQUE `title` (`title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);

		$sql = 'INSERT IGNORE INTO MRL_Dictionary VALUES (?)';
		$sth = $dbh_tools->prepare($sql);

		$dbh_tools->beginTransaction();
		$count = 0;

		$dictdata = file_get_contents($filepath);
		$dictdata = explode("\r\n==========================================\r\n", $dictdata);
		$wordsbyletter = explode("\r\n+++++++++\r\n", $dictdata[0]);

		foreach( $wordsbyletter as $WordsInLetter ){
			$words = explode( "\r\n", $WordsInLetter );

			foreach ($words as $word) {
				++$count;
				if ($count % 1000 == 0) {
					$dbh_tools->commit();
					$dbh_tools->beginTransaction();
				}

				$word = $this->lcasePhrase(utf8_encode($word));

				$sth->bindParam(1, $word);
				$sth->execute();
			}
		}

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