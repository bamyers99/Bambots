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
use com_brucemyers\DataflowBot\Transformers\AddColumnRevisionID;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestAddColumnRevisionID extends UnitTestCase
{
	static $data = array(
			array('Rank', 'Article'),
			array('1', 'Apple'),
			array('2', "Helen D'Arcy Stewart"),
			array('3', 'Article not found')
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
        $rows[0][] = 'Revision ID';
        $rows[1][] = 721239847;
        $rows[2][] = 721584760;
        $flowWriter->expectAt(0, 'writeRecords', array(array($rows[0])));
        $flowWriter->expectAt(1, 'writeRecords', array(array($rows[1])));
        $flowWriter->expectAt(2, 'writeRecords', array(array($rows[2])));
        $flowWriter->expectCallCount('writeRecords', 3);

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = &new \MockMediaWiki();
        $mediaWiki->returnsAt(0, 'getPagesLastRevision', array(
        	'Apple' => array('revid' => 721239847),
        	"Helen D'Arcy Stewart" => array('revid' => 721584760)
        ));

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = &new \MockServiceManager();
        $serviceMgr->returns('getMediaWiki', $mediaWiki);

        $transformer = new AddColumnRevisionID($serviceMgr);

    	$params = array(
    		'insertpos' => 'append',
    	    'lookupcol' => '2',
    		'title' => 'Revision ID'
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
