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

namespace com_brucemyers\InceptionBot;

use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use Exception;

class MovedPageLister
{
    protected $mediawiki;
    protected $params;
    protected $continue = array('continue' => '');

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki wiki
     * @param $earliestTimestamp (20130917000000) format
     * @param $latestTimestamp
     */
    public function __construct($mediawiki, $earliestTimestamp, $latestTimestamp)
    {
        $this->mediawiki = $mediawiki;
        $this->params = array(
            'leprop' => 'title|details',
            'lelimit' => Config::get(MediaWiki::WIKICHANGESINCREMENT),
            'ledir' => 'newer',
            'letype' => 'move',
            'lestart' => $earliestTimestamp,
            'leend' => $latestTimestamp
        );
    }

    /**
     * Get next batch of moved pages
     *
     * @return mixed false: no more pages, array: keys = oldtitle, newtitle, oldns, newns
     */
    public function getNextBatch()
    {
        if ($this->continue === false) return false;
        $params = array_merge($this->params, $this->continue);

        $ret = $this->mediawiki->getList('logevents', $params);

        if (isset($ret['error'])) throw new Exception('MovedPageLister.getNextBatch() failed ' . $ret['error']);
        if (isset($ret['continue'])) $this->continue = $ret['continue'];
        else $this->continue = false;

        $events = array();
        foreach ($ret['query']['logevents'] as $event) {
            $events[] = array('oldns' => $event['ns'], 'oldtitle' => $event['title'], 'newns' => $event['move']['new_ns'], 'newtitle' => $event['move']['new_title']);
        }

        return $events;
    }
}