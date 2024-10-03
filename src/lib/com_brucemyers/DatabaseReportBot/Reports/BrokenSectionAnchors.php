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

use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use MediaWiki\Sanitizer;
use PDO;
use Exception;

class BrokenSectionAnchors extends DatabaseReport
{
	protected $outputDir;

    public function init($apis, $params)
    {
    	$outputDir = Config::get(DatabaseReportBot::OUTPUTDIR);
    	$outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
    	$outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
    	$outputDir .= DIRECTORY_SEPARATOR;
    	$this->outputDir = $outputDir;

    	if (empty($params)) return true;

    	$option = $params[0];

    	switch ($option) {
    		case 'createviewcounts':
    			$this->createviewcounts($apis['dbh_wiki']);
    			return false;
    			break;
    	}

    	return true;
    }

    public function getUsage()
    {
    	return " - Check redirect to section anchor existance\n" .
    	"\t\tcreateviewcounts - create view count initial file";
    }

	public function getTitle()
	{
		return 'Broken section anchors';
	}

	public function getIntro()
	{
		return "{{shortcut|WP:DBR/BSA}}\nBroken section anchors on redirect pages (excludes unused redirects); data as of <onlyinclude>%s</onlyinclude>.";
	}

	public function getHeadings()
	{
		return array('Redirect', 'Target', 'Incoming<br />links', 'Views', 'Max<br />views/links');
	}

	public function getRows($apis)
	{
		$dbh_wiki = $apis['dbh_wiki'];
		$renderedwiki = $apis['renderedwiki'];
		$wiki_host = $apis['wiki_host'];
		$user = $apis['user'];
		$pass = $apis['pass'];

		// Retrieve the target page contents

		$sql = "SELECT DISTINCT rd_title FROM redirect WHERE rd_fragment IS NOT NULL AND rd_fragment <> '' AND rd_namespace = 0";
		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);
		$titles = array();

		while ($row = $sth->fetch()) {
			$titles[] = $row[0];
		}
		$sth->closeCursor();

		$renderedwiki->cachePages($titles);

		// Check each redirect

		$sql = "SELECT rd_title, page_title, rd_fragment FROM redirect, page " .
			" WHERE rd_fragment IS NOT NULL AND rd_fragment <> '' AND rd_namespace = 0 AND page_namespace = 0 AND rd_from = page_id ";

		// Save the results to a file so that database query is not open for a long time.

		$tempfile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'DatabaseReportBotBSA.tmp';
		$hndl = fopen($tempfile, 'w');

    	$dbh_enwiki = new PDO("mysql:host=$wiki_host;dbname=enwiki_p;charset=utf8mb4", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sth = $dbh_enwiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			fwrite($hndl, "{$row[0]}\t{$row[1]}\t{$row[2]}\n");
		}

		$sth->closeCursor();
		$sth = null;
		$dbh_enwiki = null;
		fclose($hndl);

		// Load the view counts
		$viewcounts = [];
		$hndl = fopen($this->getWikiviewsPath(), 'r');
		if ($hndl === false) throw new Exception('wikiviews not found');

		while (! feof($hndl)) {
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;
			list($pagename, $count, $pageid) = explode(' ', $buffer);
			$viewcounts[$pagename] = (int)$count;
		}

		fclose($hndl);

		$results = [];
		$hndl = fopen($tempfile, 'r');

		$sql = 'SELECT COUNT(*) as linkcount FROM pagelinks, linktarget WHERE pl_target_id = lt_id AND lt_namespace = 0 AND lt_title = ? GROUP BY lt_namespace';
    	$dbh_enwiki = new PDO("mysql:host=$wiki_host;dbname=enwiki_p;charset=utf8mb4", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sth = $dbh_enwiki->prepare($sql);

		while (! feof($hndl)) {
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer);
			if (empty($buffer)) continue;
			list($target, $source, $fragment) = explode("\t", $buffer);
			$fragment = str_replace(' ', '_', $fragment);

			$page = $renderedwiki->getPageWithCache($target);
	        $escfragment = self::escape_fragment($fragment);
			$escfragment = preg_quote($escfragment, '!');

			$found = preg_match("!id\s*=\s*['\"]{$escfragment}['\"]!u", $page);
			//echo 'found=' . $found . ' regex=' . "!id\s*=\s*['\"]{$escfragment}['\"]!u\n";

			// try without escaping due to <span id= tag embedded directly into wikitext heading.
			if (! $found) {
			    $tempfragment = preg_quote($fragment, '!');
			    $found = preg_match("!id\s*=\s*['\"]{$tempfragment}['\"]!u", $page);
			}
			
			// try escaping 's
			if (! $found) {
			    $tempfragment = preg_quote(str_replace("'", '&#39;', $fragment), '!');
			    $found = preg_match("!id\s*=\s*['\"]{$tempfragment}['\"]!u", $page);
			}
			
			if (! $found) {
			    // Test the connection
			    try {
			        $dbh_enwiki->query('SELECT 1+1');
			    } catch (PDOException $e) {
			        $sth = null;
			        $dbh_enwiki = null;
			        $dbh_enwiki = new PDO("mysql:host=$wiki_host;dbname=enwiki_p;charset=utf8mb4", $user, $pass);
			        $dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			        $sth = $dbh_enwiki->prepare($sql);
			    }

				$sth->bindParam(1, $source);
				$sth->execute();
				$row = $sth->fetch(PDO::FETCH_NUM);
				$sth->closeCursor();
				if (! $row) continue; // Skip if 0 incoming links

				$incomingcnt = (int)$row[0];
				$viewcount = 0;
				if (isset($viewcounts[$source])) $viewcount = $viewcounts[$source];

				$fragment = str_replace('_', ' ', $fragment);
				$source = str_replace('_', ' ', $source);
				$target = str_replace('_', ' ', $target);
				$results[$source] = ["[{{fullurl:$source|redirect=no}} $source]", "[[$target#$fragment]]", $incomingcnt, $viewcount, max($viewcount, $incomingcnt)];
			}
		}

