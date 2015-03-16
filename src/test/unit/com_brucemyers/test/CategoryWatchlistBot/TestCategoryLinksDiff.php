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
use com_brucemyers\CategoryWatchlistBot\ServiceManager;
use com_brucemyers\test\CategoryWatchlistBot\CreateTables;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\MySQLDate;
use UnitTestCase;
use PDO;
use Mock;

class TestCategoryLinksDiff extends UnitTestCase
{

    public function testDiffLoad()
    {
    	$outputdir = Config::get(CategoryWatchlistBot::OUTPUTDIR);
    	$outputdir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputdir);
    	$outputdir = preg_replace('!(/|\\\\)$!', '', $outputdir); // Drop trailing slash
    	$outputdir .= DIRECTORY_SEPARATOR;

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = &new \MockMediaWiki();

        $mediaWiki->returns('getRevisionsText', array(
        	'Talk:Mackinac Island' => array(1, "<!-- [[Category:New pages]] -->
        		[[Category:Unassessed Michigan articles]]
        		[[category:NA-importance_Michigan_articles]]"),
        	'Lansing, Michigan' => array(2, "{{WikiProject Michigan}}[[Category:Articles needing cleanup from May 2013]]",
        		3, "[[Category:Articles needing cleanup from May 2013]]"),
        	'Earth' => array(4, "[[Category:Featured_articles]][[Category:Pages_with_DOIs_inactive_since_2013]]",
        		5, "[[Category:Featured articles]][[Category:Pages_with_DOIs_inactive_since_2013]][[Category:Articles_needing_cleanup_from_May_2013]]")
        ));

    	$serviceMgr = new ServiceManager();
    	$dbh_wiki = $serviceMgr->getDBConnection('enwiki');
    	$dbh_tools = $serviceMgr->getDBConnection('tools');

    	Mock::generate('com_brucemyers\\CategoryWatchlistBot\\ServiceManager', 'MockServiceManager');
    	$serviceMgr = &new \MockServiceManager();
    	$serviceMgr->returns('getMediaWiki', $mediaWiki);
    	$serviceMgr->returns('getDBConnection', $dbh_wiki, array('enwiki'));
    	$serviceMgr->returns('getDBConnection', $dbh_tools, array('tools'));

    	$asof_date = time();

    	new CreateTables($dbh_wiki, $dbh_tools);

    	$wikiname = 'enwiki';
    	$wikidata = array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org');
    	$ts = MySQLDate::toMySQLDatetime($asof_date);

    	//Set up a query and querycats
    	$dbh_tools->exec("INSERT INTO querys VALUES (1,'enwiki','A','','$ts','$ts')");

    	$catLinksDiff = new CategoryLinksDiff($serviceMgr, $outputdir, $asof_date);

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

    	$this->assertEqual($minuscnt, 1, 'Bad minus count');
    	$this->assertEqual($pluscnt, 3, 'Bad plus count');
    }
}