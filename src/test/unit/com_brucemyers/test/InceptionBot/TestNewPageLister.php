<?php
/**
 Copyright 2013 Myers Enterprises II

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

namespace com_brucemyers\test\InceptionBot;

use com_brucemyers\InceptionBot\NewPageLister;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Config;
use UnitTestCase;

class TestNewPageLister extends UnitTestCase
{
    public function TestNewPageLister()
    {
        $url = Config::get(MediaWiki::WIKIURLKEY);
        $mediawiki = new MediaWiki($url);

        $earliestTimestamp = date('Ymd') . '000000'; // Beginning of today
        $latestTimestamp = date('Ymd') . '120000';
        $lister = new NewPageLister($mediawiki, $earliestTimestamp, $latestTimestamp);

        $allpages = array();

        while (($pages = $lister->getNextBatch()) !== false) {
            $allpages = array_merge($allpages, $pages);
        }

        $this->assertTrue((count($allpages) > 0), 'No new pages');

        //print_r($allpages);
    }
}