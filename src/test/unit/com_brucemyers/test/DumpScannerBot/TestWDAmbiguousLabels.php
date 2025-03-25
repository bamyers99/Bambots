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

namespace com_brucemyers\test\DumpScannerBot;

use com_brucemyers\DumpScannerBot\Scanners\WDAmbiguousLabels;
use com_brucemyers\DumpScannerBot\DumpScannerBot;
use UnitTestCase;

class TestWDAmbiguousLabels extends UnitTestCase
{

    public function testDumpLabels()
    {
        $params = [];
        $params['htmldir'] = Config::get(DumpScannerBot::HTMLDIR);
        $params['outputdir'] = Config::get(DumpScannerBot::OUTPUTDIR);
        
        $scanner = new WDAmbiguousLabels();
        $scanner->init($params);
        
        $testdata = <<<EOD
EOD;
        
        $hndl = fopen('php://memory', 'r+');
        file_put_contents($hndl, $testdata);
        rewind($hndl);
        
        $scanner->dumpLabels($hndl);
        

		//$this->assertEqual(count($rows['groups']), 9, 'Wrong number of groups');
    }
}