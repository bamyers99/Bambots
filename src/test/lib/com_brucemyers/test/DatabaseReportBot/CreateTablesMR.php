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

class CreateTablesMR
{
	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 * @param PDO $dbh_wikidata
	 */
    public function __construct(PDO $dbh_enwiki, PDO $dbh_wikidata)
    {
    	// enwiki
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

    	// wikidatawiki
    	$sql = "CREATE TABLE IF NOT EXISTS wb_items_per_site (
  			`ips_row_id` int unsigned NOT NULL,
    		`ips_item_id` int unsigned NOT NULL,
    		`ips_site_id` varchar(32) binary NOT NULL,
    		`ips_site_page` varchar(255) binary NOT NULL,
		  PRIMARY KEY (`ips_row_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_wikidata->exec($sql);

    	// load dbs

   		$dbh_enwiki->exec('TRUNCATE page');
   		$dbh_enwiki->exec('TRUNCATE templatelinks');
   		$dbh_wikidata->exec('TRUNCATE wb_items_per_site');

    	// ChemSpiderID
    	$dbh_enwiki->exec("INSERT INTO page VALUES (1,0,'Boron_nitride')");
    	$dbh_enwiki->exec("INSERT INTO templatelinks VALUES (1,0,10,'Chembox_Identifiers')");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (1,410193,'enwiki','Boron nitride')");
    }
}