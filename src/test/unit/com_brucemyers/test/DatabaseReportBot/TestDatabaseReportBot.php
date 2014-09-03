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

use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\test\DatabaseReportBot\CreateTables;
use com_brucemyers\RenderedWiki\RenderedWiki;
use UnitTestCase;
use PDO;

DEFINE('ENWIKI_HOST', 'DatabaseReportBot.enwiki_host');

class TestDatabaseReportBot extends UnitTestCase
{

    public function testGenerate()
    {
    	$enwiki_host = Config::get(ENWIKI_HOST);
    	$user = Config::get(DatabaseReportBot::LABSDB_USERNAME);
    	$pass = Config::get(DatabaseReportBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $url = Config::get(MediaWiki::WIKIURLKEY);
        $wiki = new MediaWiki($url);
        $url = Config::get(RenderedWiki::WIKIRENDERURLKEY);
        $renderedwiki = new RenderedWiki($url);

    	new CreateTables($dbh_enwiki);

    	$outputDir = Config::get(DatabaseReportBot::OUTPUTDIR);
    	$outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
    	$outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
    	$outputDir .= DIRECTORY_SEPARATOR;
    	$resultwriter = new FileResultWriter($outputDir);

    	$bot = new DatabaseReportBot($resultwriter, $wiki, $renderedwiki, $enwiki_host, 'enwiki_p');

    	$bot->generateReport('BrokenSectionAnchors', 'Wikipedia:Database reports');
    }
}