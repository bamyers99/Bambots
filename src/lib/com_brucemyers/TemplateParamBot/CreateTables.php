<?php
/**
 Copyright 2016 Myers Enterprises II

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

namespace com_brucemyers\TemplateParamBot;

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
    	$sql = "CREATE TABLE IF NOT EXISTS `loads` (
		  `wikiname` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `template_id` int unsigned NOT NULL,
    	  `status` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `progress` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `lastrun` datetime NOT NULL,
    	  `runtime` time NOT NULL,
    	  UNIQUE `wikiname_tmplid` (`wikiname`,`template_id`)
    	  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `wikis` (
		  `wikiname` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL PRIMARY KEY,
		  `wikititle` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `wikidomain` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `templateNS` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `lang` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `lastdumpdate` char(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	  `revision_id` int unsigned NOT NULL,
   	  	  `templatecnt` int unsigned NOT NULL,
   	  	  `templateinstancecnt` int unsigned NOT NULL
		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);
    }
}