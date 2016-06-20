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
use com_brucemyers\DataflowBot\Extractors\UrlCsvExtractor;
use com_brucemyers\DataflowBot\ServiceManager;
use com_brucemyers\test\DataflowBot\MockCurl;
use Mock;

class TestUrlCsvExtractor extends UnitTestCase
{

    public function testExtractor()
    {
    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
        $flowWriter = &new \MockFlowWriter();
        $rows = array(MockCurl::$rows[0]);
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $rows = array(MockCurl::$rows[1]);
        $flowWriter->expectAt(1, 'writeRecords', array($rows));
        $rows = array(MockCurl::$rows[2]);
        $flowWriter->expectAt(2, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 3);

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = &new \MockServiceManager();
        $serviceMgr->returns('getCurl', new MockCurl());

    	$extractor = new UrlCsvExtractor($serviceMgr);

    	$params = array(
    	    'sep' => 'tab',
    		'delim' => '',
    		'firstrow' => '1',
    		'subdmn' => 'quarry',
    		'file' => '/query/5'
    	);

    	$result = $extractor->init($params);
    	$this->assertIdentical($result, true, 'init failed');

    	$result = $extractor->isFirstRowHeaders();
    	$this->assertIdentical($result, true, 'first row must be headers');

    	$result = $extractor->process($flowWriter);
    	$this->assertIdentical($result, true);

    	$result = $extractor->terminate();
    	$this->assertIdentical($result, true, 'terminate failed');
    }
}
