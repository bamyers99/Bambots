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
	const MAX_UNAPPROVED_CATCOUNT = 50;

	protected $dbh_wiki;
	protected $dbh_tools;

	public function __construct(PDO $dbh_wiki, PDO $dbh_tools)
	{
		$this->dbh_wiki = $dbh_wiki;
		$this->dbh_tools = $dbh_tools;
	}

	/**
	 * Calculate categories
	 *
	 * @param array $params keys = cn?{1-10} - cat names, sd?{1-10} - subcat depth
	 * @param bool $recalc Is this a recalc, default = false
	 * @return array keys = cats - array(), errors - array(), catcount - int
	 */
	public function calcCats($params, $recalc = false)
	{
		$cats = array();
		$errors = array();
		$sth = $this->dbh_wiki->prepare('SELECT cat_title FROM category WHERE cat_title = ?');

		for ($x=1; $x <= 10; ++$x) {
			$catname = trim($params["cn$x"]);
			$subdepth = (int)$params["sd$x"];

			if (! empty($catname)) {
				$wikicatname = str_replace(' ', '_', ucfirst($catname));

				// See if the category exists
	    		$sth->bindParam(1, $wikicatname);
	    		$sth->execute();

	    		if ($row = $sth->fetch(PDO::FETCH_ASSOC)) $cats[] = array('cn' => $wikicatname, 'sd' => $subdepth);
	    		else $errors[] = "Category not found - $catname";
			}
		}

		if (empty($cats)) $catcount = 0;
		else {
			$foundcats = array();

			foreach ($cats as $catdata) {
				$this->traverseCats($foundcats, $catdata['cn'], $catdata['sd']);
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
		$sth = $this->dbh_tools->prepare("INSERT INTO querycats (queryid,category) VALUES ($queryid,?)");
		$this->dbh_tools->beginTransaction();

		foreach ($cats as $catname) {
			$sth->bindParam(1, $catname);
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
	 */
	protected function traverseCats(&$foundcats, $searchcats, $depth)
	{
		if (! is_array($searchcats)) $searchcats = (array)$searchcats;

		$nextcats = array();

		foreach ($searchcats as $cat) {
			if (in_array($cat, $foundcats)) continue;
			$foundcats[] = $cat;
			if ($depth) $nextcats[] = $cat;
		}

		if (! count($nextcats)) return;

		$placeholders = implode(',', array_fill(0, count($nextcats), '?'));
		$sth = $this->dbh_wiki->prepare("SELECT DISTINCT page_title FROM page,categorylinks WHERE page_id=cl_from AND cl_to IN ($placeholders) AND cl_type='subcat'");
		$sth->execute($nextcats);

		$subcats = array();

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$cat = $row['page_title'];
			if (in_array($cat, $foundcats)) continue;
			$subcats[] = $cat;
		}

		$sth->closeCursor();

		if (! count($subcats)) return;

		$this->traverseCats($foundcats, $subcats, $depth - 1);
	}
}