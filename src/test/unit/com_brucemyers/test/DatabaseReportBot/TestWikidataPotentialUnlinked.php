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

use com_brucemyers\DatabaseReportBot\Reports\WikidataPotentialUnlinked;
use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\test\DatabaseReportBot\CreateTablesWPU;
use UnitTestCase;
use PDO;
use com_brucemyers\RenderedWiki\RenderedWiki;
use com_brucemyers\MediaWiki\WikidataWiki;

DEFINE('ENWIKI_HOST', 'DatabaseReportBot.enwiki_host');
DEFINE('TOOLS_HOST', 'DatabaseReportBot.tools_host');
DEFINE('WIKIDATA_HOST', 'DatabaseReportBot.wikidata_host');

class TestWikidataPotentialUnlinked extends UnitTestCase
{

    public function testGenerate()
    {
    	$enwiki_host = Config::get(ENWIKI_HOST);
    	$user = Config::get(DatabaseReportBot::LABSDB_USERNAME);
    	$pass = Config::get(DatabaseReportBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$tools_host = Config::get(TOOLS_HOST);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__DatabaseReportBot", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$wikidata_host = Config::get(WIKIDATA_HOST);
    	$dbh_wikidata = new PDO("mysql:host=$wikidata_host;dbname=wikidatawiki_p", $user, $pass);
    	$dbh_wikidata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $url = Config::get(MediaWiki::WIKIURLKEY);
        $wiki = new MediaWiki($url);
        $url = Config::get(RenderedWiki::WIKIRENDERURLKEY);
        $renderedwiki = new RenderedWiki($url);
        $datawiki = new WikidataWiki();

    	new CreateTablesWPU($dbh_enwiki, $dbh_wikidata);

    	$pages = array('[[Fred Smith]] (redirect)', '[[Ted Jones]]');
    	$alreadys = array('', '[https://www.wikidata.org/wiki/Q410196 Q410196]');

    	$apis = array(
    	    'dbh_wiki' => $dbh_enwiki,
    		'wiki_host' => $enwiki_host,
    		'dbh_tools' => $dbh_tools,
    		'tools_host' => $tools_host,
    		'dbh_wikidata' => $dbh_wikidata,
    		'data_host' => $wikidata_host,
    		'mediawiki' => $wiki,
    		'renderedwiki' => $renderedwiki,
    		'datawiki' => $datawiki,
    		'user' => $user,
    		'pass' => $pass
    	);

    	$report = new WikidataPotentialUnlinked();
		$rows = $report->getRows($apis);
		unset ($rows['linktemplate']);

		$this->assertEqual(count($rows), 2, 'Wrong number of potential unlinkeds');

		$row = $rows[0];
		$alreadyResult1 = $row[2];
		$this->assertTrue(in_array($row[0], $pages), 'Wrong page title 1');
		$this->assertTrue(in_array($row[2], $alreadys), 'Wrong already 1');

		$row = $rows[1];
		$alreadyResult2 = $row[2];
		$this->assertTrue(in_array($row[0], $pages), 'Wrong page title 2');
		$this->assertTrue(in_array($row[2], $alreadys), 'Wrong already 2');

		$this->assertNotEqual($alreadyResult1, $alreadyResult2, 'Missing already link');
    }
}