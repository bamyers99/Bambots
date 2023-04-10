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

use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\WikitableParser;
use Exception;

/**
 * Wikidata wrapper
 */
class WikidataWiki extends MediaWiki
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('https://www.wikidata.org/w/api.php');
    }

    /**
     * Get item with caching
     *
     * @param string $itemname Item name Q... or Property:P...
     * @return WikidataItem Item data
     */
    public function getItemWithCache($itemname)
    {
        $pages = $this->getPagesWithCache((array)$itemname);
        $page = reset($pages);
        if (! empty($page)) {
        	// Convert JSON to php array
        	return new WikidataItem(json_decode($page, true));
        }
        return new WikidataItem(array());
    }

    /**
     * Get items with caching
     *
     * @param array $itemnames Item name Q... or Property:P... or EntitySchema:E...
     * @return array WikidataItem Item data
     */
    public function getItemsWithCache($itemnames)
    {
    	$ret = array();
    	$pages = $this->getPagesWithCache((array)$itemnames);

    	foreach ($pages as $page) {
    	    if ($page === false) continue;
    	    
    		$ret[] = new WikidataItem(json_decode($page, true));
    	}

    	return $ret;
    }

    /**
     * Get items without caching
     *
     * @param array $itemnames Item name Q... or Property:P...
     * @return array WikidataItem Item data
     */
    public function getItemsNoCache($itemnames)
    {
    	$ret = array();
        $pages = $this->getPages((array)$itemnames);

        foreach ($pages as $page) {
        	$ret[] = new WikidataItem(json_decode($page, true));
        }

        return $ret;
    }

    /**
     * Get property suggestions for an item
     *
     * @param string $qid Item id
     * @param string $language label language
     * @return array suggestions (id, rating, label)
     */
    public function getPropertySuggestions($qid, $language)
    {
        $query = "?action=wbsgetsuggestions&format=php&entity=$qid&limit=10&language=$language&include=all";

        $ret = $this->query($query);

        return $ret;
    }
    
    /**
     * Set a claim
     *
     * @param int $baserevid
     * @param string $username
     * @param string $csrftoken
     * @param string $claim json claim data
     * @return string empty = success, else error info
     */
    public function createClaim($baserevid, $username, $csrftoken, $claim)
    {
        $opts = [
            'action' => 'wbsetclaim',
            'format' => 'json',
            'baserevid' => $baserevid,
            'assertuser' => $username,
            'token' => $csrftoken,
            'claim' => $claim
        ];
        
        $ret = $this->query('', $opts);
        
        if (isset($ret['error'])) return $ret['error']['info'];
        return '';
    }
    
    /**
     * Create a claim
     *
     * @param int $baserevid
     * @param string $username
     * @param string $csrftoken
     * @param string $entity QID
     * @param string $snaktype 'value', 'novalue', 'somevalue'
     * @param string $propid PID
     * @param string $propvalue JSON value
     * @return string empty = success, else error info
     */
    public function createCreateClaim($baserevid, $username, $csrftoken, $entity, $snaktype, $propid, $propvalue)
    {
        $opts = [
            'action' => 'wbcreateclaim',
            'format' => 'json',
            'baserevid' => $baserevid,
            'assertuser' => $username,
            'token' => $csrftoken,
            'entity' => $entity,
            'snaktype' => $snaktype,
            'property' => $propid
        ];
        
        if ($snaktype == 'value') $opts['value'] = $propvalue;

        $ret = $this->query('', $opts);

        if (isset($ret['error'])) return $ret['error']['info'];
        return '';
    }

    /**
     * Get search entities
     *
     * @param string $search Text to search for
     * @param string $language Language code to search in
     * @param array Query parameters xx...
     * @throws Exception
     * @return ..., ['search-continue']; pass ['search-continue'] back in as a param to get more results
     */
    public function getSearchEntities($search, $language, $params = [])
    {
        $addparams = '';

        foreach ($params as $key => $value) {
            $addparams .= "&$key=" . urlencode($value);
        }

        $search = urlencode($search);
        $language = urlencode($language);

        $ret = $this->query("?action=wbsearchentities&format=php&search=$search&language=$language" . $addparams);

        if (isset($ret['error'])) {
            throw new Exception("getWBSearchEntities Error " . $ret['error']['info'] . "\n". print_r($params, true));
        }

        return $ret;
    }

    /**
     * Cache deleted properties
     */
    public function cacheDeletedProperties()
    {
        // Check for the sentinel file
        $sentinel_file = 'Property:P2439';
        $page = FileCache::getData($sentinel_file);
        
        if ($page !== false) return;
        
        $config = $this->getPage('Wikidata:Database reports/Deleted properties');
        
        $configtable = WikitableParser::getTables($config)[0];
        
        foreach ($configtable['rows'] as $row) {
            preg_match('!P\d+!', $row[0], $matches);
            $pid = $matches[0];
            $label = $row[1];
            $description = $row[2];
            
            $page = '{"type":"property","datatype":"deleted","id":"' . $pid . '",' .
                '"labels":{"en":{"language":"en","value":' . json_encode($label) . '}},' .
                '"descriptions":{"en":{"language":"en","value":' . json_encode($description) . '}}}';
            
            FileCache::putData("Property:$pid", $page);
        }
    }
}