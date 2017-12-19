<?php
/**
 Copyright 2017 Myers Enterprises II

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

namespace com_brucemyers\InceptionBot;

use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use Exception;

class RemovedRedirectPageLister
{
    protected $mediawiki;
    protected $params;
    protected $continue = array('continue' => '');

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki
     * @param $earliestTimestamp (20130917000000) format
     * @param $latestTimestamp
     * @param $namespace string (optional) default = 0, separate multiple with '|'
     */
    public function __construct($mediawiki, $earliestTimestamp, $latestTimestamp, $namespace = '0')
    {
        $this->mediawiki = $mediawiki;
        $this->params = array(
            'rcprop' => 'title|user|timestamp',
            'rclimit' => Config::get(MediaWiki::WIKICHANGESINCREMENT),
            'rcdir' => 'newer',
            'rctype' => 'edit',
            'rcstart' => $earliestTimestamp,
            'rcend' => $latestTimestamp,
            'rcnamespace' => $namespace,
        	'rctag' => 'mw-removed-redirect',
        	'rcshow' => '!bot'
        );
    }

    /**
     * Get next batch of new pages
     *
     * @return mixed false: no more pages, array: keys = ns, title, user, timestamp (2013-09-17T19:24:15Z) format
     */
    public function getNextBatch()
    {
        if ($this->continue === false) return false;
        $params = array_merge($this->params, $this->continue);

        $ret = $this->mediawiki->getList('recentchanges', $params);

        if (isset($ret['error'])) throw new Exception('RemovedRedirectPageLister.getNextBatch() failed ' . $ret['error']);
        if (isset($ret['continue'])) $this->continue = $ret['continue'];
        else $this->continue = false;

        return $ret['query']['recentchanges'];
    }
}