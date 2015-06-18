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
namespace com_brucemyers\CleanupWorklistBot;

use PDO;

class Categories {
	public static $CATEGORIES = array (
			// from-monthly
			'1911 Britannica articles needing updates' => array (
					'type' => 'from-monthly'
			),
			'Accuracy disputes' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Article sections to be split' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles about possible neologisms' => array (
					'type' => 'from-monthly'
			),
			'Articles containing potentially dated statements' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles lacking in-text citations' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles lacking page references' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles lacking reliable references' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles lacking sources' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles needing additional categories' => array (
					'type' => 'from-monthly'
			),
			'Articles needing additional references' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles needing cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles needing expert attention' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles needing link rot cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Articles needing more viewpoints' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles needing sections' => array (
					'type' => 'from-monthly'
			),
			'Articles needing the year an event occurred' => array (
					'type' => 'from-monthly'
			),
			'Articles requiring tables' => array (
					'type' => 'from-monthly'
			),
			'Articles slanted towards recent events' => array (
					'type' => 'from-monthly'
			),
			'Articles sourced by IMDb' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles sourced only by IMDb' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles that may be too long' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles that may contain original research' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles that need to differentiate between fact and fiction' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles to be expanded' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles to be merged' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles to be split' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles with a promotional tone' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles with broken or outdated citations' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles with close paraphrasing' => array (
					'type' => 'from-monthly'
			),
			'Articles with close paraphrasing of public domain sources' => array (
					'type' => 'from-monthly'
			),
			'Articles with dead external links' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Articles with disproportional geographic scope' => array (
					'type' => 'from-monthly'
			),
			'Articles with disputed statements' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles with excessive see also sections' => array (
					'type' => 'from-monthly'
			),
			'Articles with failed verification' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Articles with improper non-free content' => array (
					'type' => 'from-monthly'
			),
			'Articles with improper non-free content (lists)' => array (
					'type' => 'from-monthly'
			),
			'Articles with limited geographic scope' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles with links needing disambiguation' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Articles with minor POV problems' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles with obsolete information' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles with peacock terms' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles with sections that need to be turned into prose' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles with specifically marked weasel-worded phrases' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles with too few wikilinks' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Articles with topics of unclear notability' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles with trivia sections' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Articles with unsourced statements' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Articles with weasel words' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Autobiographical articles' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'BLP articles lacking sources' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Copied and pasted articles and sections' => array (
					'type' => 'from-monthly'
			),
			'Copied and pasted articles and sections with url provided' => array (
					'type' => 'from-monthly'
			),
			'Dead-end pages' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Disambiguation pages in need of cleanup' => array (
					'type' => 'from-monthly'
			),
			'Incomplete disambiguation' => array (
					'type' => 'from-monthly'
			),
			'Incomplete lists' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'NPOV disputes' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'NRHP articles with dead external links' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Orphaned articles' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Pages with excessive dablinks' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Recently revised' => array (
					'type' => 'from-monthly'
			),
			'Self-contradictory articles' => array (
					'type' => 'from-monthly',
					'group' => 'Clarity'
			),
			'Suspected copyright infringements without a source' => array (
					'type' => 'from-monthly'
			),
			'Uncategorized' => array (
					'type' => 'from-monthly'
			),
			'Uncategorized stubs' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Unreferenced BLPs' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Unreviewed new articles' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Unreviewed new articles created via the Article Wizard' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Vague or ambiguous geographic scope' => array (
					'type' => 'from-monthly',
					'group' => 'Clarity'
			),
			'Vague or ambiguous time' => array (
					'type' => 'from-monthly',
					'group' => 'Clarity'
			),
			'Wikipedia articles in need of updating' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia articles needing clarification' => array (
					'type' => 'from-monthly',
					'group' => 'Clarity'
			),
			'Wikipedia articles needing context' => array (
					'type' => 'from-monthly',
					'group' => 'Clarity'
			),
			'Wikipedia articles needing copy edit' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia articles needing factual verification' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Wikipedia articles needing page number citations' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Wikipedia articles needing reorganization' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia articles needing rewrite' => array (
					'type' => 'from-monthly',
					'group' => 'Clarity'
			),
			'Wikipedia articles needing style editing' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia articles that are too technical' => array (
					'type' => 'from-monthly',
					'group' => 'Clarity'
			),
			'Wikipedia articles with plot summary needing attention' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia articles with possible conflicts of interest' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Wikipedia external links cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),
			'Wikipedia introduction cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia list cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia pages needing cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia references cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'References'
			),
			'Wikipedia spam cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Neutrality'
			),
			'Wikipedia articles containing buzzwords' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia articles without plot summaries' => array (
					'type' => 'from-monthly',
					'group' => 'Content'
			),
			'Wikipedia red link cleanup' => array (
					'type' => 'from-monthly',
					'group' => 'Links'
			),

			// no-date
			'All articles needing coordinates' => array (
					'type' => 'no-date',
					'group' => 'Content'
			),
			'All articles needing expert attention' => array (
					'type' => 'no-date',
					'group' => 'Clarity'
			),
			'Animals cleanup' => array (
					'type' => 'no-date',
					'group' => 'Content'
			),
			'Articles needing more detailed references' => array (
					'type' => 'no-date',
					'group' => 'References'
			),
			'Articles with incorrect citation syntax' => array (
					'type' => 'no-date',
					'subcats' => 'only',
					'group' => 'References'
			),
			'Invalid conservation status' => array (
					'type' => 'no-date'
			),
			'Missing taxobox' => array (
					'type' => 'no-date'
			),
			'Pages using duplicate arguments in template calls' => array (
					'type' => 'no-date'
			),
			'Persondata templates without short description parameter' => array (
					'type' => 'no-date'
			),
			'Plant articles needing a taxobox' => array (
					'type' => 'no-date'
			),
			'Taxoboxes needing a status system parameter' => array (
					'type' => 'no-date'
			),
			'Taxoboxes with an invalid color' => array (
					'type' => 'no-date'
			),
			'Taxoboxes with an unrecognised status system' => array (
					'type' => 'no-date'
			),
			'Tree of Life cleanup' => array (
					'type' => 'no-date',
					'group' => 'Content'
			),
			'Wikipedia articles needing cleanup after translation' => array (
					'type' => 'no-date',
					'group' => 'Content'
			),

			// since-yearly
			'Pages with DOIs inactive' => array (
					'type' => 'since-yearly',
					'group' => 'Links'
			)
	);
	static $parentCats = array ();
	var $dbh_tools;
	var $enwiki_host;
	var $user;
	var $pass;
	public $categories = array(); // Storing in memory because SQL join is hanging.

	/**
	 * Constructor
	 *
	 * @param string $enwiki_host
	 * @param string $user
	 * @param string $pass
	 * @param PDO $dbh_tools
	 */
	public function __construct($enwiki_host, $user, $pass, PDO $dbh_tools)
	{
		$this->enwiki_host = $enwiki_host;
		$this->user = $user;
		$this->pass = $pass;
		$this->dbh_tools = $dbh_tools;
	}

	/**
	 * Load the articles in the above categories.
	 *
	 * @param bool $skipCatLoad
	 *        	Skip the category load, only load parent cats
	 * @return int Category count
	 */
	public function load($skipCatLoad)
	{
		$count = 0;
		if (! $skipCatLoad) {
			$this->dbh_tools->exec ( 'TRUNCATE category' );
			$this->dbh_tools->exec ( 'TRUNCATE categorylinks' );

			$isth = $this->dbh_tools->prepare ( 'INSERT INTO category VALUES (:id, :title, :month, :year)' );
		}

		foreach ( self::$CATEGORIES as $cat => $attribs ) {
			$cattype = $attribs ['type'];
			$subcatsonly = isset ( $attribs ['subcats'] );
			if ($skipCatLoad && ! $subcatsonly)
				continue;
			$sqls = array ();

			switch ($cattype) {
				case 'from-monthly' :
					$param = str_replace ( ' ', '\_', "$cat from %" );
					// Making sure a page for the category exists to weed out bad categories.
					$sqls [$param] = "SELECT cat_id as id, cat_title as title,
						MONTH(STR_TO_DATE(SUBSTRING_INDEX(SUBSTRING_INDEX(cat_title, '_', -2), '_', 1), '%M')) as month,
						SUBSTRING_INDEX(cat_title, '_', -1) as year
						FROM category, page
						WHERE cat_title = page_title AND page_namespace = 14 AND
							cat_title LIKE ? AND cat_pages - (cat_subcats + cat_files) > 0";

					$param = str_replace ( ' ', '_', $cat );
					$sqls [$param] = "SELECT cat_id as id, cat_title as title,
						NULL as month,
						NULL as year
						FROM category WHERE cat_title = ? AND cat_pages - (cat_subcats + cat_files) > 0";
					break;

				case 'since-yearly' :
					$param = str_replace ( ' ', '\_', "$cat since %" );
					$sqls [$param] = "SELECT cat_id as id, cat_title as title,
						NULL as month,
						SUBSTRING_INDEX(cat_title, '_', -1) as year
						FROM category, page
						WHERE cat_title = page_title AND page_namespace = 14 AND
							cat_title LIKE ? AND cat_pages - (cat_subcats + cat_files) > 0";
					break;

				case 'no-date' :
					$param = str_replace ( ' ', '_', $cat );

					if ($subcatsonly) {
						$sqls [$param] = "SELECT c.cat_id as id, c.cat_title as title,
							NULL as month,
							NULL as year
							FROM category c
							JOIN page AS cat ON c.cat_title = cat.page_title
							JOIN categorylinks AS cl ON cat.page_id = cl.cl_from
							WHERE cl.cl_to = ? AND c.cat_pages - (c.cat_subcats + c.cat_files) > 0";
					} else {
						$sqls [$param] = "SELECT cat_id as id, cat_title as title,
							NULL as month,
							NULL as year
							FROM category WHERE cat_title = ? AND cat_pages - (cat_subcats + cat_files) > 0";
					}
					break;
			}

			foreach ( $sqls as $param => $sql ) {
    			$dbh_enwiki = new PDO("mysql:host={$this->enwiki_host};dbname=enwiki_p;charset=utf8", $this->user, $this->pass);
    			$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    			echo "$param => $sql\n";
				$sth = $dbh_enwiki->prepare ( $sql );
				$sth->bindParam ( 1, $param );
				$sth->setFetchMode ( PDO::FETCH_ASSOC );
				$sth->execute ();

				while ( $row = $sth->fetch () ) {
					$title = $row ['title'];
					if ($subcatsonly) {
						$childTitle = str_replace ( '_', ' ', $title );
						self::$parentCats [$childTitle] = $cat;
					} else {
						$row ['title'] = str_replace ( ' ', '_', $cat );
					}

					if (! $skipCatLoad) {
						$isth->execute ( $row );
						++ $count;
						$catid = (int)$row['id'];
						$this->categories[$catid] = array('t' => $row['title'], 'm' => $row['month'], 'y' => $row['year']);

						$this->loadCategoryMembers ( $catid, $title );
					}
				}

				$sth->closeCursor ();
				$sth = null;
				$dbh_enwiki = null; // Yea, well, yea
			}
		}

		$isth = null;

		if ($skipCatLoad) {
			$results = $this->dbh_tools->query('SELECT * FROM category');
			$results->setFetchMode ( PDO::FETCH_ASSOC );

			while ( $row = $results->fetch () ) {
				$catid = (int)$row['cat_id'];
				$this->categories[$catid] = array('t' => $row['cat_title'], 'm' => $row['month'], 'y' => $row['year']);
			}

			$results->closeCursor();
			$results = null;
		}

		return $count;
	}

	/**
	 * Load article ids for a category.
	 *
	 * @param int $catid Category id
	 * @param string $cat
	 *        	Category
	 */
	function loadCategoryMembers($catid, $cat)
	{
    	$dbh_enwiki = new PDO("mysql:host={$this->enwiki_host};dbname=enwiki_p;charset=utf8", $this->user, $this->pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$count = 0;
		$this->dbh_tools->beginTransaction ();
		$isth = $this->dbh_tools->prepare ( 'INSERT INTO categorylinks VALUES (:cl_from, :cat_id)' );
		$sql = "SELECT cl_from
				FROM categorylinks
				WHERE cl_to = ? AND cl_type = 'page'";

		$sth = $dbh_enwiki->prepare ( $sql );
		$sth->bindParam ( 1, $cat );
		$sth->setFetchMode ( PDO::FETCH_ASSOC );
		$sth->execute ();

		while ( $row = $sth->fetch () ) {
			++ $count;
			if ($count % 1000 == 0) {
				$this->dbh_tools->commit ();
				$this->dbh_tools->beginTransaction ();
			}
			$isth->execute ( array('cl_from' => $row['cl_from'], 'cat_id' => $catid) );
		}

		$sth->closeCursor ();
		$sth = null;
		$this->dbh_tools->commit ();
		$isth = null;
		$dbh_enwiki = null;
	}
}