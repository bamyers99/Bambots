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

/**
 * Wrapper for ChrisG's bot classes.
 */
class MediaWiki extends wikipedia
{
    const WIKIURLKEY = 'wikiurl';
    const WIKIUSERNAMEKEY = 'wikiusername';
    const WIKIPASSWORDKEY = 'wikipassword';

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
    public function login ($username, $password)
    {
    	$post = array('lgname' => $username, 'lgpassword' => $password);
        $ret = $this->query('?action=login&format=php',$post);

        /* This is now required - see https://bugzilla.wikimedia.org/show_bug.cgi?id=23076 */
        if ($ret['login']['result'] == 'NeedToken') {
        	$post['lgtoken'] = $ret['login']['token'];
        	$ret = $this->query( '?action=login&format=php', $post );
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
        $pages = array();
        $pagenames = implode('|', $pagenames);
        $ret = $this->query('?action=query&format=php&prop=revisions&titles=' . urlencode($pagenames) . '&rvlimit=1&rvprop=content');

        foreach ($ret['query']['pages'] as $page) {
            if (isset($ret['revisions'][0]['*'])) {
                $pages[$ret['title']] = $ret['revisions'][0]['*'];
            }
        }

        return $pages;
    }
}