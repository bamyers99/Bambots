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

use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\TemplateParamBot\TemplateParamBot;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'TemplateParamBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activerules = [
    	'commonswiki' => ['title' => 'Wikipedia Commons', 'domain' => 'commons.wikimedia.org', 'templateNS' => 'Template', 'lang' => 'en'],
        'enwiki' => ['title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'templateNS' => 'Template', 'lang' => 'en'],
        'enwiktionary' => ['title' => 'English Wiktionary', 'domain' => 'en.wiktionary.org', 'templateNS' => 'Template', 'lang' => 'en'],
        'enwikisource' => ['title' => 'English Wikisource', 'domain' => 'en.wikisource.org', 'templateNS' => 'Template', 'lang' => 'en'],
        //    	'svwiki' => array('title' => 'Svenska Wikipedia', 'domain' => 'sv.wikipedia.org'),
//        'nlwiki' => array('title' => 'Nederlands Wikipedia', 'domain' => 'nl.wikipedia.org'),
//        'dewiki' => array('title' => 'Deutsch Wikipedia', 'domain' => 'de.wikipedia.org'),
//        'frwiki' => array('title' => 'Français Wikipedia', 'domain' => 'fr.wikipedia.org'),
        'ruwiki' => ['title' => 'Russian Wikipedia', 'domain' => 'ru.wikipedia.org', 'templateNS' => 'Шаблон', 'lang' => 'ru'],
        'itwiki' => ['title' => 'Italian Wikipedia', 'domain' => 'it.wikipedia.org', 'templateNS' => 'Template', 'lang' => 'it'],
//        'eswiki' => array('title' => 'Español Wikipedia', 'domain' => 'es.wikipedia.org'),
//        'viwiki' => array('title' => 'Tiếng Việt Wikipedia', 'domain' => 'vi.wikipedia.org'),
//        'warwiki' => array('title' => 'Winaray Wikipedia', 'domain' => 'war.wikipedia.org'),
//        'cebwiki' => array('title' => 'Sinugboanong Binisaya Wikipedia', 'domain' => 'ceb.wikipedia.org'),
//        'plwiki' => array('title' => 'Polski Wikipedia', 'domain' => 'pl.wikipedia.org'),
//        'jawiki' => array('title' => '日本語 Wikipedia', 'domain' => 'ja.wikipedia.org'),
//        'ptwiki' => array('title' => 'Português Wikipedia', 'domain' => 'pt.wikipedia.org', 'lang' => 'pt')
//        'zhwiki' => array('title' => '中文 Wikipedia', 'domain' => 'zh.wikipedia.org'),
//        'ukwiki' => array('title' => 'Українська Wikipedia', 'domain' => 'uk.wikipedia.org')
    ];

try {
    $timer = new Timer();
    $timer->start();

    $bot = new TemplateParamBot($activerules);

    $errmsg = $bot->doAction($argc, $argv);

    if (! empty($errmsg)) {
    	echo "$errmsg\n";
    	Logger::log($errmsg);
    }

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
    Logger::log($msg);
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'From: Bambots <admin@brucemyers.com>' . "\r\n";
    mail(Config::get(TemplateParamBot::ERROREMAIL), 'TemplateParamBot failed', $msg, $headers);
}
