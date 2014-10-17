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

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\MediaWiki\WikiResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\FileCache;
use com_brucemyers\RenderedWiki\RenderedWiki;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'DatabaseReportBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

DEFINE('ENWIKI_HOST', 'DatabaseReportBot.enwiki_host');
DEFINE('TOOLS_HOST', 'DatabaseReportBot.tools_host');

    $activereports = array(
        'BrokenSectionAnchors',
    	'DiacriticRedLinks',
    	'MisspelledRedLinks',
    	'SimilarRedLinks',
    	'StubTypeSizes'
    );

try {
	if ($argc < 2 || ! in_array($argv[1], $activereports)) {
		echo "Usage: DatabaseReportBot.php <reportname>\n";
		echo "Available reports:\n";
		foreach ($activereports as $reportname) {
	    	$classname = "com_brucemyers\\DatabaseReportBot\\Reports\\$reportname";
	    	$report = new $classname();
	    	$usage = $report->getUsage();
			echo "\t$reportname$usage\n";
		}
		exit;
	}

	$reportname = $argv[1];

    $outputtype = Config::get(DatabaseReportBot::OUTPUTTYPE);

    $timer = new Timer();
    $timer->start();

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);
    FileCache::purgeExpired();
    $url = Config::get(RenderedWiki::WIKIRENDERURLKEY);
    $renderedwiki = new RenderedWiki($url);

    if ($outputtype == 'wiki') $resultwriter = new WikiResultWriter($wiki);
    else {
        $outputDir = Config::get(DatabaseReportBot::OUTPUTDIR);
        $outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
        $outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
        $outputDir .= DIRECTORY_SEPARATOR;
        $resultwriter = new FileResultWriter($outputDir);
    }

    $enwiki_host = Config::get(ENWIKI_HOST);
    $tools_host = Config::get(TOOLS_HOST);
    $bot = new DatabaseReportBot($resultwriter, $wiki, $renderedwiki, $enwiki_host, 'enwiki_p', $tools_host);

    $params = array_slice($argv, 2);

    $bot->generateReport($reportname, 'Wikipedia:Database reports', $params);

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
    Logger::log($msg);
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'From: DatabaseReportBot <admin@brucemyers.com>' . "\r\n";
    mail(Config::get(DatabaseReportBot::ERROREMAIL), 'DatabaseReportBot failed', $msg, $headers);
}
