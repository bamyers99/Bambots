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

class CreateTablesRRC
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

    	$sql = "CREATE TABLE IF NOT EXISTS `redirect` (
 			`rd_from` int unsigned NOT NULL default 0 PRIMARY KEY,
 			`rd_namespace` int NOT NULL default 0,
 			`rd_title` varchar(255) binary NOT NULL default '',
 			`rd_interwiki` varchar(32) default NULL,
 			`rd_fragment` varchar(255) binary default NULL
 		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_enwiki->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `page` (
		  `page_id` int(10) unsigned NOT NULL,
		  `page_namespace` int(11) NOT NULL,
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
 		  `page_is_redirect` tinyint unsigned NOT NULL default 0,
    	  PRIMARY KEY (`page_id`),
		  UNIQUE KEY `name_title` (`page_namespace`,`page_title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	// load enwiki

   		$dbh_enwiki->exec('TRUNCATE page');
   		$dbh_enwiki->exec('TRUNCATE redirect');

   		$dbh_enwiki->exec("INSERT INTO page VALUES (1, 0, 'Apple', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (2, 0, 'Fruit', 1)");

    	$dbh_enwiki->exec("INSERT INTO redirect VALUES (2,0,'Pom','','')");
    }
}