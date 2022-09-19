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

use com_brucemyers\WikidataBot\WikidataBot;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\FileCache;


$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'WikidataBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

try {
	$timer = new Timer();
    $timer->start();
    Logger::log('Started');
    FileCache::purgeExpired();

    if ($argc < 2) {
    	echo "Usage: WikidataBot.php <taskid> <params...>\n";
    	echo "\ttaskid 1 - Import TSV, params: <TSV file path (QID TAB property value)> <property ID>\n";
    	exit;
    }

    $taskid = $argv[1];

    $bot = new WikidataBot();
    $params = array_slice($argv, 2);
    
    $bot->run($taskid, $params);

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed Time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
    Logger::log($msg);
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'From: WMF Labs <admin@brucemyers.com>' . "\r\n";
    mail(Config::get(WikidataBot::ERROREMAIL), 'WikidataBot failed', $msg, $headers);
}