		$sth = null;
		$dbh_enwiki = null;
		fclose($hndl);

		// Sort descending by incoming link/view count
		uasort($results, function($a, $b) {
			if ($a[4] < $b[4]) return 1; // Inverted because want descending sort
			if ($a[4] > $b[4]) return -1;
			return strcmp($a[1], $b[1]);
		});

		// Load the previous list
		$prevpath = $this->outputDir . 'brokensectionanchors';
		$hndl = fopen($prevpath, 'r');
		$prevredirs = array();

		while (! feof($hndl)) {
			$buffer = fgets($hndl);
			$buffer = rtrim($buffer, "\n");
			if (empty($buffer)) continue;
			$prevredirs[] = $buffer;
		}

		fclose($hndl);

		$groups = array('comment' => 'Record count: ' . count($results),
				'linktemplate' => false,
				'forceTOC' => true,
				'rowstyle' => 'style="vertical-align: top;"',
				'groups' => array());

		// Group by target page
		$targets = [];

		foreach ($results as $result) {
			$target = explode('#', $result[1], 2);
			$target = substr($target[0], 2);
			if (! isset($targets[$target])) $targets[$target] = array();
			$targets[$target][] = $result;
		}

		uasort($targets, function($a, $b) {
			$ca = count($a);
			$cb = count($b);
			if ($ca < $cb) return 1; // Inverted because want descending sort
			if ($ca > $cb) return -1;
			return 0;
		});

		$targettmp = array_slice($targets, 0, 500, true);
		$targets = [];

		foreach ($targettmp as $target => $result) {
			$redirs = array();
			foreach ($result as $redir) {
				$section = explode('#', $redir[1], 2);
				$section = substr($section[1], 0, -2);
				$redirs[] = $redir[0] . "#$section";
			}
			$targets[] = [implode('<br />', $redirs), "[[$target]]", count($result), '', ''];
		}

		$curredirs = array_keys($results);

		// Calculate the new broken redirs
		$newredirs = array_diff($curredirs, $prevredirs);

		$newest = [];

		foreach ($newredirs as $source) {
			$newest[] = $results[$source];
			unset($results[$source]);
		}

		$groups['groups']['Newest'] = array_slice($newest, 0, 2000);
		$groups['groups']['Older (partial list)'] = array_slice($results, 0, 500);
		$groups['groups']['Grouped by target page (partial list)'] = $targets;

		// Save the previous redirs
		$bakprevpath = $prevpath . '.bak';
		@unlink($bakprevpath);
		rename($prevpath, $bakprevpath);

		// Write the current redirs
		$hndl = fopen($prevpath, 'w');
		foreach ($curredirs as $curredir) {
			fwrite($hndl, "$curredir\n");
		}

		fclose($hndl);

		return $groups;
	}

	/**
	 * Escape a url fragment.
	 *
	 * @param string $fragment
	 * @return string
	 */
	public static function escape_fragment($fragment)
	{
		static $replace = array(
			'%3A' => ':',
			'%' => '.'
		);

		$fragment = urlencode( Sanitizer::decodeCharReferences( strtr( $fragment, ' ', '_' ) ) );
		$fragment = str_replace( array_keys( $replace ), array_values( $replace ), $fragment );

		return $fragment;
	}

	/**
	 * Unescape a url fragment.
	 *
	 * @param string $fragment
	 * @return string
	 */
	public static function unescape_fragment($fragment)
	{
		$fragment = str_replace('%', "\n", $fragment);
		$fragment = str_replace('.', '%', $fragment);
		$fragment = rawurldecode($fragment);
		$fragment = str_replace('%', '.', $fragment);
		$fragment = str_replace("\n", '%', $fragment);

		return $fragment;
	}

	/**
	 * Create view count initial file.
	 *
	 * jsub -N DatabaseReportBot -cwd -mem 768m php DatabaseReportBot.php BrokenSectionAnchors createviewcounts
	 * cp wikiviews to /var/www/projects/wikitools/data
	 * mv wikiviews wikiviews.bak
	 * LC_ALL=C sort -k 1,1 wikiviews.bak >wikiviews
	 * cd ../scripts
	 * nohup ./importdata.sh getviews 201409 &
	 */
	function createviewcounts($dbh_wiki)
	{
		$sql = "SELECT page_title FROM redirect, page " .
			" WHERE rd_fragment IS NOT NULL AND rd_fragment <> '' AND rd_namespace = 0 AND page_namespace = 0 AND rd_from = page_id " .
			" ORDER BY page_title";

		$tempfile = $this->getWikiviewsPath();
		$hndl = fopen($tempfile, 'w');

		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			// pagename view_count pageid
			// Main_Page 2 3
			fwrite($hndl, "{$row[0]} 0 0\n"); // Not using pageid
		}

		$sth->closeCursor();
		fclose($hndl);
	}

	/**
	 * Get the wiki view count file path
	 *
	 * @return string
	 */
	function getWikiviewsPath()
	{
		return $this->outputDir . 'wikiviews';
	}
}