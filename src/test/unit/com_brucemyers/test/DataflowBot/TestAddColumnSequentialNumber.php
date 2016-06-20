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

namespace com_brucemyers\test\DataflowBot;

use UnitTestCase;
use com_brucemyers\DataflowBot\Transformers\AddColumnSequentialNumber;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestAddColumnSequentialNumber extends UnitTestCase
{
	static $data = array(
			array('Article'),
			array('[[Apple]]'),
			array("Helen D'Arcy Stewart")
	);

    public function testSequentialNumber()
    {
    	$serviceMgr = new ServiceManager();

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = self::$data;
        array_splice($rows[0], 0, 0, 'Rank');
        array_splice($rows[1], 0, 0, 1);
        array_splice($rows[2], 0, 0, 2);
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 1);

    	$transformer = new AddColumnSequentialNumber($serviceMgr);

    	$params = array(
    		'insertpos' => '1',
    		'title' => 'Rank',
    		'startnum' => '1'
    	);

    	$result = $transformer->init($params, true);
    	$this->assertIdentical($result, true, 'init failed');

    	$result = $transformer->isFirstRowHeaders();
    	$this->assertIdentical($result, true, 'first row must be headers');

    	$result = $transformer->process($flowReader, $flowWriter);
    	$this->assertIdentical($result, true);

    	$result = $transformer->terminate();
    	$this->assertIdentical($result, true, 'terminate failed');
    }
}
