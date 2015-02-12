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
use com_brucemyers\DataflowBot\Transformers\AddColumnFirstImage;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestAddColumnFirstImage extends UnitTestCase
{
	static $data = array(
			array('Rank', 'Article'),
			array('1', '[[Apple]]'),
			array('2', "Helen D'Arcy Stewart")
	);

    public function testFirstImage()
    {
    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = self::$data;
        $rows[0][] = 'Image';
        $rows[1][] = '[[File:Red Apple.jpg|left|100x100px]]';
        $rows[2][] = "[[File:Helen D'Arcy Stewart.png|left|100x100px]]";
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 1);

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = &new \MockMediaWiki();
        $mediaWiki->returnsAt(0, 'getPageWithCache', "{{hatnote|needs references}}
        	[[File:Red Apple.jpg|left]]
        	The '''apple''' is a fruit in the [[Malus]] family.
        	");

        $mediaWiki->returnsAt(1, 'getPageWithCache', "<!-- Afc -->
        	{{Infobox person
        	|name = Helen D'Arcy Stewart
        	|image = Helen D'Arcy Stewart.png
        	|birthdate = {{birth date|1934|01|07}}
        	}}
        	'''Helen D'Arcy Stewart''' (born 1934) is an [[artist]] who is {{convert|5|ft}} tall.
        	");

    	$realServiceMgr = new ServiceManager();
        Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = &new \MockServiceManager();
        $serviceMgr->returns('getMediaWiki', $mediaWiki);
        $serviceMgr->returns('getDBConnection', $realServiceMgr->getDBConnection('enwiki'));

    	$transformer = new AddColumnFirstImage($serviceMgr);

    	$params = array(
    		'insertpos' => 'append',
    	    'lookupcol' => '2',
    		'title' => 'Image',
    		'nonfree' => 'no',
    		'fileoptions' => 'left|100x100px'
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
