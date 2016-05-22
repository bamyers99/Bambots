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
use com_brucemyers\DataflowBot\Transformers\FilterColumn;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestFilterColumn extends UnitTestCase
{
	static $data = array(
			array('Article', 'ORES prediction'),
			array('Paris', 'Start'),
			array('Delete 1', 'GA'),
			array('Rome', 'Stub'),
			array('London', 'C'),
			array('Delete 2', 'FA')
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
        $rows = self::$data;
        unset($rows[2]);
        unset($rows[5]);

        $rows = array_values($rows); // reindex

        $flowWriter->expectAt(0, 'writeRecords', array(array($rows[0])));
        $flowWriter->expectAt(1, 'writeRecords', array(array($rows[1])));
        $flowWriter->expectAt(2, 'writeRecords', array(array($rows[2])));
        $flowWriter->expectAt(3, 'writeRecords', array(array($rows[3])));
        $flowWriter->expectCallCount('writeRecords', 4);

    	$transformer = new FilterColumn($serviceMgr);

    	$params = array(
			'filtercol' => '2',
			'filterregex' => '^(Stub|Start|C)$'
    	);

    	$result = $transformer->init($params, true);
    	$this->assertEqual($result, true, 'init failed');

    	$result = $transformer->isFirstRowHeaders();
    	$this->assertEqual($result, true, 'first row must be headers');

    	$result = $transformer->process($flowReader, $flowWriter);
    	$this->assertEqual($result, true, 'process failed');

    	$result = $transformer->terminate();
    	$this->assertEqual($result, true, 'terminate failed');
    }
}
