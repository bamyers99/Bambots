<?php
/**
 Copyright 2015 Myers Enterprises II

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

namespace com_brucemyers\test\DataflowBot;

use PDO;

class CreateTablesACPC
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

    	$sql = "CREATE TABLE IF NOT EXISTS `categorylinks` (
		  `cl_from` int(10) unsigned NOT NULL DEFAULT '0',
		  `cl_to` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
		  `cl_type` enum('page','subcat','file') NOT NULL DEFAULT 'page',
		  UNIQUE KEY `cl_from` (`cl_from`,`cl_to`),
		  KEY `cl_sortkey` (`cl_to`,`cl_type`,`cl_from`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `page` (
		  `page_id` int(10) unsigned NOT NULL,
		  `page_namespace` int(11) NOT NULL,
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  PRIMARY KEY (`page_id`),
		  UNIQUE KEY `name_title` (`page_namespace`,`page_title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	// load enwiki

   		$dbh_enwiki->exec('TRUNCATE page');
   		$dbh_enwiki->exec('TRUNCATE categorylinks');

   		$dbh_enwiki->exec("INSERT INTO page VALUES (1, 1, 'Apple')");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (2, 11, 'Fruit')");

   		$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (1, 'B-Class_Michigan_articles', 'page')");
   		$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (1, 'C-Class_Species_articles', 'page')");
   		$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (1, 'C-Class_Food_articles', 'page')");
   		$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (2, 'Unassessed_Michigan_articles', 'page')");
    }
}