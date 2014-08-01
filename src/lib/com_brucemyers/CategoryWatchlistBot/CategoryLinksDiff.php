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
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\MySQLDate;

class CategoryLinksDiff
{
	var $dbh_tools;
	var $dbh_tools2;
	var $wiki_host;
	var $outputdir;
	var $asof;
	protected $user;
	protected $pass;

    /**
     * Constructor
     *
     * @param string $wiki_host, empty = $wikiname.labsdb
     * @param PDO $dbh_tools
     * @param string $outputdir
     */
     public function __construct($wiki_host, PDO $dbh_tools, $outputdir, $user, $pass, $asof, $tools_host)
    {
        $this->wiki_host = $wiki_host;
        $this->dbh_tools = $dbh_tools;
        $this->outputdir = $outputdir;
    	$this->user = $user;
    	$this->pass = $pass;
    	$this->asof = MySQLDate::toMySQLDatetime($asof);
    	$this->dbh_tools2 = new PDO("mysql:host=$tools_host;dbname=s51454__CategoryWatchlistBot", $user, $pass);
    	$this->dbh_tools2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

	/**
     * Process a wiki.
     *
     * @param string $wikiname
     * @param array $wikidata
     * @return int Category count
     */
    function processWiki($wikiname, $wikidata)
    {
    	$totalcats = 0;
    	$wiki_host = $this->wiki_host;
    	if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";
    	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p", $this->user, $this->pass);
    	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_diffs` (
		       `diffdate` timestamp,
		       `plusminus` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			   `pageid` int unsigned NOT NULL,
			   `category` varchar(255) binary NOT NULL,
			   UNIQUE KEY `categoryplus` (`category`, `diffdate`, `plusminus`, `pageid`)
			   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->dbh_tools->exec($sql);

        // Add the wiki table entry if needed
    	$sth = $this->dbh_tools->prepare("SELECT * FROM wikis WHERE wikiname = ?");
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if (! $sth->fetch(PDO::FETCH_ASSOC)) {
    		$sth = $this->dbh_tools->prepare('INSERT INTO wikis (wikiname, wikititle, wikidomain) VALUES (?,?,?)');
    		$sth->execute(array($wikiname, $wikidata['title'], $wikidata['domain']));
    	}

    	// Recalc oldest 10 category trees if catcount >= 0
    	$sth = $this->dbh_tools->prepare('SELECT id FROM querys WHERE wikiname = ? AND catcount >= 0 ORDER BY lastrecalc LIMIT 10');
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	$results = $sth->fetchAll(PDO::FETCH_ASSOC);
    	$ids = array();
    	foreach ($results as $row) {
    		$ids[] = $row['id'];
    	}

    	if (! empty($ids)) {
    		$ids = implode(',', $ids);

    		$sth = $this->dbh_tools->prepare("UPDATE querys SET catcount = ? WHERE id IN ($ids)");
    		$sth->bindValue(1, QueryCats::CATEGORY_COUNT_RECALC);
    		$sth->execute();
    	}

    	// Calc category tress for QueryCats::CATEGORY_COUNT_RECALC and QueryCats::CATEGORY_COUNT_UNKNOWN
    	$querycats = new QueryCats($dbh_wiki, $this->dbh_tools2);

    	$sth = $this->dbh_tools->prepare('SELECT id, params, catcount FROM querys WHERE wikiname = ? AND catcount IN (?,?)');
    	$sth->bindParam(1, $wikiname);
    	$sth->bindValue(2, QueryCats::CATEGORY_COUNT_RECALC);
    	$sth->bindValue(3, QueryCats::CATEGORY_COUNT_UNKNOWN);
    	$sth->execute();
    	$sth->setFetchMode(PDO::FETCH_ASSOC);

    	while ($row = $sth->fetch()) {
    		$id = $row['id'];
    		$params = unserialize($row['params']);
    		$catcount = $row['catcount'];

    		$cats = $querycats->calcCats($params, $catcount == QueryCats::CATEGORY_COUNT_RECALC);
    		$catcount = $cats['catcount'];
    		if ($catcount > 0) {
    			$querycats->saveCats($id, $cats['cats']);
    		}

    		$isth = $this->dbh_tools2->prepare("UPDATE querys SET catcount = $catcount, lastrecalc = ? WHERE id = $id");
    		$isth->bindParam(1, $this->asof);
    		$isth->execute();
    	}

    	// Get the previous run date
    	$sth = $this->dbh_tools->prepare('SELECT rundate FROM runs WHERE wikiname = ? ORDER BY rundate desc LIMIT 1');
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    		$prevrun = $row['rundate'];
    	} else {
    		$prevrun = MySQLDate::toMySQLDatetime(strtotime('-1 day', MySQLDate::toPHP($this->asof)));
    	}

    	// Get the categories watched for this wiki
    	$catsth = $this->dbh_tools->prepare('SELECT DISTINCT qc.category FROM querys q, querycats qc WHERE q.wikiname = ? AND q.id = qc.queryid');
    	$catsth->bindParam(1, $wikiname);
    	$catsth->execute();
    	$catsth->setFetchMode(PDO::FETCH_ASSOC);

		$this->dbh_tools2->beginTransaction();
		$isth = $this->dbh_tools2->prepare("INSERT INTO {$wikiname}_diffs (diffdate, plusminus, pageid, category) VALUES (?,?,?,?)");
		$insert_count = 0;

		$sth = $dbh_wiki->prepare("SELECT cl_from FROM categorylinks WHERE cl_to = ? AND cl_timestamp > ? AND cl_timestamp <= ?");

		while ($catrow = $catsth->fetch()) {
			++$totalcats;
			$category = $catrow['category'];

			// Load category additions
			$sth->bindParam(1, $category);
			$sth->bindParam(2, $prevrun);
			$sth->bindParam(3, $this->asof);
			$sth->execute();
			$sth->setFetchMode(PDO::FETCH_ASSOC);

			while ($row = $sth->fetch()) {
				++$insert_count;
				if ($insert_count % 1000 == 0) {
					$this->dbh_tools2->commit();
					$this->dbh_tools2->beginTransaction();
				}

	    		$isth->bindParam(1, $this->asof);
	    		$isth->bindValue(2, '+');
				$isth->bindParam(3, $row['cl_from']);
	    		$isth->bindParam(4, $category);
	    		$isth->execute();
			}

			$sth->closeCursor();
		}

    	$this->dbh_tools2->commit();
    	$catsth->closeCursor();

		// Update the runs table
		$isth = $this->dbh_tools->prepare("INSERT INTO runs (wikiname, rundate) VALUES (?,?)");
		$isth->bindParam(1, $wikiname);
		$isth->bindParam(2, $this->asof);
		$isth->execute();

		return $totalcats;
    }
}
