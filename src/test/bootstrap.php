<?php
/*
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

$testdir = dirname(__FILE__);
$libdir = dirname($testdir) . DIRECTORY_SEPARATOR . 'lib';

require $libdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$classLoader = new SplClassLoader(null, $testdir . DIRECTORY_SEPARATOR . 'lib');
$classLoader->register();

require_once $testdir . DIRECTORY_SEPARATOR . 'simpletest' . DIRECTORY_SEPARATOR . 'autorun.php' ;

use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;

$topdir = dirname(dirname($testdir));
Config::init($topdir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'testbot.properties');
Config::set(Config::BASEDIR, $topdir);

Logger::init($topdir . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'testbot.log');
