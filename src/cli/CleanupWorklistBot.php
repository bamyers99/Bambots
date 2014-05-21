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
        'Michigan' => '',
    	'Protected areas' => ''
        );

try {
	if ($argc > 1) {
		$action = $argv[1];
		switch ($action) {
		    case 'isWikiproject':
		    	$dir = '/home/bruce/Downloads/wikipedia/CleanupListing';
		    	$projlist = array();
		    	isWikiproject($dir, '', $projlist);
		    	sort($projlist);

		    	$outfile = '/home/bruce/Downloads/wikipedia/CleanupListing/AProjectList.txt';
		    	$hndl = fopen($outfile, 'w');

		    	foreach($projlist as $projname) {
		    		fwrite($hndl, ' ' . $projname . "\n");
		    	}

		    	fclose($hndl);
		    	break;

		    default:
		    	echo 'Unknown action = ' . $action;
		    	break;
		}
		exit;
	}


    $ruletype = Config::get(CleanupWorklistBot::RULETYPE);
    $outputtype = Config::get(CleanupWorklistBot::OUTPUTTYPE);

    $timer = new Timer();
    $timer->start();

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);

    if ($ruletype == 'active') $rules = $activerules;
    elseif ($ruletype == 'custom') $rules = array(Config::get(CleanupWorklistBot::CUSTOMRULE) => '');
    else {
        $data = $wiki->getpage('User:AlexNewArtBot/Master');

        $masterconfig = new MasterRuleConfig($data);

        // Prioritize the active rules first
        $rules = array_merge($activerules, $masterconfig->ruleConfig);
    }

    if ($outputtype == 'wiki') $resultwriter = new WikiResultWriter($wiki);
    else {
        $outputDir = Config::get(CleanupWorklistBot::OUTPUTDIR);
        $outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
        $outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
        $outputDir .= DIRECTORY_SEPARATOR;
        $resultwriter = new FileResultWriter($outputDir);
    }

    $bot = new CleanupWorklistBot($wiki, $rules, $resultwriter);

    Config::set(CleanupWorklistBot::LASTRUN, $newlastrun, true);

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
 * Determine WikiProject names.
 */
function isWikiproject($dir, $subdir, &$projlist)
{
    $handle = opendir($dir);

    while (($entry = readdir($handle)) !== false) {
    	if ($entry == '.' || $entry == '..') continue;
    	if (strpos($entry, '.csv') !== false) continue;
    	if (strpos($entry, '-history.') !== false) continue;
    	if ($entry == 'AProjectList.txt') continue;

    	$filepath = $dir . '/' . $entry;

    	if (is_dir($filepath)) {
    		isWikiproject($filepath, $entry, $projlist);
    		continue;
    	}

    	if (! empty($subdir)) $entry = $subdir . '/' . $entry;

    	if (preg_match('!^project=(.*)\\.html$!', $entry, $matches) == 0) {
    		echo "Project not found = $entry\n";
    		continue;
    	}
    	$projname = urldecode($matches[1]);

    	$data = file_get_contents($filepath);
    	if (strpos($data, 'Wikipedia:WikiProject_') === false) {
    		$projlist[] = $projname;
    	} else {
    		$projlist[] = 'WikiProject_' . $projname;
    	}
    }

   	closedir($handle);
}