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

namespace com_brucemyers\test\CleanupWorklistBot;

use com_brucemyers\CleanupWorklistBot\Categories;
use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;
use com_brucemyers\CleanupWorklistBot\ProjectPages;
use com_brucemyers\CleanupWorklistBot\ReportGenerator;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\test\CleanupWorklistBot\CreateTables;
use UnitTestCase;
use PDO;

class TestReports extends UnitTestCase
{

    public function testGenerate()
    {
    	$enwiki_host = Config::get(CleanupWorklistBot::ENWIKI_HOST);
    	$tools_host = Config::get(CleanupWorklistBot::TOOLS_HOST);
    	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	new CreateTables($dbh_enwiki, $dbh_tools);

    	$categories = new Categories($dbh_enwiki, $dbh_tools);
    	$categories->load();

    	$asof_date = date('F j, Y');
    	$outputdir = Config::get(CleanupWorklistBot::HTMLDIR);
    	$urlpath = Config::get(CleanupWorklistBot::URLPATH);

    	$project_pages = new ProjectPages($dbh_enwiki, $dbh_tools);

    	$outputDir = Config::get(CleanupWorklistBot::OUTPUTDIR);
    	$outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
    	$outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
    	$outputDir .= DIRECTORY_SEPARATOR;
    	$resultwriter = new FileResultWriter($outputDir);

    	$repgen = new ReportGenerator($dbh_tools, $outputdir, $urlpath, $asof_date, $resultwriter);

    	$category = 'Michigan';
    	$page_count = $project_pages->load($category);

    	$repgen->generateReports($category, true, $page_count);

    	$category = 'Good_article_nominees';
    	$page_count = $project_pages->load($category);

    	$repgen->generateReports($category, false, $page_count);

    	$category = 'Featured_articles';
    	$page_count = $project_pages->load($category);

    	$repgen->generateReports($category, false, $page_count);

        $category = 'India';
    	$page_count = $project_pages->load($category);

    	$repgen->generateReports($category, false, $page_count);
    }
}