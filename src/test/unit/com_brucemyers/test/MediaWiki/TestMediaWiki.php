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

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use UnitTestCase;

class TestMediaWiki extends UnitTestCase
{
    protected $wiki;

    public function setUp()
    {
        $url = Config::get(MediaWiki::WIKIURLKEY);
        $this->wiki = new MediaWiki($url);
    }

    public function notestLogin()
    {
        $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
        $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
        $this->wiki->login($username, $password);
        $this->pass();
    }

    public function notestPageGet()
    {
        $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
        $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
        $this->wiki->login($username, $password);

        // Make sure the page was retrieved
        FileCache::purgeAll();
        $page = $this->wiki->getPageWithCache('Main Page');
        $this->assertFalse(empty($page), 'Page empty');

        // Make sure the page was cached
        $cachedfiles = array_diff(scandir(FileCache::getCacheDir()), array('..', '.'));
        $this->assertEqual(count($cachedfiles), 1, 'Cached file count must be 1');

        // Make sure the cache was used to retrieve the page
        $cacheFile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . reset($cachedfiles);
        clearstatcache();
        $modified = filemtime($cacheFile);
        sleep(2);

        $page = $this->wiki->getPageWithCache('Main Page');

        clearstatcache();
        $modified2 = filemtime($cacheFile);
        $this->assertEqual($modified, $modified2, "File cache wasn't used");
    }

    public function testGetEditToken()
    {
    	$this->wiki->http->quiet = false;
     	$username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    	$password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    	$this->wiki->login($username, $password);

        $x = $this->wiki->query('?action=query&meta=userinfo&uiprop=blockinfo|hasmsg|groups|rights&format=php');
   		print_r($x);

   		$token = $this->wiki->getedittoken();
    	print_r($token);

    	$result = 0;
    	$answer = 'abc';
    	$test = 'abc';
    	$i = 0;

    	$result |= ord( $answer{$i} ) ^ ord( $test{$i} );
    	echo $result;
    }

    public function notestDoubleLogin()
    {
        $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
        $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
        $this->wiki->login($username, $password);
        $this->wiki->getedittoken();
        $this->wiki->login(null, null);
        $this->pass();
    }

    public function notestGetLinkSafePagename()
    {
    	$origpagename = '';
    	$newpagename = MediaWiki::getLinkSafePagename($origpagename);
    	$this->assertEqual($newpagename, $origpagename, 'Pagename must be empty');

    	$origpagename = ':Category:Living people';
    	$newpagename = MediaWiki::getLinkSafePagename($origpagename);
    	$this->assertEqual($newpagename, $origpagename, 'Pagename must not change');

    	$origpagename = 'Category:Living people';
    	$newpagename = MediaWiki::getLinkSafePagename($origpagename);
    	$this->assertEqual($newpagename, ':' . $origpagename, 'Pagename must have : prefix');

    	$origpagename = 'File:Cat';
    	$newpagename = MediaWiki::getLinkSafePagename($origpagename);
    	$this->assertEqual($newpagename, ':' . $origpagename, 'Pagename must have : prefix 2');

    	$origpagename = 'Template:Convert';
    	$newpagename = MediaWiki::getLinkSafePagename($origpagename);
    	$this->assertEqual($newpagename, $origpagename, 'Pagename must not change 2');

    	$origpagename = 'Fruit:Apple';
    	$newpagename = MediaWiki::getLinkSafePagename($origpagename);
    	$this->assertEqual($newpagename, $origpagename, 'Pagename must not change 3');

    	$origpagename = 'Apple';
    	$newpagename = MediaWiki::getLinkSafePagename($origpagename);
    	$this->assertEqual($newpagename, $origpagename, 'Pagename must not change 4');
    }
}