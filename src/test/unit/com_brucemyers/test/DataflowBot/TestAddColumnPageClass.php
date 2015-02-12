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
use com_brucemyers\DataflowBot\Transformers\AddColumnPageClass;
use com_brucemyers\DataflowBot\ServiceManager;
use com_brucemyers\test\DataflowBot\CreateTablesACPC;
use Mock;

class TestAddColumnPageClass extends UnitTestCase
{
	static $data = array(
			array('Rank', 'Article'),
			array('1', '[[Apple]]'),
			array('2', 'Template:Fruit')
	);

    public function testBestTextAppend()
    {
    	$serviceMgr = new ServiceManager();
    	$dbh_enwiki = $serviceMgr->getDBConnection('enwiki');
    	new CreateTablesACPC($dbh_enwiki);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$rows = array_slice(self::$data, 0, 1);
    	$flowReader->returnsAt(0, 'readRecords', $rows);
    	$rows = array_slice(self::$data, 1);
    	$flowReader->returnsAt(1, 'readRecords', $rows);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = array_slice(self::$data, 0, 1);
        $rows[0][] = 'Class';
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $rows = array_slice(self::$data, 1);
        $rows[0][] = 'B';
        $rows[1][] = 'Unassessed';
        $flowWriter->expectAt(1, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 2);

    	$transformer = new AddColumnPageClass($serviceMgr);

    	$params = array(
    		'insertpos' => 'append',
    	    'lookupcol' => '2',
    		'priority' => 'best',
    		'valuetype' => 'text',
    		'title' => 'Class'
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

    public function testCommonImageColumn2()
    {
    	$serviceMgr = new ServiceManager('com_brucemyers\\test\\DataflowBot\\MockCurl');
    	$dbh_enwiki = $serviceMgr->getDBConnection('enwiki');
    	new CreateTablesACPC($dbh_enwiki);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$rows = array_slice(self::$data, 0, 1);
    	$flowReader->returnsAt(0, 'readRecords', $rows);
    	$rows = array_slice(self::$data, 1);
    	$flowReader->returnsAt(1, 'readRecords', $rows);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = array_slice(self::$data, 0, 1);
        array_splice($rows[0], 1, 0, 'Class');
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $rows = array_slice(self::$data, 1);
        array_splice($rows[0], 1, 0, '[[File:Symbol c class.svg|16px|C-class]]');
        array_splice($rows[1], 1, 0, '[[File:Symbol question.svg|16px|Unassessed-class]]');
        $flowWriter->expectAt(1, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 2);

    	$transformer = new AddColumnPageClass($serviceMgr);

    	$params = array(
    		'insertpos' => '2',
    		'lookupcol' => '2',
    		'priority' => 'common',
    		'valuetype' => 'image',
    		'title' => 'Class'
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
