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
use com_brucemyers\DataflowBot\Loaders\WikiLoader;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestWikiLoader extends UnitTestCase
{
	static $data = array(
			array('{|'),
			array('|-'),
			array('|Col1||col2'),
			array('|}')
	);

    public function testLoader()
    {
    	$serviceMgr = new ServiceManager();

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

        Mock::generate('com_brucemyers\\MediaWiki\\ResultWriter', 'MockResultWriter');
        $resultWriter = &new \MockResultWriter();
        $resultWriter->expectOnce('writeResults', array(
        	'User:DataflowBot/output/Testpage (id-1)',
        	"Header\n{|\n|-\n|Col1||col2\n|}\nFooter",
        	'Data update'));

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = &new \MockServiceManager();
        $serviceMgr->returns('getWikiResultWriter', $resultWriter);
        $serviceMgr->returnsAt(0, 'replaceVars', 'Header');
        $serviceMgr->returnsAt(1, 'replaceVars', 'Footer');

        $loader = new WikiLoader($serviceMgr);

    	$params = array(
    		'wiki' => 'enwiki',
    	    'pagename' => 'Testpage',
    		'header' => 'Header',
    		'footer' => 'Footer'
    	);

    	$result = $loader->init($params, true, 1);
    	$this->assertIdentical($result, true, 'init failed');

    	$result = $loader->isFirstRowHeaders();
    	$this->assertIdentical($result, true, 'first row must be headers');

    	$result = $loader->process($flowReader);
    	$this->assertIdentical($result, true, 'process failed');

    	$result = $loader->terminate();
    	$this->assertIdentical($result, true, 'terminate failed');
    }
}
