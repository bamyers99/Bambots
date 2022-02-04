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

use com_brucemyers\CleanupWorklistBot\MasterRuleConfig;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;
use com_brucemyers\CleanupWorklistBot\CreateTables;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\MediaWiki\WikiResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\FileCache;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'CleanupWorklistBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activerules = array(
    	"SGpedians'_notice_board" => 'Singapore',
    	'WikiProject_Beer/Pub_Taskforce' => 'Pubs',
    	'WikiProject_Biophysics' => '',
        'WikiProject_Michigan' => '',
    	'WikiProject_Physics' => 'physics',
    	'WikiProject_Protected_areas' => ''
        );

try {
    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $skipCatLoad = false;

	if ($argc > 1) {
		$action = $argv[1];
		switch ($action) {
		    case 'checkWPCategory':
		        checkWPCategory($wiki);
		        exit;
		        break;

		    case 'calcMemberCatType':
		        calcMemberCatType($wiki);
		        exit;
		        break;

		    case 'dumpLivingPeople':
		        dumpLivingPeople();
		        exit;
		        break;

		    case 'skipCatLoad':
		    	$skipCatLoad = true;
		    	break;

		    default:
		    	echo 'Unknown action = ' . $action;
				exit;
		    	break;
		}
	}

    $ruletype = Config::get(CleanupWorklistBot::RULETYPE);
    $outputtype = Config::get(CleanupWorklistBot::OUTPUTTYPE);

    $timer = new Timer();
    $timer->start();

    if ($ruletype == 'active') $rules = $activerules;
    elseif ($ruletype == 'custom') {
        $parts = explode('=>', Config::get(CleanupWorklistBot::CUSTOMRULE), 2);
        $key = str_replace(' ', '_', trim($parts[0]));
        $value = (count($parts) > 1) ? str_replace(' ', '_', trim($parts[1])) : '';
    	$rules = [$key => $value];
    }
    else {
        $data = $wiki->getpage('User:CleanupWorklistBot/Master');
        $masterconfig = new MasterRuleConfig($data);
        $rules = $masterconfig->ruleConfig;
    }

    if ($outputtype == 'wiki') $resultwriter = new WikiResultWriter($wiki);
    else {
        $outputDir = Config::get(CleanupWorklistBot::OUTPUTDIR);
        $outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
        $outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
        $outputDir .= DIRECTORY_SEPARATOR;
        $resultwriter = new FileResultWriter($outputDir);
    }

    $bot = new CleanupWorklistBot($rules, $resultwriter, $skipCatLoad);

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
    Logger::log($msg);
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'From: WMF Labs <admin@brucemyers.com>' . "\r\n";
    mail(Config::get(CleanupWorklistBot::ERROREMAIL), 'CleanupWorklistBot failed', $msg, $headers);
}

/**
 * Check WikiProject class category.
 */
function checkWPCategory($wiki)
{
    $enwiki_host = Config::get(CleanupWorklistBot::ENWIKI_HOST);
    $user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    $pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p;charset=utf8mb4", $user, $pass);
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
 * Check WikiProject class category.
 */
function calcMemberCatType($wiki)
{
    $tools_host = Config::get(CleanupWorklistBot::TOOLS_HOST);
    $user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    $pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
    $dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $user, $pass);
    $dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = $wiki->getpage('User:CleanupWorklistBot/Master');
    $masterconfig = new MasterRuleConfig($data);

    $projects = [];

    foreach ($masterconfig->ruleConfig as $wikiproject => $category) {
        $project = $wikiproject;
        if (strpos($wikiproject, 'WikiProject_') === 0) {
            $project = substr($project, 12);
        }

        if (empty($category)) $category = $project;

        $projects[$wikiproject] = $category;
    }

    $results = $dbh_tools->query('SELECT `name` FROM project');

    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $project = $row['name'];

        if (isset($projects[$project])) {
            $member_cat_type = _test_category($wiki, $projects[$project]);

            $sth = $dbh_tools->prepare("UPDATE project SET member_cat_type = ? WHERE `name` = ?");
            $sth->execute([$member_cat_type, $project]);
        }
    }
}

/**
 * Tests a category.
 *
 * @param string $category
 * @return int
 */
function _test_category($wiki, $category)
{
    // category - x articles by quality (subcats)
    $ucfcategory = ucfirst($category);
    $param = "{$ucfcategory}_articles_by_quality";
    $ret = $wiki->getProp('categoryinfo', ['titles' => "Category:$param"]);

    if (! empty($ret['query']['pages'])) {
        $page = reset($ret['query']['pages']);
        if ($page['categoryinfo']['pages'] > 0) {
            return 0;
        }
    }

    // category - WikiProject x articles
    $param = "WikiProject_{$category}_articles";
    $ret = $wiki->getProp('categoryinfo', ['titles' => "Category:$param"]);

    if (! empty($ret['query']['pages'])) {
        $page = reset($ret['query']['pages']);
        if ($page['categoryinfo']['pages'] > 0) {
            return 1;
        }
    }

    // category - x (talk namespace)
    $param = $category;
    $ret = $wiki->getList('categorymembers', [
        'cmtitle' => "Category:$param",
        'cmnamespace' => 1,
        'cmtype' => 'page',
        'cmlimit' => 1
    ]);

    if (! empty($ret['query']['categorymembers'])) {
        return 2;
    }

    // category - x (article namespace)
    $param = $category;
    $ret = $wiki->getList('categorymembers', [
        'cmtitle' => "Category:$param",
        'cmnamespace' => 0,
        'cmtype' => 'page',
        'cmlimit' => 1
    ]);

    if (! empty($ret['query']['categorymembers'])) {
        return 3;
    }

    return 0;
}

/**
 * Dump page titles in category Living_people.
 */
function dumpLivingPeople()
{
    $outpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'LivingPeople.tsv';
    $hndl = fopen($outpath, 'w');

    $username = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    $password = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
    $wiki_host = Config::get('CleanupWorklistBot.enwiki_host');
    $dbh = new PDO("mysql:host=$wiki_host;dbname=enwiki_p;charset=utf8mb4", $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT page_title FROM page, categorylinks WHERE cl_from = page_id AND cl_to = 'Living_people' AND cl_type='page'";
    $stmt = $dbh->query($sql);
    $stmt->setFetchMode(PDO::FETCH_NUM);

    while (($row = $stmt->fetch()) !== false) {
        $pagename = str_replace('_', ' ', $row[0]);
        fwrite($hndl, "$pagename\n");
    }

    fclose($hndl);
}
