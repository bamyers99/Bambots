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

namespace com_brucemyers\CategoryWatchlistBot;

use PDO;

class CreateTables
{
	/**
	 * Create work tables
	 *
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_tools)
    {
    	$sql = "CREATE TABLE IF NOT EXISTS `querys` (
		  `id` int unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
		  `wikiname` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `hash` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `params` varchar(2048) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `lastaccess` date NOT NULL,
    	  `lastrecalc` date NOT NULL,
 		  UNIQUE KEY `hash` (`hash`),
    	  KEY `wikiname` (`wikiname`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `querycats` (
		  `queryid` int unsigned NOT NULL,
		  `category` varchar(255) binary NOT NULL,
		  `plusminus` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  UNIQUE KEY `queryid_category` (`queryid`, `category`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `wikis` (
		  `wikiname` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL PRIMARY KEY,
		  `wikititle` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `wikidomain` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `runs` (
		  `wikiname` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `rundate` timestamp,
    	  `rev_id` int unsigned,
		  UNIQUE KEY `wikiname_rundate` (`wikiname`, `rundate`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);
    }
}