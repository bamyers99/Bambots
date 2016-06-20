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
use com_brucemyers\DataflowBot\Extractors\SQLQuery;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestSQLQueryExtractor extends UnitTestCase
{
    public function testTransformer()
    {
    	$serviceMgr = new ServiceManager();
    	$dbh_enwiki = $serviceMgr->getDBConnection('enwiki');
    	new CreateTablesACPC($dbh_enwiki);
    	new CreateTablesT20($dbh_enwiki);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $flowWriter->expectAt(0, 'writeRecords', array(array(array('ID','Namespace','Title'))));
        $flowWriter->expectAt(1, 'writeRecords', array(array(array('1','1','Apple'))));
        $flowWriter->expectAt(2, 'writeRecords', array(array(array('2','11','Fruit'))));
        $flowWriter->expectCallCount('writeRecords', 3);

    	$extractor = new SQLQuery($serviceMgr);

    	$params = array(
    		'wiki' => 'enwiki',
     		'sql' => 'SELECT page_id AS `ID`, page_namespace AS `Namespace`, page_title AS `Title` FROM page'
    	);

    	$result = $extractor->init($params, true);
    	$this->assertIdentical($result, true, 'init failed');

    	$result = $extractor->isFirstRowHeaders();
    	$this->assertIdentical($result, true, 'first row must be headers');

    	$result = $extractor->process($flowWriter);
    	$this->assertIdentical($result, true);

    	$result = $extractor->terminate();
    	$this->assertIdentical($result, true, 'terminate failed');
    }
}
