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

class CreateTablesWPU
{
	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 * @param PDO $dbh_wikidata
	 */
    public function __construct(PDO $dbh_enwiki, PDO $dbh_wikidata)
    {
   		$dbh_enwiki->exec('DROP TABLE page');

    	// enwiki
    	$sql = "CREATE TABLE IF NOT EXISTS `page` (
		  `page_id` int(10) unsigned NOT NULL,
		  `page_namespace` int(11) NOT NULL,
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `page_is_redirect` tinyint unsigned NOT NULL default 0,
		  PRIMARY KEY (`page_id`),
		  UNIQUE KEY `name_title` (`page_namespace`,`page_title`)
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

    	$sql = "CREATE TABLE IF NOT EXISTS wb_entity_per_page (
  			`epp_entity_id`                  INT unsigned        NOT NULL,
  			`epp_entity_type`                VARBINARY(32)       NOT NULL,
  			`epp_page_id`                    INT unsigned        NOT NULL,
  			`epp_redirect_target`            VARBINARY(255)      DEFAULT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_wikidata->exec($sql);

    	// load dbs

   		$dbh_wikidata->exec('TRUNCATE wb_items_per_site');
   		$dbh_wikidata->exec('TRUNCATE wb_entity_per_page');

    	// With enwiki
    	$dbh_enwiki->exec("INSERT INTO page VALUES (1,0,'Boron_nitride',0)");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (1,410193,'enwiki','Boron nitride')");
    	$dbh_wikidata->exec("INSERT INTO wb_entity_per_page VALUES (410193,'item',1,NULL)");

    	// With dewiki
    	$dbh_enwiki->exec("INSERT INTO page VALUES (2,0,'Fred_Smith',1)");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (2,410194,'dewiki','Fred Smith')");
    	$dbh_wikidata->exec("INSERT INTO wb_entity_per_page VALUES (410194,'item',2,NULL)");

    	// With dewiki and page already has wikidata
    	$dbh_enwiki->exec("INSERT INTO page VALUES (3,0,'Ted_Jones',0)");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (3,410195,'dewiki','Ted Jones')");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (4,410196,'enwiki','Ted Jones')");
    	$dbh_wikidata->exec("INSERT INTO wb_entity_per_page VALUES (410195,'item',3,NULL)");

    	// With dewiki and page already has wikidata and is redirect
    	$dbh_enwiki->exec("INSERT INTO page VALUES (4,0,'Jane_Doe',1)");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (5,410197,'dewiki','Jane Doe')");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (6,410198,'enwiki','Jane Doe')");
    	$dbh_wikidata->exec("INSERT INTO wb_entity_per_page VALUES (410197,'item',4,NULL)");

    	// With dewiki, single word
    	$dbh_enwiki->exec("INSERT INTO page VALUES (5,0,'Toast',0)");
    	$dbh_wikidata->exec("INSERT INTO wb_items_per_site VALUES (7,410199,'dewiki','Toast')");
    	$dbh_wikidata->exec("INSERT INTO wb_entity_per_page VALUES (410199,'item',5,NULL)");
    }
}