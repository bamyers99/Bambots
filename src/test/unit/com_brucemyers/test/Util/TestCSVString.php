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

namespace com_brucemyers\test\Util;

use com_brucemyers\Util\CSVString;
use UnitTestCase;

class TestCSVString extends UnitTestCase
{

    public function testParse()
    {
    	$line = '"a,b,c",5,"before\"after"';
    	$fields = CSVString::parse($line);

    	$this->assertEqual(count($fields), 3, 'Field count error');
    	$this->assertEqual($fields[0], 'a,b,c', 'Field 1 parse error');
    	$this->assertEqual($fields[1], 5, 'Field 2 parse error');
        $this->assertEqual($fields[2], 'before"after', 'Field 3 parse error');
    }

    public function testFormat()
    {
		$fields = array('a,b,c', 5, 'before"after');

		$line = CSVString::format($fields);

		$this->assertEqual($line, '"a,b,c","5","before\"after"', 'Format error');
    }
}