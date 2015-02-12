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
use com_brucemyers\DataflowBot\Transformers\ToWikitable;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestToWikitable extends UnitTestCase
{
	static $data = array(
			array('Article', 'Image'),
			array('Apple', 'Apple.jpg'),
			array('Fruit', 'Fruit.png')
	);

	public function testSortable()
	{
		$serviceMgr = new ServiceManager();

		Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
		$flowReader = &new \MockFlowReader();
		$flowReader->returns('readRecords', false);
		$flowReader->returnsAt(0, 'readRecords', self::$data);

		Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
		$flowWriter = &new \MockFlowWriter();
		$rows = self::$data;
		$lines = array();
		$lines[] = array('|-');
		$lines[] = array('!Article!!class="unsortable"|Image');
		$lines[] = array('|-');
		$lines[] = array('|Apple||Apple.jpg');
		$lines[] = array('|-');
		$lines[] = array('|Fruit||Fruit.png');

		$flowWriter->expectAt(0, 'writeRecords', array(array(array("{| class=\"wikitable sortable\""))));
		$flowWriter->expectAt(1, 'writeRecords', array($lines));
		$flowWriter->expectAt(2, 'writeRecords', array(array(array("|}"))));
		$flowWriter->expectCallCount('writeRecords', 3);

		$transformer = new ToWikitable($serviceMgr);

		$params = array(
				'sortable' => '1',
				'unsortable' => '2'
		);

		$result = $transformer->init($params, true);
		$this->assertEqual($result, true, 'init failed');

		$result = $transformer->isFirstRowHeaders();
		$this->assertEqual($result, false, 'first row must not be headers');

		$result = $transformer->process($flowReader, $flowWriter);
		$this->assertEqual($result, true, 'process failed');

		$result = $transformer->terminate();
		$this->assertEqual($result, true, 'terminate failed');
	}

    public function testNonSortable()
    {
    	$serviceMgr = new ServiceManager('com_brucemyers\\test\\DataflowBot\\MockCurl');

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = self::$data;
        $lines = array();
        $lines[] = array('|-');
        $lines[] = array('!Article!!Image');
        $lines[] = array('|-');
        $lines[] = array('|Apple||Apple.jpg');
        $lines[] = array('|-');
        $lines[] = array('|Fruit||Fruit.png');

        $flowWriter->expectAt(0, 'writeRecords', array(array(array("{| class=\"wikitable\""))));
        $flowWriter->expectAt(1, 'writeRecords', array($lines));
        $flowWriter->expectAt(2, 'writeRecords', array(array(array("|}"))));
        $flowWriter->expectCallCount('writeRecords', 3);

    	$transformer = new ToWikitable($serviceMgr);

    	$params = array(
    		'sortable' => '0',
    		'unsortable' => '2'
    	);

    	$result = $transformer->init($params, true);
    	$this->assertEqual($result, true, 'init failed');

    	$result = $transformer->isFirstRowHeaders();
    	$this->assertEqual($result, false, 'first row must not be headers');

    	$result = $transformer->process($flowReader, $flowWriter);
    	$this->assertEqual($result, true, 'process failed');

    	$result = $transformer->terminate();
    	$this->assertEqual($result, true, 'terminate failed');
    }
}
