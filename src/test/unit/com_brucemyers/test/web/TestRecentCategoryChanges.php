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
			  rc_new_len int
   			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	$dbh_enwiki->exec("INSERT INTO page VALUES (1,1,'Test_page')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (2,1,'New_page')");
    	$dbh_enwiki->exec("INSERT INTO page VALUES (3,11,'Test_template')");
    	$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (1,'Test_category','page')");
    	$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (2,'Test_category','page')");
    	$dbh_enwiki->exec("INSERT INTO categorylinks VALUES (3,'Test_category','page')");

    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (1,'20160909142000',1,'Test user',0,'Test_page','Test comment 1'," .
    		"0,0,0,57,2000,1000,0,'mw.edit',1,50,50)");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (2,'20160910143000',2,'Test bot',0,'Test_page','Bot comment'," .
    		"0,1,0,58,2001,1001,0,'mw.edit',1,50,55)");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (3,'20160910144000',0,'127.0.0.1',0,'Test_page','IP comment'," .
    		"0,0,0,59,2002,1002,0,'mw.edit',1,600,5)");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (4,'20160910145000',1,'Test user',0,'Test_page','Undid revision 738620213 by [[Special:Contributions/166.137.99.31|166.137.99.31]] ([[User_talk:166.137.99.31|talk]])'," .
    		"0,0,0,60,2003,1003,0,'mw.edit',1,5,600)");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (5,'20160910155000',1,'Test user',10,'Test_template','/* top */USA is deprecated, per [[MOS:NOTUSA]]'," .
    		"0,0,0,61,2004,1004,0,'mw.edit',1,600,550)");
    	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES (6,'20160910165000',1,'Test user',0,'New_page','New comment'," .
    		"0,0,1,62,2005,0,1,'mw.new',1,0,700)");
    }
}