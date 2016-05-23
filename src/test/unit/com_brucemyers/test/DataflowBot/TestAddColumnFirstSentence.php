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
use com_brucemyers\DataflowBot\Transformers\AddColumnFirstSentence;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestAddColumnFirstSentence extends UnitTestCase
{
	static $data = array(
			array('Rank', 'Article'),
			array('1', 'Apple'),
			array('2', "Helen D'Arcy Stewart")
	);

    public function testFirstSentence()
    {
    	$serviceMgr = new ServiceManager();

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = self::$data;
        $rows[0][] = 'Abstract';
        $rows[1][] = "The <b>apple</b> is a fruit in the Malus family.                                                          "; // Pad past 100 chars
        $rows[2][] = "<b>Helen D'Arcy Stewart</b> (born 1934) is an artist who is 5ft (10 m) tall.                              ";
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 1);

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = &new \MockMediaWiki();
        $mediaWiki->returnsAt(0, 'getPageLead', $rows[1][2]);
        $mediaWiki->returnsAt(1, 'getPageLead', $rows[2][2]);

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = &new \MockServiceManager();
        $serviceMgr->returns('getMediaWiki', $mediaWiki);

        $transformer = new AddColumnFirstSentence($serviceMgr);

    	$params = array(
    		'insertpos' => 'append',
    	    'lookupcol' => '2',
    		'title' => 'Abstract'
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

    public function testSpecial()
    {
		$data = array(
				array('1', 'The SpongeBob Movie: Sponge Out of Water'),
		);
    	$serviceMgr = new ServiceManager();

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', $data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
    	$rows = $data;
    	$rows[0][] = "The <b>SpongeBob Movie: Sponge Out of Water</b> is a 2015 American animated/live action adventure comedy film, based on the Nickelodeon television series SpongeBob SquarePants, created by Stephen Hillenburg.";
    	$flowWriter->expectAt(0, 'writeRecords', array($rows));
    	$flowWriter->expectCallCount('writeRecords', 1);

    	Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
    	$mediaWiki = &new \MockMediaWiki();

    	$pagetext = "The <b>SpongeBob Movie: Sponge Out of Water</b> is a 2015 American animated/live action adventure comedy film, based on the Nickelodeon television series SpongeBob SquarePants, created by Stephen Hillenburg.";
    	$mediaWiki->returnsAt(0, 'getPageLead', $pagetext);

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
    	$serviceMgr = &new \MockServiceManager();
    	$serviceMgr->returns('getMediaWiki', $mediaWiki);

    	$transformer = new AddColumnFirstSentence($serviceMgr);

    	$params = array(
    			'insertpos' => 'append',
    			'lookupcol' => '2',
    			'title' => 'Abstract'
    	);

    	$result = $transformer->init($params, false);
    	$this->assertEqual($result, true, 'init failed');

    	$result = $transformer->isFirstRowHeaders();
    	$this->assertEqual($result, false, 'first row must not be headers');

    	$result = $transformer->process($flowReader, $flowWriter);
    	$this->assertEqual($result, true, 'process failed');

    	$result = $transformer->terminate();
    	$this->assertEqual($result, true, 'terminate failed');

    }
}
