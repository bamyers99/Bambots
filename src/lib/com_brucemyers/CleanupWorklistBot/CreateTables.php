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
	static $CLASSES = array('FA' => '00', 'FL' => '01', 'A' => '02', 'GA' => '03', 'AL' => '04', 'Bplus' => '05', 'B' => '06', 'BL' => '07', 'C' => '08', 'CL' => '09',
		'Start' => '10', 'Stub' => '11', 'List' => '12', 'Unassessed' => '13', 'NA' => '14', 'Book' => '15',
		'Category' => '16', 'Current' => '17', 'Disambig' => '18', 'File' => '19', 'Future' => '20', 'Merge' => '21',
	    'Needed' => '22', 'Portal' => '23', 'Project' => '24', 'Redirect' => '25', 'SIA' => '26', 'Template' => '27', '' => '28');
	static $IMPORTANCES = array('Top' => '0', 'High' => '1', 'Mid' => '2', 'Low' => '3', 'Unknown' => '4', 'NA' => '5',
		'Bottom' => '6', 'No' => '7', '' => '8');

	/**
	 * Create work tables
	 *
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_tools)
    {
    	$sql = "CREATE TABLE IF NOT EXISTS `categorylinks` (
		  `cl_from` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
		  `cat_id` int(10) unsigned NOT NULL,
		  UNIQUE KEY `cl_from` (`cl_from`,`cat_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `category` (
		  `cat_id` int(10) unsigned NOT NULL,
		  `cat_title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    	  `month` tinyint(2) unsigned NULL DEFAULT NULL,
          `year` smallint(4) unsigned NULL DEFAULT NULL,
    	  PRIMARY KEY (`cat_id`),
		  UNIQUE KEY `cat_title` (`cat_title`,`month`,`year`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

		$classes_string = "'" . implode("', '", array_keys(self::$CLASSES)) . "'";
		$importances_string = "'" . implode("', '", array_keys(self::$IMPORTANCES)) . "'";

    	$sql = "CREATE TABLE IF NOT EXISTS `page` (
		  `page_title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
          `importance` ENUM($importances_string),
          `class` ENUM($classes_string),
    	  PRIMARY KEY (`page_title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `history` (
    	  `project` varchar(255) NOT NULL,
    	  `time` date NOT NULL,
		  `total_articles` int(8) unsigned NOT NULL,
		  `cleanup_articles` int(8) unsigned NOT NULL,
		  `issues` int(8) unsigned NOT NULL,
  		  `added_articles` int(8) unsigned NOT NULL DEFAULT '0',
  		  `removed_articles` int(8) unsigned NOT NULL DEFAULT '0',
    	  KEY `project` (`project`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `project` (
    	  `name` varchar(255) NOT NULL,
    	  `wiki_too_big` int(8) NOT NULL,
          `member_cat_type` int(8) NOT NULL DEFAULT '0',
    	  PRIMARY KEY `name` (`name`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `livingpeople` (
		  `page_title` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
		  UNIQUE KEY `page_title` (`page_title`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);
    }
}