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
use com_brucemyers\DataflowBot\Extractors\PopularPages;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestPopularPages extends UnitTestCase
{

    public function testExtractor()
    {
    	$pop_pages = <<< EOT
::{| class="wikitable sortable"
|-
! scope="col" | Rank
! scope="col" | Article
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" |
! scope="col" | Views
! scope="col" | %-Mobi
! scope="col" | %-Zero
|-
|1
|[[Main Page]]
|
|
|
|
|
|
|
|
|
|
|align="right"|127,477,970
|align="right"|28.77%
|align="right"|0.00%
|-
|2
|[[Azúcar Moreno]]
|
|
|
|
|
|[[File:Symbol start class.svg|16px]]
|
|[[File:Symbol question.svg|16px]]
|
|
|align="right"|1,770,126
|align="right"|0.01%
|align="right"|0.00%
|-
|3
|[[XHamster]]
|
|
|
|
|
|
|[[File:Symbol stub class.svg|16px]]
|
|
|
|align="right"|1,494,461
|align="right"|95.23%
|align="right"|0.00%
|-
|4
|[[Captain America: Civil War]]
|
|
|
|[[File:Symbol b class.svg|16px]]
|
|
|
|[[File:Symbol question.svg|16px]]
|
|
|align="right"|1,284,748
|align="right"|57.10%
|align="right"|0.00%
|-
|5
|[[X-Men: Apocalypse]]
|
|
|
|
|
|[[File:Symbol start class.svg|16px]]
|
|
|
|
|align="right"|1,009,485
|align="right"|56.69%
|align="right"|0.00%
|}
EOT;

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
        $flowWriter = &new \MockFlowWriter();
        $rows = array(array('Article', 'Views'));
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $rows = array(array('Main_Page', '127,477,970'));
        $flowWriter->expectAt(1, 'writeRecords', array($rows));
        $rows = array(array('Captain_America:_Civil_War', '1,284,748'));
        $flowWriter->expectAt(2, 'writeRecords', array($rows));
        $rows = array(array('X-Men:_Apocalypse', '1,009,485'));
        $flowWriter->expectAt(3, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 4);

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = &new \MockMediaWiki();
        $mediaWiki->returnsAt(0, 'getpage', $pop_pages);

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = &new \MockServiceManager();
        $serviceMgr->returns('getMediaWiki', $mediaWiki);

    	$extractor = new PopularPages($serviceMgr);

    	$params = array(
    	    'pagename' => 'User:West.andrew.g/Popular_pages',
			'minmobile' => '2',
			'maxmobile' => '95'
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
