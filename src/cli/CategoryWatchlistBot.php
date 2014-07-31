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

use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\CategoryWatchlistBot\CategoryWatchlistBot;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'CategoryWatchlistBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activerules = array(
    	'commons' => 'Wikipedia Commons',
    	'enwiki' => 'English Wikipedia'
        );

try {

	if ($argc > 1) {
		$action = $argv[1];
		switch ($action) {

		    default:
		    	echo 'Unknown action = ' . $action;
				exit;
		    	break;
		}
	}

    $ruletype = Config::get(CategoryWatchlistBot::RULETYPE);

    $timer = new Timer();
    $timer->start();

    if ($ruletype == 'active') $rules = $activerules;
    elseif ($ruletype == 'custom') $rules = array(Config::get(CategoryWatchlistBot::CUSTOMRULE) => Config::get(CategoryWatchlistBot::CUSTOMRULE));
    else {
    	echo 'Unknown ruletype = ' . $ruletype;
    	exit;
    }

    $bot = new CategoryWatchlistBot($rules);

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
    Logger::log($msg);
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'From: WMF Labs <admin@brucemyers.com>' . "\r\n";
    mail(Config::get(CategoryWatchlistBot::ERROREMAIL), 'CleanupWorklistBot failed', $msg, $headers);
}

/**
 * Retrieve CSV files from toolserver.
 */
function retrieveCSV($wiki)
{
    $data = $wiki->getpage('User:CleanupWorklistBot/Master');
    $masterconfig = new MasterRuleConfig($data);
    $outputdir = Config::get(CleanupWorklistBot::HTMLDIR);
    $ch = curl_init();

    foreach ($masterconfig->ruleConfig as $project => $category) {
    	if (strpos($project, 'WikiProject_') === 0) {
    		$project = substr($project, 12);
    	}
    	$filesafe_project = str_replace('/', '_', $project);

    	$csvurl = 'http://toolserver.org/~svick/CleanupListing/CleanupListing.php?project=' . urlencode($project) . '&format=csv';
		$bakcsvpath = $outputdir . 'csv' . DIRECTORY_SEPARATOR . $filesafe_project . '.csv.bak';

		if (file_exists($bakcsvpath)) continue;

		$fp = fopen($bakcsvpath, 'w');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_URL, $csvurl);

		$stat = curl_exec($ch);
		if ($stat !== true) echo "curl_exec failed: $project | " . curl_error($ch);

		fclose($fp);

		clearstatcache();
		$filesize = filesize($bakcsvpath);
		if ($filesize < 66) {
			echo "No data for: $project\n";
    		unlink($bakcsvpath);
		};
    }

	curl_close($ch);
}

/**
 * Retrieve history from toolserver.
 */
function retrieveHistory($wiki)
{
    $data = $wiki->getpage('User:CleanupWorklistBot/Master');
    $masterconfig = new MasterRuleConfig($data);
    $ch = curl_init();

    $tools_host = Config::get(CleanupWorklistBot::TOOLS_HOST);
    $user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    $pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);

    $dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot", $user, $pass);
    $dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $isth = $dbh_tools->prepare('INSERT INTO history VALUES (?,?,?,?,?)');

    foreach ($masterconfig->ruleConfig as $project => $category) {
    	if (strpos($project, 'WikiProject_') === 0) {
    		$project = substr($project, 12);
    	}

    	$histurl = 'http://toolserver.org/~svick/CleanupListing/CleanupListingHistory.php?project=' . urlencode($project);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, $histurl);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$page = curl_exec($ch);

		if ($page === false) {
			echo "curl_exec failed: $project | " . curl_error($ch);
			continue;
		}

		if (strpos($page, 'Timestamp') === false) {
			echo "No data for: $project\n";
			continue;
		}

		preg_match_all('`<tr>\s*<td>(\d{4}-\d{2}-\d{2})\s\d{2}:\d{2}:\d{2}</td>\s*<td>(\d+)</td>\s*<td>(\d+)</td>\s*<td>(\d+)</td>\s*</tr>`i', $page, $rows, PREG_SET_ORDER);

		foreach ($rows as $row) {
			$date = $row[1];
			$totart = $row[2];
			$cuart = $row[3];
			$istot = $row[4];

			$isth->execute(array($project, $date, $totart, $cuart, $istot));
		}
    }

	curl_close($ch);
}

/**
 * Check WikiProject class category.
 */
function checkWPCategory($wiki)
{
    $enwiki_host = Config::get(CleanupWorklistBot::ENWIKI_HOST);
    $user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    $pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = $wiki->getpage('User:CleanupWorklistBot/Master');
    $masterconfig = new MasterRuleConfig($data);

    foreach ($masterconfig->ruleConfig as $wikiproject => $category) {
    	$project = $wikiproject;
    	if (strpos($wikiproject, 'WikiProject_') === 0) {
    		$project = substr($project, 12);
    	}

        if (empty($category)) $category = $project;

        $total_count = 0;

    	foreach(array_keys(CreateTables::$CLASSES) as $class) {
	        if ($class == 'Unassessed')
  		        $theclass = "{$class}_{$category}_articles";
       		else
          		$theclass = "{$class}-Class_{$category}_articles";

	    	$sth = $dbh_enwiki->prepare("SELECT count(*) as `count` FROM categorylinks WHERE cl_to = ? AND cl_type = 'page'");
	    	$sth->bindValue(1, $theclass);
	    	$sth->execute();

	    	$row = $sth->fetch(PDO::FETCH_ASSOC);

	    	$total_count += (int)$row['count'];
    	}

    	if (! $total_count) echo "WPCategory not found = $wikiproject ($category)\n";
    }
}

    /**
     * Find toolserver cleanup listing links
     */
    function toolserverLinks($wiki)
    {
    	$outputDir = Config::get(CleanupWorklistBot::OUTPUTDIR);
    	$outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
    	$outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
    	$outputDir .= DIRECTORY_SEPARATOR;
    	$resultwriter = new FileResultWriter($outputDir);

	    $enwiki_host = Config::get(CleanupWorklistBot::ENWIKI_HOST);
		$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
		$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
		$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
		$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$output = '';
		$projects = array();

	    $results = $dbh_enwiki->query("SELECT DISTINCT el_to, page_namespace, page_title FROM externallinks
	    		LEFT JOIN page ON page_id = el_from
	    		WHERE el_to LIKE 'http://toolserver.org/~svick/CleanupListing%'
	    			AND page_namespace = 10 -- Template
	    			-- AND page_namespace IN (2,4,100,10) -- User, Wikipedia, Portal, Template
	    		ORDER BY el_to, page_namespace, page_title");

    	while($row = $results->fetch(PDO::FETCH_ASSOC)) {
    		if (strpos($row['el_to'], '=')) list($url, $project) = explode('=', $row['el_to'], 2);
    		else $project = '?';

    		if (strpos($project, '&')) list($project, $params) = explode('&', $project, 2);
    		$wikilink = $wiki->namespaces[(int)$row['page_namespace']] . ':' . $row['page_title'];

    		if (! isset($projects[$project])) $projects[$project] = array();
    		$projects[$project][] = $wikilink;
    	}

    	ksort($projects);

    	foreach ($projects as $project => $wikilinks) {
    		foreach ($wikilinks as $wikilink) $output .= "*$project - [[$wikilink]]\n";
    	}

		$resultwriter->writeResults("User:CleanupWorklistBot/ToolserverLinks", $output, "");
    }

