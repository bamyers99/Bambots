<?php
/**
 Copyright 2020 Myers Enterprises II

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
use com_brucemyers\DataflowBot\Extractors\Popular1000Pages;
use Mock;

class TestPopular1000Pages extends UnitTestCase
{

    public function testExtractor()
    {
    	$pop_pages = <<< EOT
{| class="wikitable sortable"
!Rank
!Article
!Total weekly views
!Days in top 1k this week
|-
|1
|[[w:Test1|Test1]]
|1509261
|7
|-
|2
|[[w:Test2|Test2]]
|1454336
|7
|}
EOT;

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
        $flowWriter = new \MockFlowWriter();
        $rows = [[ 'Article', 'Views']];
        $flowWriter->expectAt(0, 'writeRecords', [$rows]);
        $rows = [['Test1', '1509261']];
        $flowWriter->expectAt(1, 'writeRecords', [$rows]);
        $rows = [['Test2', '1454336']];
        $flowWriter->expectAt(2, 'writeRecords', [$rows]);
        $flowWriter->expectCallCount('writeRecords', 3);

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = new \MockMediaWiki();
        $mediaWiki->returnsAt(0, 'getpage', $pop_pages);

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = new \MockServiceManager();
        $serviceMgr->returns('getMediaWiki', $mediaWiki);

    	$extractor = new Popular1000Pages($serviceMgr);

    	$params = [
    	    'pagename' => 'User:HostBot/Top_1000_report'
    	];

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
