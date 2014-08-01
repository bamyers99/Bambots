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

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\FileCache;
use PDO;

class WatchResults
{
	protected $dbh_wiki;
	protected $dbh_tools;

	public function __construct(PDO $dbh_wiki, PDO $dbh_tools)
	{
		$this->dbh_wiki = $dbh_wiki;
		$this->dbh_tools = $dbh_tools;
	}

	/**
	 * Get watchlist results
	 *
	 * @param int $queryid
	 * @param array $params
	 * @return array, pageid => ('diffdate','plusminus','category','title','ns')
	 */
	public function getResults($queryid, $params)
	{
		$wikiname = $params['wiki'];
		$days = (int)$params['days'];
		$queryid = (int)$queryid;

		// Check the cache
		$results = FileCache::getData(CategoryWatchlistBot::CACHE_PREFIX_RESULT . $queryid);
		if (! empty($results)) {
			$results = unserialize($results);
			return $results;
		}

		// Get the start date
		$sth = $this->dbh_tools->prepare("SELECT rundate FROM runs WHERE wikiname = ? ORDER BY rundate DESC LIMIT $days");
		$sth->bindParam(1, $wikiname);
		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) $startdate = $row['rundate'];

		if (! isset($startdate)) return array();

		// Get the updated pages
		$sth = $this->dbh_tools->prepare("SELECT diffs.* FROM `{$wikiname}_diffs` diffs, querycats qc " .
			" WHERE qc.queryid = $queryid AND qc.category = diffs.category AND diffs.diffdate >= ? ORDER BY diffdate DESC LIMIT 1000");
		$sth->bindParam(1, $startdate);
		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_ASSOC);

		$results = array();
		$pageids = array();

		while ($row = $sth->fetch()) {
			$pageids[] = $row['pageid'];
			$row['category'] = str_replace('_', ' ', $row['category']);
			$results[] = $row;
		}

		$sth->closeCursor();

		if (! count($results)) return $results;

		// Get the page titles
		$sth = $this->dbh_wiki->query('SELECT page_id, page_namespace, page_title FROM page WHERE page_id IN (' .
			implode(',', $pageids) . ')');

		while ($row = $sth->fetch()) {
			$pageid = $row['page_id'];
			$namespace = (int)$row['page_namespace'];
			$prefix = '';
			if ($namespace == 1) $prefix = 'Talk:';
			elseif ($namespace != 0) $prefix = MediaWiki::$namespaces[$namespace] . ':';

			foreach ($results as &$page) {
				if ($page['pageid'] == $pageid) {
					$page['title'] = $prefix . str_replace('_', ' ', $row['page_title']);
					$page['ns'] = $namespace;
				}
			}
			unset($page);
		}

		$sth->closeCursor();

		$serialized = serialize($results);

		FileCache::putData(CategoryWatchlistBot::CACHE_PREFIX_RESULT . $queryid, $serialized);

		return $results;
	}
}