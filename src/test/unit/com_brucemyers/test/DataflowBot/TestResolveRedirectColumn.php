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
use com_brucemyers\DataflowBot\Transformers\ResolveRedirectColumn;
use com_brucemyers\DataflowBot\ServiceManager;
use com_brucemyers\test\DataflowBot\CreateTablesRRC;
use Mock;

class TestResolveRedirectColumn extends UnitTestCase
{
	static $data = array(
			array('Rank', 'Article'),
			array('1', 'Apple'),
			array('2', 'Fruit')
	);

    public function testRRC()
    {
    	$serviceMgr = new ServiceManager();
    	$dbh_enwiki = $serviceMgr->getDBConnection('enwiki');
    	new CreateTablesRRC($dbh_enwiki);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = self::$data;
        $rows[2][1] = 'Pom';
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 1);

    	$transformer = new ResolveRedirectColumn($serviceMgr);

    	$params = array(
    		'linkcol' => '2'
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
