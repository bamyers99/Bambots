<?php
/**
 Copyright 2025 Myers Enterprises II

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

use com_brucemyers\DumpScannerBot\DumpScannerBot;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\Email;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'DumpScannerBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activescanners =[
        'WDAmbiguousLabels',
    ];

try {
    if ($argc < 2 || ! in_array($argv[1], $activescanners)) {
		echo "Usage: DumpScannerBot.php <scannername>\n";
		echo "Available scanners:\n";
		foreach ($activescanners as $scannername) {
	    	$classname = "com_brucemyers\\DumpScannerBot\\Scanners\\$scannername";
	    	$scanner = new $classname();
	    	$usage = $scanner->getUsage();
			echo "\t$scannername$usage\n";
		}
		exit;
	}

	$scannername = $argv[1];

    $timer = new Timer();
    $timer->start();
    $bot = new DumpScannerBot();

    $params = array_slice($argv, 2);

    $bot->commenceScan($scannername, $params);

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
    Logger::log($msg);
    $email = new Email();
    $retval = $email->sendEmail('admin@brucemyers.com', Config::get(DumpScannerBot::ERROREMAIL), 'DumpScannerBot failed', $msg);
    if (! $retval) throw new Exception($ex->getMessage());
}
