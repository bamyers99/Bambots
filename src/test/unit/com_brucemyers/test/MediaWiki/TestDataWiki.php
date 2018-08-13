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

namespace com_brucemyers\test\MediaWiki;

use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\MediaWiki\WikidataItem;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use UnitTestCase;

class TestDataWiki extends UnitTestCase
{
    protected $wiki;

    public function setUp()
    {
        $this->wiki = new WikidataWiki();
    }

    public function testPageGet()
    {
        // Make sure the page was retrieved
        FileCache::purgeAll();
        $item = $this->wiki->getItemWithCache('Q3699773');
        $this->assertFalse(empty($item), 'Page empty');
        $this->assertTrue($item instanceof WikidataItem, 'Not WikidataItem');

        // Make sure the page was cached
        $cachedfiles = array_diff(scandir(FileCache::getCacheDir()), array('..', '.'));
        $this->assertEqual(count($cachedfiles), 1, 'Cached file count must be 1');

        // Make sure the cache was used to retrieve the page
        $cacheFile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . reset($cachedfiles);
        clearstatcache();
        $modified = filemtime($cacheFile);
        sleep(2);

        $item = $this->wiki->getItemWithCache('Q3699773');

        clearstatcache();
        $modified2 = filemtime($cacheFile);
        $this->assertEqual($modified, $modified2, "File cache wasn't used");

        print_r($item);
    }

    public function testGetStatementsOfType()
    {
        $item = $this->wiki->getItemWithCache('Q3699773');
    	$instancesof = $item->getStatementsOfType(WikidataItem::TYPE_INSTANCE_OF);
		$this->assertTrue(in_array(WikidataItem::INSTANCE_OF_DISAMBIGUATION, $instancesof), 'No instance of Disambiguation');
    }
}