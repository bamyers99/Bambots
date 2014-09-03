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

namespace com_brucemyers\RenderedWiki;

use ChrisG\http;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use Exception;

class RenderedWiki
{
    const WIKIRENDERURLKEY = 'wiki.renderurl';

    public $http;
    public $url;

    /**
     * Constructor
     *
     * @param $url String mediawiki url
     */
    public function __construct($url)
    {
        $this->http = new http;
        $this->url = $url;
        $this->http->quiet = true;
        curl_setopt($this->http->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }

    /**
     * Get multiple pages
     *
     * @param $pagenames array Page names
     * @return array Page text, pagename=>text
     */
    public function getPages($pagenames)
    {
        if (empty($pagenames)) return array();
        $pages = array();

        foreach ($pagenames as $pagename) {
        	$pages[$pagename] = $this->http->get($this->url . $pagename);
        }

        return $pages;
    }

    /**
     * Get a page with caching
     *
     * @param $pagename string Page name
     * @return string Page text
     */
    public function getPageWithCache($pagename)
    {
        $pages = $this->getPagesWithCache((array)$pagename);
        $page = reset($pages);
        if (! empty($page)) return $page;
        return '';
    }

    /**
     * Get multiple pages with caching
     *
     * @param $pagenames array Page names
     * @param $refetch bool true = refetch, false (default) = no refetch
     * @return array Page text, pagename=>text
     */
    public function getPagesWithCache($pagenames, $refetch = false)
    {
        $cached = array();

        if ($refetch) {
            $uncachednames = $pagenames;
        } else {
            // Check the cache
            foreach ($pagenames as $pagename) {
                $page = FileCache::getData($pagename . '#R');
                if ($page !== false) $cached[$pagename] = $page;
            }

            $cachednames = array_keys($cached);
            $uncachednames = array_diff($pagenames, $cachednames);
        }

        $uncached = $this->getPages($uncachednames);

        // Save uncached
        foreach ($uncached as $pagename => $page) {
            FileCache::putData($pagename . '#R', $page);
        }

        return $cached + $uncached;
    }

    /**
     * Cache multiple pages
     *
     * @param $pagenames array Page names
     */
    public function cachePages($pagenames)
    {
        $cached = array();

    	// Check the cache
    	foreach ($pagenames as $pagename) {
    		$page = FileCache::getData($pagename . '#R');
    		if ($page !== false) $cached[$pagename] = true;
    	}

    	$cachednames = array_keys($cached);
    	$uncachednames = array_diff($pagenames, $cachednames);

    	$pageChunks = array_chunk($uncachednames, 50);

    	foreach ($pageChunks as $pageChunk) {
    		$uncached = $this->getPages($pageChunk);

	    	foreach ($uncached as $pagename => $page) {
	    		FileCache::putData($pagename . '#R', $page);
	    	}
	    }
    }
}