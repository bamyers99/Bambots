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

use Exception;
use PDO;

class ProjectPages
{
	var $dbh_enwiki;
	var $dbh_tools;

    public function __construct(PDO $dbh_enwiki, PDO $dbh_tools)
    {
        $this->dbh_enwiki = $dbh_enwiki;
        $this->dbh_tools = $dbh_tools;
    }

    /**
     * Load a projects pages
     *
     * @param string $category
     * @return int Pages loaded
     * @throws Exception
     */
    public function load($category)
    {
    	// Determine the category type
    	$sql = '';

    	// category - x articles by quality (subcats)
    	$sth = $this->dbh_enwiki->prepare('SELECT * FROM category WHERE cat_title = ?');
    	$param = "{$category}_articles_by_quality";
    	$sth->bindParam(1, $param);
    	$sth->execute();

    	if ($sth->fetch(PDO::FETCH_ASSOC)) $sql = '
			SELECT DISTINCT article.page_id as artid, talk.page_id as talkid, article.page_title as title
			FROM page AS article
			JOIN page AS talk ON article.page_title = talk.page_title
			JOIN categorylinks AS cl1 ON talk.page_id = cl1.cl_from
			JOIN page AS cat ON cl1.cl_to = cat.page_title
			JOIN categorylinks AS cl2 ON cat.page_id = cl2.cl_from
			WHERE cl2.cl_to = ?
			AND article.page_namespace = 0
			AND talk.page_namespace = 1
			AND cat.page_namespace = 14';

    	$sth->closeCursor();

    	if (empty($sql)) {
    		// category - WikiProject x articles
    		$sth = $this->dbh_enwiki->prepare('SELECT * FROM category WHERE cat_title = ?');
    		$param = "WikiProject_{$category}_articles";
    		$sth->bindParam(1, $param);
    		$sth->execute();

    		if ($sth->fetch(PDO::FETCH_ASSOC)) $sql = '
				SELECT article.page_id as artid, talk.page_id as talkid, article.page_title as title
 				FROM page AS article
    			JOIN page AS talk ON article.page_title = talk.page_title
				JOIN categorylinks AS cl ON talk.page_id = cl.cl_from
				WHERE cl.cl_to = ?
				AND article.page_namespace = 0
				AND talk.page_namespace = 1';

    		$sth->closeCursor();
    	}

    	if (empty($sql)) {
    		// category - x (talk namespace)
    		$sth = $this->dbh_enwiki->prepare('SELECT * FROM categorylinks as cl, page WHERE cl.cl_from = page.page_id AND
    			page.page_namespace = 1 AND cl.cl_to = ? LIMIT 1');
    		$param = $category;
    		$sth->bindParam(1, $param);
    		$sth->execute();

    		if ($sth->fetch(PDO::FETCH_ASSOC)) $sql = '
				SELECT article.page_id as artid, talk.page_id as talkid, article.page_title as title
				FROM page AS article
				JOIN page AS talk ON article.page_title = talk.page_title
				JOIN categorylinks AS cl ON talk.page_id = cl.cl_from
				WHERE cl.cl_to = ?
				AND article.page_namespace = 0
				AND talk.page_namespace = 1';

    		$sth->closeCursor();
    	}

    	if (empty($sql)) {
    		// category - x (article namespace)
    		$sth = $this->dbh_enwiki->prepare('SELECT * FROM categorylinks as cl, page WHERE cl.cl_from = page.page_id AND
    			page.page_namespace = 0 AND cl.cl_to = ? LIMIT 1');
    		$param = $category;
    		$sth->bindParam(1, $param);
    		$sth->execute();

    		if ($sth->fetch(PDO::FETCH_ASSOC)) $sql = '
				SELECT article.page_id as artid, talk.page_id as talkid, article.page_title as title
				FROM page AS article
				LEFT JOIN page AS talk ON article.page_title = talk.page_title
				JOIN categorylinks AS cl ON article.page_id = cl.cl_from
				WHERE cl.cl_to = ?
				AND article.page_namespace = 0
				AND talk.page_namespace = 1';

    		$sth->closeCursor();
    	}

    	if (empty($sql)) throw new Exception("Category type not found for '$category'");

    	// Load the pages
   		$this->dbh_tools->exec('TRUNCATE page');

    	$sth = $this->dbh_enwiki->prepare($sql);
    	$sth->bindParam(1, $param);
    	$sth->setFetchMode(PDO::FETCH_ASSOC);
    	$sth->execute();

    	$isth = $this->dbh_tools->prepare('INSERT INTO page (article_id, talk_id, page_title) VALUES (:artid, :talkid, :title)');
    	$count = 0;

    	while($row = $sth->fetch()) {
			$isth->execute($row);
			++$count;
    	}

    	$sth->closeCursor();

    	return $count;
    }
}