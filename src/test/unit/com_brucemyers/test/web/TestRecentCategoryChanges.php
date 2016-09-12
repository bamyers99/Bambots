<?php
/**
 Copyright 2013 Myers Enterprises II

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

namespace com_brucemyers\test\web;

use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;
use com_brucemyers\Util\Config;
use PDO;
use UnitTestCase;

class TestRecentCategoryChanges extends UnitTestCase
{

    public function testIt()
    {
    	$enwiki_host = Config::get(CleanupWorklistBot::ENWIKI_HOST);
    	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p;charset=utf8", $user, $pass);
   		$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   		$dbh_enwiki->exec('DROP TABLE IF EXISTS recentchanges');
   		$dbh_enwiki->exec('DROP TABLE IF EXISTS page');
   		$dbh_enwiki->exec('DROP TABLE IF EXISTS categorylinks');

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

    	$sql = "CREATE TABLE IF NOT EXISTS `recentchanges` (
			  rc_id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  rc_timestamp varbinary(14) NOT NULL default '',
			  rc_user int unsigned NOT NULL default 0,
			  rc_user_text varchar(255) binary NOT NULL,
			  rc_namespace int NOT NULL default 0,
			  rc_title varchar(255) binary NOT NULL default '',
			  rc_comment varchar(255) binary NOT NULL default '',
			  rc_minor tinyint unsigned NOT NULL default 0,
			  rc_bot tinyint unsigned NOT NULL default 0,
			  rc_new tinyint unsigned NOT NULL default 0,
			  rc_cur_id int unsigned NOT NULL default 0,
			  rc_this_oldid int unsigned NOT NULL default 0,
			  rc_last_oldid int unsigned NOT NULL default 0,
			  rc_type tinyint unsigned NOT NULL default 0,
			  rc_source varchar(16) binary not null default '',
			  rc_patrolled tinyint unsigned NOT NULL default 0,
			  rc_old_len int,
			  rc_new_len int,
    		  rc_params blob NULL
   			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	$dbh_enwiki->exec("INSERT INTO page VALUES (1,1,'Test_page')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (2,1,'New_page')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (3,11,'Test_template')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (4,15,'Other_category')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (5,0,'Test_page')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (6,10,'Test_template')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (7,14,'Other_category')");

    	$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (1,'Test_category','page')");
    	$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (2,'Test_category','page')");
   		$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (3,'Test_category','page')");
   		$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (4,'Test_category','page')");

    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (1,'20160909142000',1,'Test user',0,'Test_page','Test comment 1'," .
    		"0,0,0,5,2000,1000,0,'mw.edit',1,50,50,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (2,'20160910143000',2,'Test bot',0,'Test_page','Bot comment'," .
    		"0,1,0,5,2001,1001,0,'mw.edit',1,50,55,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (3,'20160910144000',0,'127.0.0.1',0,'Test_page','IP comment'," .
    		"0,0,0,5,2002,1002,0,'mw.edit',1,600,5,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (4,'20160910145000',1,'Test user',0,'Test_page','Undid revision 2002 by [[Special:Contributions/127.0.0.1|127.0.0.1]] ([[User_talk:127.0.0.1|talk]])'," .
    		"0,0,0,5,2003,1003,0,'mw.edit',1,5,600,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (5,'20160910155000',1,'Test user',10,'Test_template','/* top */USA is deprecated, per [[MOS:NOTUSA]]'," .
    		"0,0,0,6,2004,1004,0,'mw.edit',1,600,550,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (6,'20160910165000',1,'Test user',0,'New_page','New comment'," .
    		"0,0,1,2,2005,0,1,'mw.new',1,0,700,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (7,'20160910172000',0,'0000:0000:0000:0000:0000:0000:0000:0001',0,'Test_page','/* wbcreateclaim-create:1| */ [[Property:P18]]'," .
    		"0,0,0,5,2006,1005,5,'wb',1,50,50,'a:1:{s:20:\"wikibase-repo-change\";a:1:{s:9:\"object_id\";s:8:\"q5658313\";}}')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (8,'20160910182000',0,'Test user',0,'Test_page','/* wbeditclaim-edit:1| */ [[Property:P18]]'," .
    		"0,0,0,5,2007,1006,5,'wb',1,50,50,'a:1:{s:20:\"wikibase-repo-change\";a:1:{s:9:\"object_id\";s:8:\"q5658313\";}}')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (9,'20160910192000',1,'Test user',14,'Other_category','[[:Test page]] added to category'," .
    		"0,0,0,7,2008,1007,6,'mw.categorize',1,50,50,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (10,'20160910202000',1,'Test user',1,'Test_page','Talk comment 1'," .
    		"0,0,0,1,2009,1008,0,'mw.edit',1,50,50,'')");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (11,'20160910215000',1,'Test user',11,'Test_template','Talk comment 2'," .
    		"0,0,0,3,2010,1009,0,'mw.edit',1,600,550,'')");
    }
}