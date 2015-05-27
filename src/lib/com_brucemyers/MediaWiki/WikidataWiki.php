<?php
/**
 Copyright 2014 Myers Enterprises II

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
     * @param string $itemname Item name Q...
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
     * Get items without caching
     *
     * @param array $itemnames Item name Q...
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
}