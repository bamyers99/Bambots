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

class Categories
{
    public static $categories = array(
    	// from-monthly
    	'1911 Britannica articles needing updates' 			=> array('type' => 'from-monthly'),
   		'Accuracy disputes' 								=> array('type' => 'from-monthly'),
     	'Article sections to be split' 						=> array('type' => 'from-monthly'),
   		'Articles about possible neologisms' 				=> array('type' => 'from-monthly'),
    	'Articles containing potentially dated statements' 	=> array('type' => 'from-monthly'),
    	'Articles lacking in-text citations' 				=> array('type' => 'from-monthly'),
    	'Articles lacking page references' 					=> array('type' => 'from-monthly'),
   		'Articles lacking reliable references' 				=> array('type' => 'from-monthly'),
   		'Articles lacking sources' 							=> array('type' => 'from-monthly'),
    	'Articles needing additional references' 			=> array('type' => 'from-monthly'),
    	'Articles needing chemical formulas' 				=> array('type' => 'from-monthly'),
    	'Articles needing cleanup' 							=> array('type' => 'from-monthly'),
   		'Articles needing expert attention' 				=> array('type' => 'from-monthly'),
   		'Articles needing link rot cleanup' 				=> array('type' => 'from-monthly'),
    	'Articles needing more viewpoints' 					=> array('type' => 'from-monthly'),
    	'Articles needing sections' 						=> array('type' => 'from-monthly'),
    	'Articles needing the year an event occurred' 		=> array('type' => 'from-monthly'),
    	'Articles requiring tables' 						=> array('type' => 'from-monthly'),
    	'Articles slanted towards recent events' 			=> array('type' => 'from-monthly'),
    	'Articles sourced by IMDB' 							=> array('type' => 'from-monthly'),
    	'Articles sourced only by IMDB' 					=> array('type' => 'from-monthly'),
    	'Articles that may be too long' 					=> array('type' => 'from-monthly'),
    	'Articles that may contain original research' 		=> array('type' => 'from-monthly'),
    	'Articles that need to differentiate between fact and fiction' => array('type' => 'from-monthly'),
    	'Articles to be expanded' 							=> array('type' => 'from-monthly'),
   		'Articles to be expanded with sources' 				=> array('type' => 'from-monthly'),
    	'Articles to be merged' 							=> array('type' => 'from-monthly'),
   		'Articles to be pruned' 							=> array('type' => 'from-monthly'),
   		'Articles to be split' 								=> array('type' => 'from-monthly'),
    	'Articles with a promotional tone' 					=> array('type' => 'from-monthly'),
    	'Articles with broken or outdated citations' 		=> array('type' => 'from-monthly'),
    	'Articles with close paraphrasing' 					=> array('type' => 'from-monthly'),
   		'Articles with close paraphrasing of public domain sources' => array('type' => 'from-monthly'),
    	'Articles with dead external links' 				=> array('type' => 'from-monthly'),
   		'Articles with disproportional geographic scope' 	=> array('type' => 'from-monthly'),
    	'Articles with disputed statements' 				=> array('type' => 'from-monthly'),
   		'Articles with excessive "see also" sections' 		=> array('type' => 'from-monthly'),
   		'Articles with improper non-free content' 			=> array('type' => 'from-monthly'),
    	'Articles with improper non-free content (lists)' 	=> array('type' => 'from-monthly'),
    	'Articles with limited geographic scope' 			=> array('type' => 'from-monthly'),
    	'Articles with links needing disambiguation' 		=> array('type' => 'from-monthly'),
    	'Articles with minor POV problems' 					=> array('type' => 'from-monthly'),
   		'Articles with obsolete information' 				=> array('type' => 'from-monthly'),
    	'Articles with peacock terms' 						=> array('type' => 'from-monthly'),
   		'Articles with sections that need to be turned into prose' => array('type' => 'from-monthly'),
    	'Articles with specifically marked weasel-worded phrases' => array('type' => 'from-monthly'),
    	'Articles with too few wikilinks' 					=> array('type' => 'from-monthly'),
    	'Articles with topics of unclear notability' 		=> array('type' => 'from-monthly'),
   		'Articles with trivia sections' 					=> array('type' => 'from-monthly'),
   		'Articles with unsourced statements' 				=> array('type' => 'from-monthly'),
   		'Articles with weasel words' 						=> array('type' => 'from-monthly'),
   		'Autobiographical articles' 						=> array('type' => 'from-monthly'),
    	'BLP articles lacking sources' 						=> array('type' => 'from-monthly'),
   		'Category needed' 									=> array('type' => 'from-monthly'),
   		'Cleanup section' 									=> array('type' => 'from-monthly'),
    	'Copied and pasted articles and sections' 			=> array('type' => 'from-monthly'),
    	'Copied and pasted articles and sections with url provided' => array('type' => 'from-monthly'),
    	'Dead-end pages' 									=> array('type' => 'from-monthly'),
   		'Disambiguation pages in need of cleanup' 			=> array('type' => 'from-monthly'),
    	'Incomplete disambiguation' 						=> array('type' => 'from-monthly'),
    	'Incomplete lists' 									=> array('type' => 'from-monthly'),
    	'NPOV disputes' 									=> array('type' => 'from-monthly'),
    	'NRHP articles with dead external links' 			=> array('type' => 'from-monthly'),
    	'Orphaned articles' 								=> array('type' => 'from-monthly'),
    	'Pages with excessive dablinks' 					=> array('type' => 'from-monthly'),
    	'Recently revised' 									=> array('type' => 'from-monthly'),
    	'Self-contradictory articles' 						=> array('type' => 'from-monthly'),
   		'Suspected copyright infringements without a source' => array('type' => 'from-monthly'),
    	'Uncategorized stubs' 								=> array('type' => 'from-monthly'),
    	'Unreferenced BLPs' 								=> array('type' => 'from-monthly'),
    	'Unreviewed new articles' 							=> array('type' => 'from-monthly'),
    	'Unreviewed new articles created via the Article Wizard' => array('type' => 'from-monthly'),
   		'Vague or ambiguous geographic scope' 				=> array('type' => 'from-monthly'),
    	'Vague or ambiguous time' 							=> array('type' => 'from-monthly'),
    	'Wikipedia articles in need of updating' 			=> array('type' => 'from-monthly'),
    	'Wikipedia articles needing clarification' 			=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing context' 				=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing copy edit' 				=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing factual verification' 	=> array('type' => 'from-monthly'),
    	'Wikipedia articles needing page number citations' 	=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing reorganization' 		=> array('type' => 'from-monthly'),
    	'Wikipedia articles needing rewrite' 				=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing style editing' 			=> array('type' => 'from-monthly'),
   		'Wikipedia articles that are too technical' 		=> array('type' => 'from-monthly'),
    	'Wikipedia articles with plot summary needing attention' => array('type' => 'from-monthly'),
    	'Wikipedia articles with possible conflicts of interest' => array('type' => 'from-monthly'),
   		'Wikipedia external links cleanup' 					=> array('type' => 'from-monthly'),
   		'Wikipedia introduction cleanup' 					=> array('type' => 'from-monthly'),
   		'Wikipedia laundry list cleanup' 					=> array('type' => 'from-monthly'),
    	'Wikipedia list cleanup' 							=> array('type' => 'from-monthly'),
    	'Wikipedia pages needing cleanup' 					=> array('type' => 'from-monthly'),
   		'Wikipedia references cleanup' 						=> array('type' => 'from-monthly'),
    	'Wikipedia spam cleanup' 							=> array('type' => 'from-monthly'),
   		'Wikipedia articles containing buzzwords' 			=> array('type' => 'from-monthly'),
   		'Wikipedia articles without plot summaries' 		=> array('type' => 'from-monthly'),
   		'Wikipedia red link cleanup' 						=> array('type' => 'from-monthly'),

    	// no-date
    	'All articles needing coordinates' 					=> array('type' => 'no-date'),
    	'All articles needing expert attention' 			=> array('type' => 'no-date'),
    	'Animals cleanup' 									=> array('type' => 'no-date'),
    	'Articles needing more detailed references' 		=> array('type' => 'no-date'),
    	'Articles with incorrect citation syntax'			=> array('type' => 'no-date', 'subcats' => 'only'),
 		'Invalid conservation status' 						=> array('type' => 'no-date'),
    	'Missing taxobox' 									=> array('type' => 'no-date'),
    	'Pages with several capitalization mistakes' 		=> array('type' => 'no-date'),
    	'Persondata templates without short description parameter' => array('type' => 'no-date'),
    	'Plant articles needing a taxobox' 					=> array('type' => 'no-date'),
    	'Proposed moves' 									=> array('type' => 'no-date'),
    	'Redundant taxobox' 								=> array('type' => 'no-date'),
    	'Taxoboxes needing a status system parameter' 		=> array('type' => 'no-date'),
    	'Taxoboxes with an invalid color' 					=> array('type' => 'no-date'),
    	'Taxoboxes with an unrecognised status system' 		=> array('type' => 'no-date'),
    	'Tree of Life cleanup' 								=> array('type' => 'no-date'),
    	'Wikipedia articles needing cleanup after translation' => array('type' => 'no-date'),

    	// since-yearly
    	'Pages with DOIs inactive' 							=> array('type' => 'since-yearly')
    );

