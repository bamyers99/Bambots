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

namespace com_brucemyers\test\DatabaseReportBot;

use com_brucemyers\DatabaseReportBot\Reports\DiacriticRedLinks;
use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\test\DatabaseReportBot\CreateTablesBSA;
use UnitTestCase;
use PDO;

DEFINE('ENWIKI_HOST', 'DatabaseReportBot.enwiki_host');
DEFINE('TOOLS_HOST', 'DatabaseReportBot.tools_host');
DEFINE('WIKIDATA_HOST', 'DatabaseReportBot.wikidata_host');

class TestDiacriticRedLinks extends UnitTestCase
{

    public function testDumpredlinks()
    {
    	$enwiki_host = Config::get(ENWIKI_HOST);
    	$user = Config::get(DatabaseReportBot::LABSDB_USERNAME);
    	$pass = Config::get(DatabaseReportBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p;charset=utf8", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$tools_host = Config::get(TOOLS_HOST);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__DatabaseReportBot;charset=utf8", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$wikidata_host = Config::get(WIKIDATA_HOST);
    	$dbh_wikidata = new PDO("mysql:host=$wikidata_host;dbname=wikidatawiki_p;charset=utf8", $user, $pass);
    	$dbh_wikidata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	new CreateTablesDRL($dbh_enwiki);

    	$apis = array(
    			'dbh_wiki' => $dbh_enwiki,
    			'wiki_host' => $enwiki_host,
    			'dbh_tools' => $dbh_tools,
    			'tools_host' => $tools_host,
    			'dbh_wikidata' => $dbh_wikidata,
    			'data_host' => $wikidata_host,
    			'mediawiki' => null,
    			'renderedwiki' => null,
    			'datawiki' => null,
    			'user' => $user,
    			'pass' => $pass
    	);

		$report = new DiacriticRedLinks();
		$params = array('dumpredlinks');
		$report->init($apis, $params);

		$dumppath = DiacriticRedLinks::getDumpPath();
		$hndl = fopen($dumppath, 'r');

		$count = 0;

		while (! feof($hndl)) {
			$title = trim(fgets($hndl));
			if (empty($title)) continue;
			++$count;
		}

		fclose($hndl);

		$this->assertEqual($count, 2, 'Wrong number of red links');
    }
}