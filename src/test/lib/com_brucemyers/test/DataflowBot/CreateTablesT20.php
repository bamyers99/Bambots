<?php
/**
 Copyright 2015 Myers Enterprises II

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

namespace com_brucemyers\test\DataflowBot;

use PDO;

/**
 * Top 20 enwiki articles by edits & editors in past 7 days
 */
class CreateTablesT20
{
	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_enwiki)
    {
    	// enwiki
   		$dbh_enwiki->exec('DROP TABLE recentchanges');

    	$sql = "CREATE TABLE IF NOT EXISTS `recentchanges` (
		  `rc_user_text` varchar(255) binary NOT NULL,
		  `rc_title` varchar(255) binary NOT NULL default '',
		  `rc_namespace` int NOT NULL default 0,
    	  `rc_timestamp` varbinary(14) NOT NULL default ''
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_enwiki->exec($sql);

    	// load enwiki

   		$dbh_enwiki->exec('TRUNCATE recentchanges');

   		$today = date('YmdHis');

   		$dbh_enwiki->exec("INSERT INTO recentchanges VALUES ('Fred', 'Apple', 0, '$today')");
   		$dbh_enwiki->exec("INSERT INTO recentchanges VALUES ('Mary', 'Apple', 0, '$today')");
        $dbh_enwiki->exec("INSERT INTO recentchanges VALUES ('Mary', 'Fruit', 0, '$today')");
       	$dbh_enwiki->exec("INSERT INTO recentchanges VALUES ('Bill', 'Cow', 0, '$today')");
    }
}