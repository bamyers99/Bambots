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

namespace com_brucemyers\test\CategoryWatchlistBot;

use com_brucemyers\CategoryWatchlistBot\CategoryLinksDiff;
use com_brucemyers\CategoryWatchlistBot\CategoryWatchlistBot;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\test\CategoryWatchlistBot\CreateTables;
use com_brucemyers\Util\MySQLDate;
use UnitTestCase;
use PDO;

class TestCategoryLinksDiff extends UnitTestCase
{

    public function testDiffLoad()
    {
    	$wiki_host = Config::get(CategoryWatchlistBot::WIKI_HOST);
    	$tools_host = Config::get(CategoryWatchlistBot::TOOLS_HOST);
    	$user = Config::get(CategoryWatchlistBot::LABSDB_USERNAME);
    	$pass = Config::get(CategoryWatchlistBot::LABSDB_PASSWORD);

    	$outputdir = Config::get(CategoryWatchlistBot::OUTPUTDIR);
    	$outputdir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputdir);
    	$outputdir = preg_replace('!(/|\\\\)$!', '', $outputdir); // Drop trailing slash
    	$outputdir .= DIRECTORY_SEPARATOR;

    	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=enwiki_p", $user, $pass);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CategoryWatchlistBot", $user, $pass);
    	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$asof_date = time();

    	new CreateTables($dbh_wiki, $dbh_tools);

    	$wikiname = 'enwiki';
    	$wikidata = array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org');
    	$ts = MySQLDate::toMySQLDatetime($asof_date);

    	// Set up the new cat links
    	$sth = $dbh_wiki->prepare("UPDATE categorylinks SET cl_timestamp = '$ts' WHERE cl_from = ? AND cl_to = ?");
    	$sth->execute(array(4, 'B-Class_Michigan_articles'));
    	$sth->execute(array(5, 'Articles_needing_cleanup_from_May_2013'));
    	$sth->execute(array(303, 'Articles_needing_cleanup_from_May_2013'));

    	//Set up a query and querycats
    	$dbh_tools->exec("INSERT INTO querys VALUES (1,'enwiki','A','','$ts',4)");
    	$dbh_tools->exec("INSERT INTO querycats VALUES (1,'Michigan_articles_by_quality')");
    	$dbh_tools->exec("INSERT INTO querycats VALUES (1,'B-Class_Michigan_articles')");
    	$dbh_tools->exec("INSERT INTO querycats VALUES (1,'Unassessed_Michigan_articles')");
    	$dbh_tools->exec("INSERT INTO querycats VALUES (1,'Articles_needing_cleanup_from_May_2013')");

    	$catLinksDiff = new CategoryLinksDiff($wiki_host, $dbh_tools, $outputdir, $user, $pass, $asof_date, $tools_host);

    	$catLinksDiff->processWiki($wikiname, $wikidata);

    	// Check wikis table
        $sql = 'SELECT * FROM wikis';
    	$sth = $dbh_tools->query($sql);
    	if ($row = $sth->fetch()) {
    		$this->assertEqual($row['wikiname'], $wikiname, 'Bad wikiname');
    		$this->assertEqual($row['wikititle'], $wikidata['title'], 'Bad wikititle');
    		$this->assertEqual($row['wikidomain'], $wikidata['domain'], 'Bad wikidomain');
    	} else {
    		$this->fail('wikis table empty');
    	}

    	//Check enwiki_diffs table
    	$minuscnt = 0;
    	$pluscnt = 0;

        $sql = 'SELECT * FROM enwiki_diffs';
    	$sth = $dbh_tools->query($sql);

    	while ($row = $sth->fetch()) {
    		if ($row['plusminus'] == '-') ++$minuscnt;
    		elseif ($row['plusminus'] == '+') ++$pluscnt;
    		else $this->fail('Invalid plusminus = ' . $row['plusminus']);
    	}

    	//$this->assertEqual($minuscnt, 3, 'Bad minus count');
    	$this->assertEqual($pluscnt, 3, 'Bad plus count');
    }
}