    var $dbh_enwiki;
    var $dbh_tools;

    /**
     * Constructor
     *
     * @param PDO $dbh_enwiki
     * @param PDO $dbh_tools
     */
    public function __construct(PDO $dbh_enwiki, PDO $dbh_tools)
    {
    	$this->dbh_enwiki = $dbh_enwiki;
    	$this->dbh_tools = $dbh_tools;
    }

    /**
     * Load the articles in the above categories.
     *
     * @return int Category count
     */
    public function load()
    {
   	$this->dbh_tools->exec('TRUNCATE category');
   	$this->dbh_tools->exec('TRUNCATE categorylinks');

    	$isth = $this->dbh_tools->prepare('INSERT INTO category VALUES (:id, :title, :month, :year)');
    	$count = 0;

    	foreach (self::$categories as $cat => $attribs) {
			$cattype = $attribs['type'];
			$subcatsonly = isset($attribs['subcats']);
			$sqls = array();

			switch ($cattype) {
			    case 'from-monthly':
			    	$param = str_replace(' ', '\_', "$cat from %");
					$sqls[$param] = "SELECT cat_id as id, cat_title as title,
						MONTH(STR_TO_DATE(SUBSTRING_INDEX(SUBSTRING_INDEX(cat_title, '_', -2), '_', 1), '%M')) as month,
						SUBSTRING_INDEX(cat_title, '_', -1) as year
						FROM category WHERE cat_title LIKE ? AND cat_pages - (cat_subcats + cat_files) > 0";

			    	$param = str_replace(' ', '_', $cat);
					$sqls[$param] = "SELECT cat_id as id, cat_title as title,
						NULL as month,
						NULL as year
						FROM category WHERE cat_title = ? AND cat_pages - (cat_subcats + cat_files) > 0";
					break;

			    case 'since-yearly':
			    	$param = str_replace(' ', '\_', "$cat since %");
					$sqls[$param] = "SELECT cat_id as id, cat_title as title,
						NULL as month,
						SUBSTRING_INDEX(cat_title, '_', -1) as year
						FROM category WHERE cat_title LIKE ? AND cat_pages - (cat_subcats + cat_files) > 0";
			    	break;

			    case 'no-date':
			    	$param = str_replace(' ', '_', $cat);

			    	if ($subcatsonly) {
						$sqls[$param] = "SELECT c.cat_id as id, c.cat_title as title,
							NULL as month,
							NULL as year
							FROM category c
							JOIN page AS cat ON c.cat_title = cat.page_title
							JOIN categorylinks AS cl ON cat.page_id = cl.cl_from
							WHERE cl.cl_to = ? AND c.cat_pages - (c.cat_subcats + c.cat_files) > 0";
			    	} else {
			    		$sqls[$param] = "SELECT cat_id as id, cat_title as title,
							NULL as month,
							NULL as year
							FROM category WHERE cat_title = ? AND cat_pages - (cat_subcats + cat_files) > 0";
			    	}
			    	break;
			}

			foreach ($sqls as $param => $sql) {
		    	$sth = $this->dbh_enwiki->prepare($sql);
		    	$sth->bindParam(1, $param);
    			$sth->setFetchMode(PDO::FETCH_ASSOC);
		    	$sth->execute();

		    	while($row = $sth->fetch()) {
		    		$title = $row['title'];
		    		if (! $subcatsonly) $row['title'] = str_replace(' ', '_', $cat);
					$isth->execute($row);
					++$count;

					$this->loadCategoryMembers($title);
		    	}

		    	$sth->closeCursor();
			}
    	}

    	return $count;
    }

    /**
     * Load article ids for a category.
     *
     * @param string $cat Category
     */
    function loadCategoryMembers($cat)
    {
    	$count = 0;
    	$this->dbh_tools->beginTransaction();
    	$isth = $this->dbh_tools->prepare('INSERT INTO categorylinks VALUES (:cl_from, :cat_id)');
    	$sql = "SELECT cl.cl_from, cat.cat_id
				FROM categorylinks cl, category cat
				WHERE cat.cat_title = ? AND cl.cl_to = cat.cat_title AND cl.cl_type = 'page'";

		$sth = $this->dbh_enwiki->prepare($sql);
		$sth->bindParam(1, $cat);
    	$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute();

		while($row = $sth->fetch()) {
			++$count;
		    if ($count % 1000 == 0) {
    			$this->dbh_tools->commit();
    			$this->dbh_tools->beginTransaction();
    		}
			$isth->execute($row);
		}

		$sth->closeCursor();
    	$this->dbh_tools->commit();
    }
}