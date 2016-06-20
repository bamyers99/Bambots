<?php
/**
 Copyright 2016 Myers Enterprises II

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

use com_brucemyers\MediaWiki\MediaWiki;
use Exception;

class TriagePageLister
{
    protected $mediawiki;
    protected $params;
    protected $continue = array('offset' => '');
    protected $earliestTimestamp;

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
            'limit' => '200',
            'namespace' => '0',
            'dir' => 'newestfirst',
            'showreviewed' => 'Y',
            'showunreviewed' => 'Y'
        );
        $this->earliestTimestamp = $earliestTimestamp;
        $this->continue['offset'] = $latestTimestamp;
    }

    /**
     * Get next batch of moved pages
     *
     * @return mixed false: no more pages, array: keys = title, ns, timestamp, user
     */
    public function getNextBatch()
    {
        if ($this->continue === false) return false;
        $params = array_merge($this->params, $this->continue);

        $addparams ='';

        foreach ($params as $key => $value) {
        	$addparams .= "&$key=" . urlencode($value);
        }

        $ret = $this->mediawiki->query("?action=pagetriagelist&format=php" . $addparams);

        if (isset($ret['error'])) throw new Exception('TriagePageLister.getNextBatch() failed ' . $ret['error']);

        $pages = array();
        foreach ($ret['pagetriagelist']['pages'] as $page) {
        	if (strcmp($page['creation_date'], $this->earliestTimestamp) < 0) {
        		$this->continue = false;
        		break;
        	}

        	$ts = $page['creation_date'];
        	$ts = substr($ts, 0, 4) . '-' . substr($ts, 4, 2) . '-' . substr($ts, 6, 2) . 'T' .
          		substr($ts, 8, 2) . ':' . substr($ts, 10, 2) . ':' . substr($ts, 12, 2) . 'Z';

            $pages[] = array('ns' => '0', 'title' => $page['title'], 'timestamp' => $ts,
                            'user' => $page['user_name']);
            $this->continue['offset'] = $page['creation_date'];
            $this->continue['pageoffset'] = $page['pageid'];
        }

        if (count($pages) < 200) $this->continue = false;

        return $pages;
    }
}