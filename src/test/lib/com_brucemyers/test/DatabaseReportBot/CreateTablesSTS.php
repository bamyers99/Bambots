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

class CreateTablesSTS
{
	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 */
    public function __construct(PDO $dbh_enwiki)
    {
    	// enwiki
    	$sql = "CREATE TABLE IF NOT EXISTS `category` (
		  `cat_id` int(10) unsigned NOT NULL,
		  `cat_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `cat_pages` int(11) NOT NULL DEFAULT '0',
		  `cat_subcats` int(11) NOT NULL DEFAULT '0',
		  `cat_files` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`cat_id`),
		  UNIQUE KEY `cat_title` (`cat_title`)
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

   		$dbh_enwiki->exec('TRUNCATE category');
   		$dbh_enwiki->exec('TRUNCATE page');

   		// < 30, no subcats
    	$dbh_enwiki->exec("INSERT INTO category VALUES (1,'Steubenville-Follansbee_Stubs_players',1,0,0)");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (1,14,'Steubenville-Follansbee_Stubs_players')");

    	// < 60, no subcats
    	$dbh_enwiki->exec("INSERT INTO category VALUES (2,'Systems_theory_stubs',30,0,0)");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (2,14,'Systems_theory_stubs')");

    	// < 200
        $dbh_enwiki->exec("INSERT INTO category VALUES (3,'Government_agency_stubs',4,2,0)");
        $dbh_enwiki->exec("INSERT INTO page VALUES (3,14,'Government_agency_stubs')");

        // < 400
        $dbh_enwiki->exec("INSERT INTO category VALUES (4,'Paleontology_stubs',200,21,0)");
        $dbh_enwiki->exec("INSERT INTO page VALUES (4,14,'Paleontology_stubs')");

        // < 600
       	$dbh_enwiki->exec("INSERT INTO category VALUES (5,'1910s_comedy_film_stubs',405,0,0)");
       	$dbh_enwiki->exec("INSERT INTO page VALUES (5,14,'1910s_comedy_film_stubs')");

       	// < 800
       	$dbh_enwiki->exec("INSERT INTO category VALUES (6,'Crime_novel_stubs',606,0,0)");
       	$dbh_enwiki->exec("INSERT INTO page VALUES (6,14,'Crime_novel_stubs')");

       	// < 1000
       	$dbh_enwiki->exec("INSERT INTO category VALUES (7,'Computing_stubs',813,15,0)");
       	$dbh_enwiki->exec("INSERT INTO page VALUES (7,14,'Computing_stubs')");

       	// < 1200
       	$dbh_enwiki->exec("INSERT INTO category VALUES (8,'Theclinae_stubs',1072,0,0)");
       	$dbh_enwiki->exec("INSERT INTO page VALUES (8,14,'Theclinae_stubs')");

       	// >= 1200
       	$dbh_enwiki->exec("INSERT INTO category VALUES (9,'Hadeninae_stubs',1200,2,0)");
       	$dbh_enwiki->exec("INSERT INTO category VALUES (10,'Plant_disease_stubs',1445,1,0)");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (9,14,'Hadeninae_stubs')");
        $dbh_enwiki->exec("INSERT INTO page VALUES (10,14,'Plant_disease_stubs')");
    }
}