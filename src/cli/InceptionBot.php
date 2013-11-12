<?php
/**
 Copyright 2013 Myers Enterprises II

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

use com_brucemyers\InceptionBot\MasterRuleConfig;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\InceptionBot\InceptionBot;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\MediaWiki\WikiResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\FileCache;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'InceptionBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activerules = array(
        'Architecture' => 'Portal:Architecture/New article announcements',
        'Astro' => '',
        'Biomes' => '',
        'Bivalves' => '',
        'Cheshire' => '',
        'Cuisine' => '',
        'Cycling' => 'Wikipedia:WikiProject_Cycling/New_articles',
        'FoodDrink' => '',
        'Forestry' => 'Wikipedia:WikiProject Forestry',
        'Gastropods' => '',
        'HipHop' => '',
        'Japan' => '',
        'Michigan' => '',
        'NZ' => '',
        'Opera' => 'Wikipedia:WikiProject Opera/New article bot',
        'Oregon' => '',
        'Philately' => 'Wikipedia:WikiProject Philately/New articles',
        'Poland' => 'Portal:Poland/New article announcements',
        'Sweden' => "Wikipedia:Swedish Wikipedians' notice board/New articles"
    );

try {
    $ruletype = Config::get(InceptionBot::RULETYPE);
    $outputtype = Config::get(InceptionBot::OUTPUTTYPE);

    $timer = new Timer();
    $timer->start();

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);

    if ($ruletype == 'active') $rules = $activerules;
    elseif ($ruletype == 'custom') $rules = array(Config::get(InceptionBot::CUSTOMRULE) => '');
    else {
        $data = $wiki->getpage('User:AlexNewArtBot/Master');

        $masterconfig = new MasterRuleConfig($data);

        // Prioritize the active rules first
        $rules = array_merge($activerules, $masterconfig->ruleConfig);
    }

    $historydays = Config::get(InceptionBot::HISTORYDAYS);
    $earliestTimestamp = gmdate('Ymd', strtotime("-$historydays days")) . '000000';
    $lastrun = Config::get(InceptionBot::LASTRUN);
    $newlastrun = gmdate('YmdHis');

    if ($outputtype == 'wiki') $resultwriter = new WikiResultWriter($wiki);
    else {
        $outputDir = Config::get(InceptionBot::OUTPUTDIR);
        $outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
        $outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
        $outputDir .= DIRECTORY_SEPARATOR;
        $resultwriter = new FileResultWriter($outputDir);
    }

    $bot = new InceptionBot($wiki, $rules, $earliestTimestamp, $lastrun, $resultwriter);

    Config::set(InceptionBot::LASTRUN, $newlastrun);

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed Time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    Logger::log($ex->getMessage() . "\n" . $ex->getTraceAsString());
}
