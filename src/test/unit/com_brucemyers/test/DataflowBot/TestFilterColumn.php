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
use com_brucemyers\DataflowBot\Transformers\FilterColumn;
use com_brucemyers\DataflowBot\ServiceManager;
use com_brucemyers\test\DataflowBot\CreateTablesFilterCol;
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

    public function testIncludeRegex()
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
			'includeregex' => '^(Stub|Start|C)$'
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

    public function testExcludeRegex()
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
			'filtercol' => '1',
			'excluderegex' => '^Delete'
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

    public function testDisambigNo()
    {
    	$serviceMgr = new ServiceManager();
    	$dbh_enwiki = $serviceMgr->getDBConnection('enwiki');
    	new CreateTablesFilterCol($dbh_enwiki);

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
			'filtercol' => '1',
			'disambig' => 'no'
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

    public function testDisambigYes()
    {
    	$serviceMgr = new ServiceManager();
    	$dbh_enwiki = $serviceMgr->getDBConnection('enwiki');
    	new CreateTablesFilterCol($dbh_enwiki);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = self::$data;
        unset($rows[1]);
        unset($rows[3]);
        unset($rows[4]);

        $rows = array_values($rows); // reindex

        $flowWriter->expectAt(0, 'writeRecords', array(array($rows[0])));
        $flowWriter->expectAt(1, 'writeRecords', array(array($rows[1])));
        $flowWriter->expectAt(2, 'writeRecords', array(array($rows[2])));
        $flowWriter->expectCallCount('writeRecords', 3);

    	$transformer = new FilterColumn($serviceMgr);

    	$params = array(
			'filtercol' => '1',
			'disambig' => 'yes'
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
