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
use MediaWiki\Sanitizer;
use PDO;

class BrokenSectionAnchors extends DatabaseReport
{
    const COMMENT_REGEX = '/<!--.*?-->/us';
    const WIKI_TEMPLATE_REGEX = '/\\{\\{.+?\\}\\}/us';

	public function getTitle()
	{
		return 'Broken section anchors';
	}

	public function getIntro()
	{
		return 'Broken section anchors (excludes unused redirects); data as of <onlyinclude>%s</onlyinclude>.';
	}

	public function getHeadings()
	{
		return array('Redirect', 'Incoming links' , 'Target');
	}

	public function getRows(PDO $dbh_wiki, MediaWiki $mediawiki, RenderedWiki $renderedwiki)
	{
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

		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			fwrite($hndl, "{$row[0]}\t{$row[1]}\t{$row[2]}\n");
		}

		$sth->closeCursor();
		fclose($hndl);

		$results = array();
		$hndl = fopen($tempfile, 'r');

		$sql = 'SELECT COUNT(*) as linkcount FROM pagelinks WHERE pl_namespace = 0 AND pl_title = ? GROUP BY pl_namespace';
		$sth = $dbh_wiki->prepare($sql);

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

			if (! $found) {
				$sth->bindParam(1, $source);
				$sth->execute();
				$row = $sth->fetch(PDO::FETCH_NUM);
				$sth->closeCursor();
				if (! $row) continue; // Skip if 0 incoming links

				$fragment = str_replace('_', ' ', $fragment);
				$source = str_replace('_', ' ', $source);
				$target = str_replace('_', ' ', $target);
				$results[] = array($source, (int)$row[0], "[[$target#$fragment]]");
			}
		}

		fclose($hndl);

		// Sort descending by incoming link count
		usort($results, function($a, $b) {
			if ($a[1] < $b[1]) return 1; // Inverted because want descending sort
			if ($a[1] > $b[1]) return -1;
			return strcmp($a[2], $b[2]);
		});

		return $results;
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

}