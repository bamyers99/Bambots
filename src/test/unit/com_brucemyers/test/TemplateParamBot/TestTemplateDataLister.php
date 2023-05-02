<?php
/**
 Copyright 2023 Myers Enterprises II

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

namespace com_brucemyers\test\TemplateParamBot;

use com_brucemyers\TemplateParamBot\TemplateDataLister;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Config;
use UnitTestCase;

class TestTemplateDataLister extends UnitTestCase
{
    public function TestTemplateDataLister()
    {
        $url = Config::get(MediaWiki::WIKIURLKEY);
        $mediawiki = new MediaWiki($url);

        $lister = new TemplateDataLister($mediawiki, true);

        $allpages = [];

        while (($pages = $lister->getNextBatch()) !== false) {
            $allpages = array_merge($allpages, $pages);
        }

        $this->assertTrue((count($allpages) > 0), 'No templatedatas');

        echo "Template count: " . count($allpages) . "\n";
    }
}