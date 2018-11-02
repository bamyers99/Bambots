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

namespace com_brucemyers\MediaWiki;

use ChrisG\http;
use \Exception;

/**
 * Wikidata wrapper
 */
class WikidataSPARQL extends MediaWiki
{
    public $http;
    public $url;

	/**
     * Constructor
     */
    public function __construct()
    {
        $this->http = new http;
        $this->http->quiet = true;
        $this->url = 'https://query.wikidata.org/sparql?format=json&query=';
    }

    /**
     * Perform a SPARQL query
     * @param string $query URL encoded query
     * @param number $repeat for internal use
     * @throws Exception
     * @return array of array(variable => array('type' => type, 'value' => value, 'xml:lang' => language (for labels and descriptions)))
     */
    public function query($query, $repeat=0)
    {
    	$ret = $this->http->get($this->url . $query);

    	if ($this->http->http_code() != "200") {
    		if ($repeat < 10) {
    			return $this->query($query, ++$repeat);
    		} else {
    			throw new Exception("HTTP Error.");
    		}
    	}

    	$ret = json_decode($ret, true);

    	return $ret['results']['bindings'];
    }
}