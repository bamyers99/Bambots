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

// Do not allow us to be run directly
if (! isset($GLOBALS['included'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'index.php';
	header("Location: http://$host$uri/$extra");
	exit;
}

$webdir = dirname(__FILE__);
$libdir = dirname($webdir) . DIRECTORY_SEPARATOR . 'lib';

require $libdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

use com_brucemyers\Util\Config;

$topdir = dirname(dirname($webdir));
Config::init($topdir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $GLOBALS['botname'] . '.properties');
Config::set(Config::BASEDIR, $topdir);
