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

class QueryCats
{
	const CATEGORY_COUNT_UNKNOWN = -1;
	const CATEGORY_COUNT_UNAPPROVED = -2;
	const CATEGORY_COUNT_DENIED = -3;
	const CATEGORY_COUNT_RECALC = -4;
	const MAX_UNAPPROVED_CATCOUNT = 500;

	protected $media_wiki;
	protected $dbh_tools;

	public function __construct(MediaWiki $media_wiki, PDO $dbh_tools)
	{
	    $this->media_wiki = $media_wiki;
		$this->dbh_tools = $dbh_tools;
	}

	/**
	 * Calculate categories
	 *
	 * @param array $params keys = cn?{1-10} - cat names, sd?{1-10} - subcat depth, rt?{1-10} - report type
	 * @param bool $recalc Is this a recalc, default = false
	 * @return array keys = cats - array(), errors - array(), catcount - int
	 */
	public function calcCats($params, $recalc = false)
	{
		$cats = array();
		$errors = array();
		$reporttypes = array('B' => 'B', 'P' => '+', 'M' => '-');

		for ($x=1; $x <= 10; ++$x) {
			if ($params["mt$x"] != 'S') continue; // matchtype subcats only
			$catname = trim($params["cn$x"]);
			$subdepth = 10;
			$reporttype = $reporttypes[$params["rt$x"]];

			if (! empty($catname)) {
				$ret = $this->media_wiki->resolvePageTitle("Category:$catname");

				if (! empty($ret)) $cats[] = array('cn' => $catname, 'sd' => $subdepth, 'rt' => $reporttype);
	    		else $errors[] = "Category not found - $catname";
			}
		}

		$foundcats = array();

		if (empty($cats)) $catcount = 0;
		else {

			foreach ($cats as $catdata) {
				$this->traverseCats($foundcats, $catdata['cn'], $catdata['sd'], $catdata['rt']);
			}

			$catcount = count($foundcats);
		}

		if ($catcount > self::MAX_UNAPPROVED_CATCOUNT && ! $recalc) {
			$catcount = self::CATEGORY_COUNT_UNAPPROVED;
		}

		return array('cats' => $foundcats, 'errors' => $errors, 'catcount' => $catcount);
	}

	/**
	 * Save query categories
	 *
	 * @param int $queryid Query id
	 * @param array $cats Category names
	 */
	public function saveCats($queryid, &$cats)
	{
		$this->dbh_tools->exec("DELETE FROM querycats WHERE queryid = $queryid");
		$sth = $this->dbh_tools->prepare("INSERT INTO querycats (queryid,category,plusminus) VALUES ($queryid,?,?)");
		$this->dbh_tools->beginTransaction();

		foreach ($cats as $catname => $reporttype) {
			$catname = str_replace('_', ' ', $catname);
			$sth->bindValue(1, $catname);
			$sth->bindValue(2, $reporttype);
			$sth->execute();
		}

		$this->dbh_tools->commit();
	}

	/**
	 * Traverse a category tree
	 *
	 * @param array $foundcats in/out
	 * @param mixed $searchcats
	 * @param int $depth
	 * @param string $reporttype
	 */
	protected function traverseCats(&$foundcats, $searchcats, $depth, $reporttype)
	{
		if (! is_array($searchcats)) $searchcats = (array)$searchcats;

		$nextcats = [];

		foreach ($searchcats as $cat) {
			if (isset($foundcats[$cat])) continue;
			if (count($foundcats) >= self::MAX_UNAPPROVED_CATCOUNT) return;
			$foundcats[$cat] = $reporttype;
			if ($depth) $nextcats[] = $cat;
		}

		if (! count($nextcats)) return;


		$subcats = [];

		foreach ($nextcats as $nextcat) {
		    $ret = $this->media_wiki->getList('categorymembers', ['cmtitle' => "Category:$nextcat", 'cmtype' => 'subcat', 'cmlimit' => '500']);

		    if (isset($ret['query']['categorymembers'])) {
		        foreach ($ret['query']['categorymembers'] as $cm) {
        			$cat = substr($cm['title'], 9);
        			if (isset($foundcats[$cat])) continue;
        			$subcats[] = $cat;
        		}
		    }
		}

		if (! count($subcats)) return;

		$this->traverseCats($foundcats, $subcats, $depth - 1, $reporttype);
	}
}