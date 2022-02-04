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

namespace com_brucemyers\test\CleanupWorklistBot;

use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;
use com_brucemyers\CleanupWorklistBot\CreateTables;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Timer;
use UnitTestCase;
use PDO;

class InsertTest extends UnitTestCase
{
	static $MAX_RECORDS = 10000;
	var $dbh_tools;

    public function testSQLInsert()
    {
    	$tools_host = Config::get(CleanupWorklistBot::TOOLS_HOST);
    	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);

    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$this->dbh_tools = $dbh_tools;

		$classes_string = "'" . implode("', '", array_keys(CreateTables::$CLASSES)) . "'";
		$importances_string = "'" . implode("', '", array_keys(CreateTables::$IMPORTANCES)) . "'";

    	$timer = new Timer();

    	for ($x=0; $x < 4; ++$x) {
    		$dbh_tools->exec('DROP TABLE page');
	    	$sql = "CREATE TABLE `page` (
	    		`article_id` int(10) unsigned NOT NULL,
	    		`talk_id` int(10) unsigned NULL,
	    		`page_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
	    		`importance` ENUM($importances_string),
	    		`class` ENUM($classes_string),
	    		PRIMARY KEY (`article_id`),
	    		UNIQUE KEY `talk_id` (`talk_id`)
	    		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	    	$dbh_tools->exec($sql);

	    	$timer->start();

    		switch ($x) {
    		    case 0:
    		    	$test = 'Keep index, no trans';
    		    	$this->performInserts(false, false);
    		    	break;

    		    case 1:
    		    	$test = 'Keep index, use trans';
   		    		$this->performInserts(false, true);
    		    	break;

    		    case 2:
    		    	$test = 'Drop index, no trans';
   		    		$this->performInserts(true, false);
    		    	break;

    		    case 3:
    		    	$test = 'Drop index, use trans';
   		    		$this->performInserts(true, true);
    		    	break;
    		}

    		$ts = $timer->stop();

    		echo sprintf("$test time: %02d:%02d:%02d\n", $ts['hours'], $ts['minutes'], $ts['seconds']);
    	}

    }

    /**
     * Insert records
     *
     * @param bool $drop_indices
     * @param bool $use_trans
     */
    function performInserts($drop_indices, $use_trans)
    {
    	if ($drop_indices) {
    		$this->dbh_tools->exec('ALTER TABLE `page` DROP PRIMARY KEY, DROP INDEX `talk_id`');
    	}

    	$isth = $this->dbh_tools->prepare('INSERT INTO page (article_id, talk_id, page_title) VALUES (:artid, :talkid, :title)');

    	if ($use_trans) $this->dbh_tools->beginTransaction();

    	for ($x=1; $x <= self::$MAX_RECORDS; ++$x) {
    		if ($use_trans && ($x % 1000 == 0)) {
    			$this->dbh_tools->commit();
    			$this->dbh_tools->beginTransaction();
    		}
    		$isth->execute(array('artid' => $x, 'talkid' => $x, 'title' => "$x"));
    	}

    	if ($use_trans) $this->dbh_tools->commit();

    	if ($drop_indices) {
    	    $this->dbh_tools->exec('ALTER TABLE `page` ADD PRIMARY KEY (`article_id`), ADD UNIQUE KEY `talk_id` (`talk_id`)');
    	}
    }
}