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
class MediaWiki
{
    protected $wiki;

    /**
     * Constructor
     *
     * @param $url String mediawiki url
     */
    public function __construct($url)
    {
        $this->wiki = new wikipedia();
        $this->wiki->url = $url;
    }

    /**
     * Login to a mediawiki
     *
     * @param $username String username
     * @param $password String password
     */
    public function login($username, $password)
    {
        $ret = $this->wiki->login($username, $password);
        if ($ret['login']['result'] != 'Success') {
            throw new Exception("Login failed - {$ret['login']['result']}");
        }
    }
}