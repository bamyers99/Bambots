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
use com_brucemyers\Util\Logger;
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
    const MAX_PAGE_SIZE = 2000000;

    static $namespaces = array(
                    0 => 'Article',
                    1 => 'Article talk',
                    2 => 'User',
                    3 => 'User talk',
                    4 => 'Wikipedia',
                    5 => 'Wikipedia talk',
                    6 => 'File',
                    7 => 'File talk',
                    8 => 'MediaWiki',
                    9 => 'MediaWiki talk',
                    10 => 'Template',
                    11 => 'Template talk',
                    12 => 'Help',
                    13 => 'Help talk',
                    14 => 'Category',
                    15 => 'Category talk',
                    100 => 'Portal',
                    101 => 'Portal talk',
                    108 => 'Book',
                    109 => 'Book talk',
                    118 => 'Draft',
                    119 => 'Draft talk',
                    446 => 'Education Program',
                    447 => 'Education Program talk',
                    710 => 'TimedText',
                    711 => 'TimedText talk',
                    828 => 'Module',
                    829 => 'Module talk'
    );

    /**
     * Constructor
     *
     * @param $url String mediawiki url
     */
    public function __construct($url)
    {
        parent::__construct($url);
        $this->http->quiet = true;
        curl_setopt($this->http->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }

    /**
     * Login to mediawiki
     *
     * @param $username String username
     * @param $password String password
     */
    public function login($username, $password)
    {
    	static $saveusername = null;
    	static $savepassword = null;

    	if ($username == null && $password == null) {
    		$username = $saveusername;
    		$password = $savepassword;
    		$this->http->reset(); // Clear stored cookies
    	} else {
    		$saveusername = $username;
    		$savepassword = $password;
    	}

    	$post = array('lgname' => $username, 'lgpassword' => $password);
        $ret = $this->query('?action=login&format=php', $post);

        /* This is now required - see https://bugzilla.wikimedia.org/show_bug.cgi?id=23076 */
        if ($ret['login']['result'] == 'NeedToken') {
        	$post['lgtoken'] = $ret['login']['token'];
        	$ret = $this->query('?action=login&format=php', $post);
        }

        if ($ret['login']['result'] != 'Success') {
            throw new Exception('Login Error ' . print_r($ret, true));
        }
    }

    /**
     * Query mediawiki
     *
     * @param $query string query string
     * @param $post array Post data using key=>value
     * @param $repeat int Retry start value, max = 5
     * @return array Response
     */
    public function query($query, $post = null, $repeat = 0)
    {
        if ($post == null) {
            $ret = $this->http->get($this->url . $query);
        } else {
            $ret = $this->http->post($this->url . $query, $post);
        }

        $http_code = $this->http->http_code();

        $ok = false;
        if ($http_code == '200') $ok = true;
        elseif (strpos($query, 'action=edit') !== false && ($http_code == '504' || $http_code == '503')){
            return array(); // Proxy timeout on large edit requests
        }

		if (! $ok) {
			if ($repeat < 5) {
			    Logger::log("*** query retry #$repeat $query http_code:" . $http_code . ' errortext:' . $this->http->http_errortext());
			    sleep($repeat * 10);
				return $this->query($query, $post, ++$repeat);
			} else {
				throw new Exception('HTTP Error ' . $this->http->http_code());
			}
		}

        return unserialize($ret);
    }

    /**
     * Edits a page.
     * @param $page Page name to edit.
     * @param $data Data to post to page.
     * @param $summary Edit summary to use.
     * @param $minor Whether or not to mark edit as minor.  (Default false)
     * @param $bot Whether or not to mark edit as a bot edit.  (Default true)
     * @param $repeat int Retry start value, max = 5
     * @return api result
     **/
    public function edit($page, &$data, $summary = '', $minor = false, $bot = true, $section = null, $detectEC=false, $maxlag='', $repeat = 0) {
    	if ($this->token==null) {
    		$this->token = $this->getedittoken();
    	}
    	$params = array(
    			'title' => $page,
    			'text' => $data,
    			'token' => $this->token,
    			'summary' => $summary,
    			($minor?'minor':'notminor') => '1',
    			($bot?'bot':'notbot') => '1'
    	);
    	if ($section != null) {
    		$params['section'] = $section;
    	}
    	if ($this->ecTimestamp != null && $detectEC == true) {
    		$params['basetimestamp'] = $this->ecTimestamp;
    		$this->ecTimestamp = null;
    	}
    	$tmaxlag = '';
    	if ($maxlag!='') {
    		$tmaxlag='&maxlag='.$maxlag;
    	}

    	$ret = $this->query('?action=edit&format=php'.$tmaxlag,$params);

    	if (isset($ret['error']) && $ret['error']['info'] == 'Invalid token') {
    		if ($repeat < 5) {
			    Logger::log("*** edit retry #$repeat $page errortext:Invalid token");
			    sleep($repeat * 10);
			    $this->token = null;
			    $this->login(null, null);

				return $this->edit($page, $data, $summary, $minor, $bot, $section, $detectEC, $maxlag, ++$repeat);
			} else {
				throw new Exception('Edit Error Invalid token');
			}
     	}

    	return $ret;
    }

    /**
     * Get multiple pages last revision
     *
     * @param $pagenames array Page names
     * @return array Page text, pagename=>revision info (timestamp|minor|comment|user)
     */
    public function getPagesLastRevision($pagenames)
    {
        if (empty($pagenames)) return array();
        $pages = array();
        $pageChunks = array_chunk($pagenames, Config::get(self::WIKIPAGEINCREMENT));

        foreach ($pageChunks as $pageChunk) {
        	$pagenames = implode('|', $pageChunk);
        	$ret = $this->query('?action=query&format=php&prop=revisions&titles=' . urlencode($pagenames) . '&rvprop=timestamp|flags|comment|user&continue=');

        	if (isset($ret['error'])) {
        		throw new Exception('Query Error ' . $ret['error']['info']);
        	}

        	$normalized = array();

        	if (isset($ret['query']['normalized'])) {
        		foreach ($ret['query']['normalized'] as $normal) {
        			$normalized[$normal['to']] = $normal['from'];
        		}
        	}

        	foreach ($ret['query']['pages'] as $page) {
        		if (isset($page['revisions'][0])) {
        			$pagename = $page['title'];
        			if (isset($normalized[$pagename])) $pagename = $normalized[$pagename];
        			$pages[$pagename] = $page['revisions'][0];
        		}
        	}
        }

        return $pages;
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

            if (isset($ret['error'])) {
                throw new Exception('Query Error ' . $ret['error']['info']);
            }

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
                $page = FileCache::getData($pagename);
                if ($page !== false) $cached[$pagename] = $page;
            }

            $cachednames = array_keys($cached);
            $uncachednames = array_diff($pagenames, $cachednames);
        }

        $uncached = $this->getPages($uncachednames);

        // Save uncached
        foreach ($uncached as $pagename => $page) {
            FileCache::putData($pagename, $page);
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
    		$page = FileCache::getData($pagename);
    		if ($page !== false) $cached[$pagename] = true;
    	}

    	$cachednames = array_keys($cached);
    	$uncachednames = array_diff($pagenames, $cachednames);

    	$pageChunks = array_chunk($uncachednames, Config::get(self::WIKIPAGEINCREMENT));

    	foreach ($pageChunks as $pageChunk) {
    		$uncached = $this->getPages($pageChunk);

	    	foreach ($uncached as $pagename => $page) {
	    		FileCache::putData($pagename, $page);
	    	}
	    }
    }

    /**
     * Get recent changes
     *
     * https://www.mediawiki.org/wiki/API:Recentchanges
     *
     * @param $params array Recent changes query parameters rc...
     * @return array Recent changes ['query']['recentchanges'], ['continue']; pass ['continue'] back in as a param to get more results
     * @deprecated Use MediaWiki::getList('recentchanges', $params)
     */
    public function getRecentChanges(&$params)
    {
        return $this->getList('recentchanges', $params);
    }

    /**
     * Get user contributions
     *
     * https://www.mediawiki.org/wiki/API:Usercontribs
     *
     * @param $params array Recent changes query parameters uc...
     * @return array Recent changes ['query']['usercontribs'], ['continue']; pass ['continue'] back in as a param to get more results
     * @deprecated Use MediaWiki::getList('usercontribs', $params)
     */
    public function getContributions(&$params)
    {
        return $this->getList('usercontribs', $params);
    }

    /**
     * Get category members
     *
     * https://www.mediawiki.org/wiki/API:Categorymembers
     *
     * @param $params array Recent changes query parameters cm...
     * @return array Category members ['query']['categorymembers'], ['continue']; pass ['continue'] back in as a param to get more results
     * @deprecated Use MediaWiki::getList('categorymembers', $params)
     */
    public function getCategoryMembers(&$params)
    {
        return $this->getList('categorymembers', $params);
    }

    /**
     * Get log events
     *
     * https://www.mediawiki.org/wiki/API:Logevents
     *
     * @param $params array Log events query parameters le...
     * @return array Recent changes ['query']['logevents'], ['continue']; pass ['continue'] back in as a param to get more results
     * @deprecated Use MediaWiki::getList('logevents', $params)
     */
    public function getLogEvents(&$params)
    {
        return $this->getList('logevents', $params);
    }

    /**
     * Get list
     *
     * https://www.mediawiki.org/wiki/API:Categorymembers
     * https://www.mediawiki.org/wiki/API:Logevents
     * https://www.mediawiki.org/wiki/API:Recentchanges
     * https://www.mediawiki.org/wiki/API:Usercontribs
     *
     * @param $listtype string List type - categorymembers, logevents, recentchanges, usercontribs, etc.
     * @param $params array Query parameters xx...
     * @return ..., ['continue']; pass ['continue'] back in as a param to get more results
     */
    public function getList($listtype, $params)
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

        $ret = $this->query("?action=query&format=php&list=$listtype" . $addparams);

        if (isset($ret['error'])) {
        	throw new Exception("$listtype Error " . $ret['error']['info']);
        }

        return $ret;
    }

    /**
     * Get link safe namespace prefix.
     *
     * @param int $id Namespace id
     * @return string Namespace prefix
     */
    public static function getLinkSafeNamespacePrefix($id)
    {
    	$id = (int)$id;
    	if ($id == 0) return '';
    	if ($id == 1) return 'Talk:';
    	if ($id == 6 || $id == 14) return ':' . self::$namespaces[$id] . ':';
    	return self::$namespaces[$id] . ':';
    }
}