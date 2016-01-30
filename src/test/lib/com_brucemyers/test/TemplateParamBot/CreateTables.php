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

namespace com_brucemyers\test\TemplateParamBot;

use PDO;

class CreateTables
{
	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_enwiki, PDO $dbh_tools)
    {
    	// enwiki
   		$dbh_enwiki->exec('DROP TABLE IF EXISTS redirect');
   		$dbh_enwiki->exec('DROP TABLE IF EXISTS page');
   		$dbh_enwiki->exec('DROP TABLE IF EXISTS page_props');

    	$sql = "CREATE TABLE IF NOT EXISTS `page` (
		  `page_id` int(10) unsigned NOT NULL,
		  `page_namespace` int(11) NOT NULL,
		  `page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  		  `page_is_redirect` tinyint unsigned NOT NULL default 0,
    	  PRIMARY KEY (`page_id`),
		  UNIQUE KEY `name_title` (`page_namespace`,`page_title`)
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `redirect` (
 			`rd_from` int unsigned NOT NULL default 0 PRIMARY KEY,
 			`rd_namespace` int NOT NULL default 0,
 			`rd_title` varchar(255) binary NOT NULL default '',
 			`rd_interwiki` varchar(32) default NULL,
 			`rd_fragment` varchar(255) binary default NULL
 			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_enwiki->exec($sql);

		$sql = "CREATE TABLE `page_props` (
  			`pp_page` int NOT NULL,
  			`pp_propname` varchar(60) NOT NULL,
  			`pp_value` blob NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_enwiki->exec($sql);

    	// tools
    	new \com_brucemyers\TemplateParamBot\CreateTables($dbh_tools);

    	// load enwiki

   		$dbh_enwiki->exec("INSERT INTO page VALUES (1, 10, 'Infobox_Person', 1)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (2, 10, 'Infobox_person', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (3, 10, 'Birth_date', 0)");

   		$dbh_enwiki->exec("INSERT INTO page VALUES (101, 0, 'Person_101', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (102, 0, 'Person_102', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (103, 0, 'Person_103', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (104, 0, 'Person_104', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (105, 0, 'Person_105', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (106, 0, 'Person_106', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (107, 0, 'Person_107', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (108, 0, 'Person_108', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (109, 0, 'Person_109', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (110, 0, 'Person_110', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (111, 0, 'Person_111', 0)");
   		$dbh_enwiki->exec("INSERT INTO page VALUES (112, 0, 'Person_112', 0)");

   		$dbh_enwiki->exec("INSERT INTO redirect VALUES (1, 10, 'Infobox_person' , '', '')");

   		$templatedata = <<<EOT
{
        "description": "Person infobox",
        "params": {
                "title": {
                        "label": "Title",
                        "description": "Persons name",
                        "type": "string",
                        "required": true
                },
                "birth_date": {
                        "label": "Birth date",
                        "description": "The date the person was born",
                        "type": "string",
                        "required": false
                },
                "honorific": {
                        "label": "Honorific",
                        "description": "The honorific, ie. Mr, Dr, etc",
                        "type": "string",
                        "required": false
                }
        }
}
EOT;

   		$sth = $dbh_enwiki->prepare("INSERT INTO page_props VALUES (2, 'templatedata', ?)");
   		$sth->bindValue(1, $templatedata);
   		$sth->execute();

   		$templatedata = <<<EOT
{
        "description": "This template returns a living person's date of birth and current age.",
        "params": {
                "1": {
                        "label": "Year of birth",
                        "description": "The year in which the person was born",
                        "type": "number",
                        "required": true
                },
                "2": {
                        "label": "Month of birth",
                        "description": "The month (number) in which the person was born",
                        "type": "number",
                        "required": true
                },
                "3": {
                        "label": "Day of birth",
                        "description": "The day (number) in which the person was born",
                        "type": "number",
                        "required": true
                },
                "df": {
                        "label": "Day first",
                        "description": "When set to 'y' or 'yes', the date of birth is output in a DMY format",
                        "type": "string",
                        "required": false
                },
                "mf": {
                        "label": "Month first",
                        "description": "When set to 'y' or 'yes', stresses that the default MDY date format is intended for the date of birth",
                        "type": "string",
                        "required": false
                }
        }
}
EOT;

   		$sth = $dbh_enwiki->prepare("INSERT INTO page_props VALUES (3, 'templatedata', ?)");
   		$sth->bindValue(1, $templatedata);
   		$sth->execute();

    }
}