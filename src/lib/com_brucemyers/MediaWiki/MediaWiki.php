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

namespace com_brucemyers\MediaWiki;

use ChrisG\wikipedia;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use Exception;

/**
 * Wrapper for ChrisG's bot classes.
 */
class MediaWiki extends wikipedia
{
    const WIKIURLKEY = 'wiki.url';
    const WIKIUSERNAMEKEY = 'wiki.username';
    const WIKIPASSWORDKEY = 'wiki.password';
    const WIKIPAGEINCREMENT = 'wiki.pagefetchincrement';
    const WIKICHANGESINCREMENT = 'wiki.recentchangesincrement';

    /**
     * Constructor
     *
     * @param $url String mediawiki url
     */
    public function __construct($url)
    {
        parent::__construct($url);
        $this->http->quiet = true;
    }

    /**
     * Login to mediawiki
     *
     * @param $username String username
     * @param $password String password
     */
    public function login($username, $password)
    {
    	$post = array('lgname' => $username, 'lgpassword' => $password);
        $ret = $this->query('?action=login&format=php', $post);

        /* This is now required - see https://bugzilla.wikimedia.org/show_bug.cgi?id=23076 */
        if ($ret['login']['result'] == 'NeedToken') {
        	$post['lgtoken'] = $ret['login']['token'];
        	$ret = $this->query('?action=login&format=php', $post);
        }

        if ($ret['login']['result'] != 'Success') {
            throw new Exception('Login Error ' . $ret['login']['result']);
        }
    }

    /**
     * Query mediawiki
     *
     * @param $query string query string
     * @param $post array Post data using key=>value
     * @param $repeat int Retry start value, max = 10
     * @return array Response
     */
    public function query($query, $post = null, $repeat = 0)
    {
        if ($post == null) {
            $ret = $this->http->get($this->url . $query);
        } else {
            $ret = $this->http->post($this->url . $query, $post);
        }

		if ($this->http->http_code() != "200") {
			if ($repeat < 10) {
			    sleep($repeat * 10);
				return $this->query($query, $post, ++$repeat);
			} else {
				throw new Exception('HTTP Error ' . $this->http->http_code());
			}
		}

        return unserialize($ret);
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
        $pageChunks = array_chunk($pagenames, Config::get(self::WIKIPAGEINCREMENT));

        foreach ($pageChunks as $pageChunk) {
            $pagenames = implode('|', $pageChunk);
            $ret = $this->query('?action=query&format=php&prop=revisions&titles=' . urlencode($pagenames) . '&rvprop=content&continue=');

            $normalized = array();

            if (isset($ret['query']['normalized'])) {
                foreach ($ret['query']['normalized'] as $normal) {
                    $normalized[$normal['to']] = $normal['from'];
                }
            }

            foreach ($ret['query']['pages'] as $page) {
                if (isset($page['revisions'][0]['*'])) {
                    $pagename = $page['title'];
                    if (isset($normalized[$pagename])) $pagename = $normalized[$pagename];
                    $pages[$pagename] = $page['revisions'][0]['*'];
                }
            }
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
     * @return array Page text, pagename=>text
     */
    public function getPagesWithCache($pagenames)
    {
        $cached = array();

        // Check the cache
        foreach ($pagenames as $pagename) {
            $page = FileCache::getData($pagename);
            if ($page !== false) $cached[$pagename] = $page;
        }

        $cachednames = array_keys($cached);
        $uncachednames = array_diff($pagenames, $cachednames);

        $uncached = $this->getPages($uncachednames);

        // Save uncached
        foreach ($uncached as $pagename => $page) {
            FileCache::putData($pagename, $page);
        }

        return $cached + $uncached;
    }

    /**
     * Get recent changes
     *
     * https://www.mediawiki.org/wiki/API:Recentchanges
     *
     * @param $params array Recent changes query parameters rc...
     * @return array Recent changes ['query']['recentchanges'], ['continue']; pass ['continue'] back in as a param to get more results
     */
    public function getRecentChanges($params)
    {
        if (! isset($params['continue'])) {
            $params['continue'] = '';
        } elseif (is_array($params['continue'])){
            $continue = $params['continue'];
            unset($params['continue']);
            $params = array_merge($params, $continue);
        }

        $addparams ='';

        foreach ($params as $key => $value) {
            $addparams .= "&$key=" . urlencode($value);
        }

        $ret = $this->query('?action=query&format=php&list=recentchanges' . $addparams);

        return $ret;
    }
}