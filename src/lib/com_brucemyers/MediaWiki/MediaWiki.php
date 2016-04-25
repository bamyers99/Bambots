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
use DateTime;

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

    static public $namespaces = array(
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
    				120 => 'Property',
    				121 => 'Property talk',
    				200 => 'Grants',
    				201 => 'Grants talk',
    				202 => 'Research',
    				203 => 'Research talk',
    				204 => 'Participation',
    				205 => 'Participation talk',
    				206 => 'Iberocoop',
    				207 => 'Iberocoop talk',
    				208 => 'Programs',
    				209 => 'Programs talk',
    				446 => 'Education Program',
                    447 => 'Education Program talk',
    				470 => 'Schema',
                    471 => 'Schema talk',
    				484 => 'Graph',
                    485 => 'Graph talk',
    				486 => 'Data',
                    487 => 'Data talk',
    				710 => 'TimedText',
                    711 => 'TimedText talk',
                    828 => 'Module',
                    829 => 'Module talk',
                    866 => 'CNBanner',
                    867 => 'CNBanner talk',
                    1198 => 'Translations',
                    1199 => 'Translations talk'
    );

    static $flipped_namespaces = false;

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

		$retval = unserialize($ret);
		if ($retval === false) {
			// Patch the last 8 bytes because of garbage chars if $ret length = 32767
			$ret = substr_replace($ret, "\";}}}}}}", -8, 8);
			$retval = unserialize($ret);
			if ($retval === false) {
				throw new Exception("unserialize failed = $ret");
			}
		}
        return $retval;
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
     	} elseif (isset($ret['error'])) {
     		if ($repeat < 5) {
     			Logger::log("*** edit retry #$repeat $page errortext:{$ret['error']['info']}");
     			sleep($repeat * 60);

     			return $this->edit($page, $data, $summary, $minor, $bot, $section, $detectEC, $maxlag, ++$repeat);
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
     * Get multiple revisions text
     *
     * @param $revids array Revision ids
     * @return array pagetitle=>array(namespace, revid, revision text, revid, revision text) second revid is optional
     */
    public function getRevisionsText($revids)
    {
        if (empty($revids)) return array();
        $revs = array();
        $revChunks = array_chunk($revids, Config::get(self::WIKIPAGEINCREMENT));

        foreach ($revChunks as $revChunk) {
        	$revids = implode('|', $revChunk);
        	$ret = $this->query('?action=query&format=php&prop=revisions&revids=' . $revids . '&rvprop=ids|content&continue=');

        	if (isset($ret['error'])) {
        		throw new Exception('Query Error ' . $ret['error']['info']);
        	}

        	foreach ($ret['query']['pages'] as $page) {
       			$pagetitle = $page['title'];
       			$ns = $page['ns'];
       			if (! isset($page['revisions'])) continue;

       			$pagetext = (isset($page['revisions'][0]['*'])) ? $page['revisions'][0]['*'] : '';
         		$revs[$pagetitle] = array($ns, $page['revisions'][0]['revid'], $pagetext);

       			if (isset($page['revisions'][1])) {
         			$revs[$pagetitle][] = $page['revisions'][1]['revid'];
       				$pagetext = (isset($page['revisions'][1]['*'])) ? $page['revisions'][1]['*'] : '';
         			$revs[$pagetitle][] = $pagetext;
        		}
        	}
        }

        return $revs;
    }

    /**
     * Get multiple revisions categories
     *
     * @param $revids array Revision ids
     * @return array pagetitle=>array(categories)
     */
    public function getRevisionsCategories($revids)
    {
        if (empty($revids)) return array();
        $revs = array();

        foreach ($revids as $revid) {
        	$ret = $this->query("?action=parse&format=php&oldid=$revid&prop=categories");

        	if (isset($ret['error'])) {
        		Logger::log('MediaWiki->getRevisionsCategories Error ' . $ret['error']['info']);
        		continue;
        	}

        	$pagetitle = $ret['parse']['title'];
        	$revs[$pagetitle] = array();

        	foreach ($ret['parse']['categories'] as $category) {
       			$revs[$pagetitle][] = str_replace('_', ' ', $category['*']);
        	}
        }

        return $revs;
    }

    /**
     * Parse categories from text.
     *
     * @param string $pagetitle
     * @param string $text
     * @return array Categories
     */
    public function parseCategoriesFromText($pagetitle, $text)
    {
   		$cats = array();

    	$post = array('prop' => 'categories', 'title' => $pagetitle, 'text' => $text, 'contentformat' => 'text/x-wiki',
    		'contentmodel' => 'wikitext');
    	$ret = $this->query("?action=parse&format=php", $post);

        if (isset($ret['error'])) {
        	Logger::log('MediaWiki->parseCategoriesFromText Error ' . $ret['error']['info']);
        	return $cats;
        }

    	foreach ($ret['parse']['categories'] as $category) {
       		$cats[] = str_replace('_', ' ', $category['*']);
        }

        return $cats;
    }

    /**
     * Get a pages lead.
     *
     * @param string $pagetitle
     * @param int $sentences (optional) Number of sentences to retrieve, only one of this or $characters can be > 0, default = 1
     * @param int $characters (optional) Number of characters to retrieve, default = 0
     * @param bool $plaintext (optional) Retrieve plaintext vs limited html, default = false
     */
    public function getPageLead($pagetitle, $sentences = 1, $characters = 0, $plaintext = false)
    {
    	$query = '?action=query&format=php&prop=extracts&exintro=&titles='  . urlencode($pagetitle);

    	if ($characters > 0) $query .= "&exchars=$characters";
    	else {
    		if ($sentences < 1) $sentences = 1;
    		$query .= "&exsentences=$sentences";
    	}

    	if ($plaintext) $query .= '&explaintext=';

    	$ret = $this->query($query);

    	if (isset($ret['error'])) {
    		Logger::log('MediaWiki->getPageLead Error ' . $ret['error']['info']);
    		return '';
    	}

    	if (! empty($ret['query']['pages'])) {
        	foreach ($ret['query']['pages'] as $page) {
    			return $page['extract'];
        	}
    	}

    	return '';
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
     * Get property list
     *
     * @param string $proptype Prop type - categories, etc.
     * @param array Query parameters xx...
     * @throws Exception
     * @return ..., ['continue']; pass ['continue'] back in as a param to get more results
     */
    public function getProp($proptype, $params)
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

    	$ret = $this->query("?action=query&format=php&prop=$proptype" . $addparams);

    	if (isset($ret['error'])) {
    		throw new Exception("$listtype Error " . $ret['error']['info']);
    	}

    	return $ret;
    }

    /**
     * Get namespace prefix.
     *
     * @param int $id Namespace id
     * @return string Namespace prefix
     */
    public static function getNamespacePrefix($id)
    {
    	$id = (int)$id;
    	if ($id == 0) return '';
    	if ($id == 1) return 'Talk:';
    	if (! isset(self::$namespaces[$id])) return '';
    	return self::$namespaces[$id] . ':';
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
    	if (! isset(self::$namespaces[$id])) return '';
    	return self::$namespaces[$id] . ':';
    }

    /**
     * Get a link safe page name. ie. :Category:Living people
     *
     * @param string $pagename Pagename
     * @return string Link safe pagename
     */
    public static function getLinkSafePagename($pagename)
    {
    	if (strlen($pagename) == 0) return '';
    	if ($pagename[0] == ':') return $pagename;

		$parts = explode(':', $pagename, 2);
		if (count($parts) == 1) return $pagename;

		$namespace = str_replace('_', ' ', $parts[0]);

		if (! self::$flipped_namespaces) self::$flipped_namespaces = array_flip(self::$namespaces);

		if (! isset(self::$flipped_namespaces[$namespace])) return $pagename;
		$ns = self::$flipped_namespaces[$namespace];

		if ($ns == 6 || $ns == 14) return ':' . $pagename;
		return $pagename;
    }

    /**
     * Get the namespace name for a pagename.
     *
     * @param string $pagename
     * @return string Namespace name with _ replaced with space
     */
    public static function getNamespaceName($pagename)
    {
    	if (strlen($pagename) == 0) return '';
    	if ($pagename[0] == ':') $pagename = substr($pagename, 1);

		$parts = explode(':', $pagename, 2);
		if (count($parts) == 1) return '';

		$namespace = str_replace('_', ' ', $parts[0]);

		if ($namespace == 'Talk') return $namespace;

		if (! self::$flipped_namespaces) self::$flipped_namespaces = array_flip(self::$namespaces);

		if (! isset(self::$flipped_namespaces[$namespace])) return '';

		return $namespace;
    }

    /**
     * Get the namespace id.
     *
     * @param string $namespace Namespace name
     * @return int Namespace id, -1 = not found
     */
    public static function getNamespaceId($namespace)
    {
		if ($namespace == '') return 0;
		if ($namespace == 'Talk') return 1;
		$namespace = str_replace('_', ' ', $namespace);

		if (! self::$flipped_namespaces) self::$flipped_namespaces = array_flip(self::$namespaces);

		if (! isset(self::$flipped_namespaces[$namespace])) return -1;

		return self::$flipped_namespaces[$namespace];
    }

    /**
     * Convert a wiki timestamp to a unix timestamp.
     *
     * @param string $timestamp Wiki timestamp
     * @return int Unix timestamp
     */
    public static function wikiTimestampToUnixTimestamp($timestamp)
    {
    	$dt = DateTime::createFromFormat('YmdHis', $timestamp);
    	return $dt->getTimestamp();
    }

    /**
     * Convert a unix timestamp to a wiki timestamp.
     *
     * @param int $timestamp Unix timestamp
     * @return string Wiki timestamp
     */
    public static function unixTimestampToWikiTimestamp($timestamp)
    {
    	return date('YmdHis', $timestamp);
    }
}