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
 
 php runner.php com_brucemyers/test/Util/TestWikitableParser
 
 */
$GLOBALS['botname'] = 'testbot';

$testdir = dirname(__FILE__);

require $testdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

if ($argc == 1) {
    // Run all tests
    require $testdir . DIRECTORY_SEPARATOR . 'AllTests.php';
} else {
    // Run one test
    $testcase = str_replace('\\', DIRECTORY_SEPARATOR, $argv[1]) . '.php';
    require $testdir . DIRECTORY_SEPARATOR . 'unit' . DIRECTORY_SEPARATOR . $testcase;
}
