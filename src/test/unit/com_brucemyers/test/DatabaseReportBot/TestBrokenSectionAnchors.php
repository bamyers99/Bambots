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

use com_brucemyers\DatabaseReportBot\Reports\BrokenSectionAnchors;
use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\test\DatabaseReportBot\CreateTablesBSA;
use UnitTestCase;
use PDO;
use com_brucemyers\RenderedWiki\RenderedWiki;

DEFINE('ENWIKI_HOST', 'DatabaseReportBot.enwiki_host');
DEFINE('TOOLS_HOST', 'DatabaseReportBot.tools_host');
DEFINE('WIKIDATA_HOST', 'DatabaseReportBot.wikidata_host');

class TestBrokenSectionAnchors extends UnitTestCase
{

    public function notestGenerate()
    {
    	$enwiki_host = Config::get(ENWIKI_HOST);
    	$user = Config::get(DatabaseReportBot::LABSDB_USERNAME);
    	$pass = Config::get(DatabaseReportBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p;charset=utf8mb4", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$tools_host = Config::get(TOOLS_HOST);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__DatabaseReportBot;charset=utf8mb4", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$wikidata_host = Config::get(WIKIDATA_HOST);
    	$dbh_wikidata = new PDO("mysql:host=$wikidata_host;dbname=wikidatawiki_p;charset=utf8mb4", $user, $pass);
    	$dbh_wikidata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $url = Config::get(MediaWiki::WIKIURLKEY);
        $wiki = new MediaWiki($url);
        $url = Config::get(RenderedWiki::WIKIRENDERURLKEY);
        $renderedwiki = new RenderedWiki($url);

    	new CreateTablesBSA($dbh_enwiki);
    	$this->_createDataFiles();

    	$redirects = array('[[Anesthesia record]]', '[[Anesthesia not found]]', '[[Xfburn]]');
    	$targets = array('[[Anesthesia#Anesthetic monitoring]]', '[[Anesthesia#Anesthetic not found]]', '[[Xfce#Applications]]');
    	$grouped_redirects = array('[[Xfburn]]#Applications', '[[Anesthesia record]]#Anesthetic monitoring');
    	$grouped_targets = array('[[Xfce]]', '[[Anesthesia]]');

    	$apis = array(
    	    'dbh_wiki' => $dbh_enwiki,
    		'wiki_host' => $enwiki_host,
    		'dbh_tools' => $dbh_tools,
    		'tools_host' => $tools_host,
    		'dbh_wikidata' => $dbh_wikidata,
    		'data_host' => $wikidata_host,
    		'mediawiki' => $wiki,
    		'renderedwiki' => $renderedwiki,
    		'datawiki' => null,
    		'user' => $user,
    		'pass' => $pass
    	);

		$report = new BrokenSectionAnchors();
		$report->init($apis, array());
		$groups = $report->getRows($apis);

		//echo count($groups['groups']['Newest']) . "\n";
		$this->assertEqual(count($groups['groups']['Newest']), 1, 'Wrong number of Newest broken section anchors');
		//echo count($groups['groups']['Older (partial list)']) . "\n";
		$this->assertEqual(count($groups['groups']['Older (partial list)']), 1, 'Wrong number of Older broken section anchors');
		//echo count($groups['groups']['Grouped by target page (partial list)']) . "\n";
		$this->assertEqual(count($groups['groups']['Grouped by target page (partial list)']), 2, 'Wrong number of grouped broken section anchors');
		//print_r($groups);

		$row = reset($groups['groups']['Newest']);
		$this->assertTrue(in_array($row[0], $redirects), 'Wrong redirect page title 1');
		$this->assertTrue(in_array($row[1], $targets), 'Wrong target page 1');

		$row = reset($groups['groups']['Older (partial list)']);
		$this->assertTrue(in_array($row[0], $redirects), 'Wrong redirect page title 2');
		$this->assertTrue(in_array($row[1], $targets), 'Wrong target page 2');

		$row = reset($groups['groups']['Grouped by target page (partial list)']);
		$this->assertTrue(in_array($row[0], $grouped_redirects), 'Wrong redirect page title 3');
		$this->assertTrue(in_array($row[1], $grouped_targets), 'Wrong target page 3');
    }

    protected function _createDataFiles()
    {
    	$outputDir = Config::get(DatabaseReportBot::OUTPUTDIR);
    	$outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
    	$outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
    	$outputDir .= DIRECTORY_SEPARATOR;

    	$hndl = fopen($outputDir . 'wikiviews', 'w');
    	fwrite($hndl, "Anesthesia_record 45 0\n");
    	fwrite($hndl, "Anesthesia_not_found 34 0\n");
    	fwrite($hndl, "Xfburn 15 0\n");
    	fclose($hndl);

    	$hndl = fopen($outputDir . 'brokensectionanchors', 'w');
    	fwrite($hndl, "Xfburn\n");
    	fclose($hndl);
    }

    public function testAnchor()
    {
        $page = '<span id="Season_3_.281995.E2.80.9396.29"></span><span class="mw-headline" id="Season_3_(1995–96)">Season 3 (1995–96) <span id="Season_3:_1995–96"></span></span>';
        $fragment = 'Season 3: 1995–96';
        $fragment = str_replace(' ', '_', $fragment);

        $escfragment = BrokenSectionAnchors::escape_fragment($fragment);
        $escfragment = preg_quote($escfragment, '!');

        $found = preg_match("!id\s*=\s*['\"]{$escfragment}['\"]!u", $page);

        // try without escaping due to <span id= tag embedded directly into wikitext heading.
        if (! $found) {
            $tempfragment = preg_quote($fragment, '!');
            $found = preg_match("!id\s*=\s*['\"]{$tempfragment}['\"]!u", $page);
        }

        $this->assertTrue($found, 'Frament not found');
    }
}