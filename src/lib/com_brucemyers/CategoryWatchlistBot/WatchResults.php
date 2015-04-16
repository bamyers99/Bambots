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
use com_brucemyers\Util\MySQLDate;
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
	 * @param int $page -1 = atom feed
	 * @param int $max_rows
	 * @return array, pageid => ('diffdate','plusminus','category','title','ns')
	 */
	public function getResults($queryid, $params, $page, $max_rows)
	{
		$wikiname = $params['wiki'];
		$queryid = (int)$queryid;
		$origpage = $page = (int)$page;
		$page = $page - 1;
		if ($page < 0 || $page > 1000) $page = 0;
		$offset = $page * $max_rows;

		$cachesfx = $page;
		if ($origpage == -1) $cachesfx = '-1';

		$cachekey = CategoryWatchlistBot::CACHE_PREFIX_RESULT . $queryid . '_' . $cachesfx;

		// Check the cache
		$results = FileCache::getData($cachekey);
		if (! empty($results)) {
			$results = unserialize($results);
			return $results;
		}

		$where = $this->buildSQLWhere($params);
		if (empty($where)) return array();

		// Get the updated pages
		$sth = $this->dbh_tools->prepare("SELECT * FROM `{$wikiname}_diffs` " .
			" WHERE $where " .
			" ORDER BY id DESC " .
			" LIMIT $offset,$max_rows");
		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_ASSOC);

		$results = array();

		while ($row = $sth->fetch()) {
			$ns = MediaWiki::getNamespaceId(MediaWiki::getNamespaceName($row['pagetitle']));
			if ($ns == -1) $ns = 9999; // Hack for non english ns
			$row['ns'] = $ns;
			$row['title'] = $row['pagetitle'];
			unset($row['pagetitle']);
			$results[] = $row;
		}

		$sth->closeCursor();

		if (! count($results)) return $results;

		$serialized = serialize($results);

		FileCache::putData($cachekey, $serialized);

		return $results;
	}

	/**
	 * Build a SQL where clause with the paramaters.
	 *
	 * @param array $params Parameters
	 * @return string Where clause
	 */
	public function buildSQLWhere($params)
	{
		static $reporttypes = array('B' => 'B', 'P' => '+', 'M' => '-');
		$where = array();

		for ($x=1; $x <= 10; ++$x) {
			$catname = trim($params["cn$x"]);
			if (empty($catname)) continue;

			$reporttype = $reporttypes[$params["rt$x"]];
			$pagetype = $params["pt$x"];
			$matchtype = $params["mt$x"];

			if ($matchtype == 'P') {
				$catname = $this->dbh_tools->quote("%$catname%");
				$catmatch = "category LIKE $catname ";
			} else {
				$catname = $this->dbh_tools->quote($catname);
				$catmatch = "category = $catname";
			}

			$extra = '';
			if ($reporttype != 'B') $extra = " AND plusminus = '$reporttype'";

			$where[] = "($catmatch AND cat_template = '$pagetype'$extra)";
		}

		return implode(' OR ', $where);
	}
}