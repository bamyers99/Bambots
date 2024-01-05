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
use com_brucemyers\Util\Config;
use com_brucemyers\test\CleanupWorklistBot\CreateTables;
use UnitTestCase;
use PDO;

class TestProjectPages extends UnitTestCase
{

    public function testPageLoad()
    {
    	$tools_host = Config::get(CleanupWorklistBot::TOOLS_HOST);
    	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);

    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	$tables = new CreateTables($dbh_tools);
    	$mediawiki = $tables->getMediawiki();

    	$categories = new Categories($mediawiki, $user, $pass, $tools_host);
    	$categories->load(false);

    	$project_pages = new ProjectPages($mediawiki, $user, $pass, $tools_host);

    	$category = 'Michigan';
    	list($page_count, $assessment_count) = $project_pages->load($category, 0, $category, '');
    	$this->assertEqual($page_count, 4, "Wrong page count for $category $page_count != 4");

    	$category = 'India';
    	list($page_count, $assessment_count) = $project_pages->load($category, 1, $category, '');
    	$this->assertEqual($page_count, 1, "Wrong page count for $category $page_count != 1");

    	$category = 'Good_article_nominees';
    	list($page_count, $assessment_count) = $project_pages->load($category, 2, $categoryv);
    	$this->assertEqual($page_count, 1, "Wrong page count for $category $page_count != 1");

    	$category = 'Featured articles';
    	list($page_count, $assessment_count) = $project_pages->load($category, 3, $category, '');
    	$this->assertEqual($page_count, 2, "Wrong page count for $category $page_count != 2");
    }
}