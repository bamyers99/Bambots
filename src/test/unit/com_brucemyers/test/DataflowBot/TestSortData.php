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

namespace com_brucemyers\test\DataflowBot;

use UnitTestCase;
use com_brucemyers\DataflowBot\Transformers\SortData;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestSortData extends UnitTestCase
{
	static $data = array(
			array('Article', 'ORES prediction', 'Views'),
			array('Rome', 'Stub', '4'),
			array('Paris', 'Start', '6'),
			array('Berlin', 'C', '2'),
			array('London', 'C', '5'),
			array('Milan', 'Stub', '5')
	);

	static $sorted_data = array(
			array('Milan', 'Stub', '5'),
			array('Rome', 'Stub', '4'),
			array('Paris', 'Start', '6'),
			array('London', 'C', '5'),
			array('Berlin', 'C', '2')
	);

    public function testTransformer()
    {
    	$serviceMgr = new ServiceManager();

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();

        $flowWriter->expectAt(0, 'writeRecords', array(array(self::$data[0])));
        $flowWriter->expectAt(1, 'writeRecords', array(self::$sorted_data));
        $flowWriter->expectCallCount('writeRecords', 2);

    	$transformer = new SortData($serviceMgr);

    	$params = array(
			'sortcol1' => '2',
			'sorttype1' => 'enum',
			'sortdir1' => 'asc',
			'sortenum1' => 'Stub|Start|C',
			'sortcol2' => '3',
			'sorttype2' => 'numeric',
			'sortdir2' => 'desc'
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
