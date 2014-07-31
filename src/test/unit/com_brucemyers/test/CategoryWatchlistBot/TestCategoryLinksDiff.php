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
    	$asof_date = getdate();

    	new CreateTables($dbh_wiki, $dbh_tools);

    	$wikiname = 'enwiki';
    	$wikititle = 'English Wikipedia';

    	// Set up the previous cat links
		$output = <<<EOT
0000000001|Michigan_articles_by_quality
0000000002|Michigan_articles_by_quality
0000000003|All_articles_needing_coordinates
0000000004|C-Class_Michigan_articles
0000000004|Top-importance_Michigan_articles
0000000005|Articles_needing_cleanup_from_June_2013
0000000005|Articles_needing_cleanup_from_March_2013
0000000006|NA-importance_Michigan_articles
0000000006|Unassessed_Michigan_articles
0000000008|NA-importance_Michigan_articles
0000000008|Unassessed_Michigan_articles
0000000009|Articles_needing_cleanup_from_May_2013
0000000010|NA-importance_Michigan_articles
0000000010|Unassessed_Michigan_articles
0000000301|Articles_needing_cleanup_from_May_2013
0000000301|Featured_articles
0000000301|Pages_with_DOIs_inactive_since_2013
0000000303|Articles_needing_cleanup_from_June_2013
0000000303|Featured_articles
0000000303|Pages_with_DOIs_inactive_since_2013
EOT;

		$dumppath = $outputdir . "$wikiname.dump";
		file_put_contents($dumppath, $output);

    	$catLinksDiff = new CategoryLinksDiff($wiki_host, $dbh_tools, $outputdir, $user, $pass, $asof_date);

    	$catLinksDiff->processWiki($wikiname, $wikititle);

    	// Check wikis table
        $sql = 'SELECT * FROM wikis';
    	$sth = $dbh_tools->query($sql);
    	if ($row = $sth->fetch()) {
    		$this->assertEqual($row['wikiname'], $wikiname, 'Bad wikiname');
    		$this->assertEqual($row['wikititle'], $wikititle, 'Bad wikititle');
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

    	$this->assertEqual($minuscnt, 3, 'Bad minus count');
    	$this->assertEqual($pluscnt, 3, 'Bad plus count');
    }
}