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

use PDO;

class CreateTablesINL
{
	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_enwiki)
    {
    	// enwiki
   		$dbh_enwiki->exec('DROP TABLE page');

    	$sql = "CREATE TABLE IF NOT EXISTS `page` (
		  `page_id` int(10) unsigned NOT NULL,
		  `page_namespace` int(11) NOT NULL,
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  PRIMARY KEY (`page_id`),
		  UNIQUE KEY `name_title` (`page_namespace`,`page_title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS templatelinks (
  			`tl_from` int unsigned NOT NULL default 0,
    		`tl_from_namespace` int NOT NULL default 0,
    		`tl_namespace` int NOT NULL default 0,
    		`tl_title` varchar(255) binary NOT NULL default ''
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	// load enwiki

   		$dbh_enwiki->exec('TRUNCATE page');
   		$dbh_enwiki->exec('TRUNCATE templatelinks');

    	// Navbox - No navbar
    	$dbh_enwiki->exec("INSERT INTO page VALUES (1,10,'NavboxNoNavbar')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (1,10,10,'Navbox')");

    	// Navbox - Good name
    	$dbh_enwiki->exec("INSERT INTO page VALUES (2,10,'NavboxGoodName')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (2,10,10,'Navbox')");

    	// Navbox - Bad name
    	$dbh_enwiki->exec("INSERT INTO page VALUES (3,10,'NavboxBadName')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (3,10,10,'Navbox')");

    	// Navbox - with columns
    	$dbh_enwiki->exec("INSERT INTO page VALUES (4,10,'NavboxWithColumns')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (4,10,10,'Navbox')");

    	// BS-headerGoodName
    	$dbh_enwiki->exec("INSERT INTO page VALUES (5,10,'BS-headerBadName')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (5,10,10,'BS-header')");

    	// BS-mapNoTitle
    	$dbh_enwiki->exec("INSERT INTO page VALUES (6,10,'BS-mapNoTitle')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (6,10,10,'BS-map')");

    	// SidebarNavbarOff
    	$dbh_enwiki->exec("INSERT INTO page VALUES (7,10,'SidebarNavbarOff')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (7,10,10,'Sidebar')");
    }
}