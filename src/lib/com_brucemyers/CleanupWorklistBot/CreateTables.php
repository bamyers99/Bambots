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

namespace com_brucemyers\CleanupWorklistBot;

use PDO;

class CreateTables
{
	static $CLASSES = array('FA', 'A', 'GA', 'Bplus', 'B', 'C', 'Start', 'Stub', 'FL', 'List', 'Unassessed', 'NA', 'Book',
		'Category', 'Current', 'Disambig', 'File', 'Future', 'Merge', 'Needed', 'Portal', 'Project', 'Redirect', 'Template');
	static $IMPORTANCES = array('Top', 'High', 'Mid', 'Low', 'Unknown', 'NA', 'Bottom', 'No');

	/**
	 * Create work tables
	 *
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_tools)
    {
    	$sql = "CREATE TABLE IF NOT EXISTS `categorylinks` (
		  `cl_from` int(10) unsigned NOT NULL,
		  `cat_id` int(10) unsigned NOT NULL,
		  UNIQUE KEY `cl_from` (`cl_from`,`cat_id`),
    	  KEY `cat_id` (`cat_id`, `cl_from`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `category` (
		  `cat_id` int(10) unsigned NOT NULL,
		  `cat_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `month` tinyint(2) unsigned NULL DEFAULT NULL,
          `year` smallint(4) unsigned NULL DEFAULT NULL,
    	  PRIMARY KEY (`cat_id`),
		  UNIQUE KEY `cat_title` (`cat_title`,`month`,`year`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

		$classes_string = "'" . implode("', '", self::$CLASSES) . "'";
		$importances_string = "'" . implode("', '", self::$IMPORTANCES) . "'";

    	$sql = "CREATE TABLE IF NOT EXISTS `page` (
		  `article_id` int(10) unsigned NOT NULL,
		  `talk_id` int(10) unsigned NULL,
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `importance` ENUM($importances_string),
          `class` ENUM($classes_string),
    	  PRIMARY KEY (`article_id`),
    	  UNIQUE KEY `talk_id` (`talk_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `history` (
    	  `project` varchar(255) NOT NULL,
    	  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `total_articles` int(8) unsigned NOT NULL,
		  `cleanup_articles` int(8) unsigned NOT NULL,
		  `issues` int(8) unsigned NOT NULL,
    	  `runtime` varchar(5) NOT NULL,
    	  KEY `project` (`project`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);
    }
}