<?php
/**
 Copyright 2015 Myers Enterprises II

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

namespace com_brucemyers\PageTools;

use PDO;
use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;


class UIHelper
{
	protected $serviceMgr;

	public function __construct()
	{
		$this->serviceMgr = new ServiceManager();
	}

	/**
	 * Get the page tools.
	 *
	 * @param array $params keys = wiki, page
	 * @return array keys = abstract (string - html),
	 * 	categories (array, key = category, value = hidden(bool)),
	 * 	wikidata (array of WikidataItem),
	 *  wikidata_exact_match (bool),
	 *  pagetext (string)
	 */
	public function getResults($params)
	{
		$results = array();

		if (preg_match('!([a-z]{2,3})wiki!', $params['wiki'], $matches)) {
			$domain = $matches[1] . '.wikipedia.org';
		} elseif ($params['wiki'] == 'wikidata') {
			$domain = 'www.wikidata.org';
		} elseif ($params['wiki'] == 'commons') {
			$domain = 'commons.wikimedia.org';
		} else {
			$results['errors'][] = 'Wiki not supported - contact author';
			return;
		}

		$mediawiki = $this->serviceMgr->getMediaWiki($domain);
		$pagename = str_replace(' ', '_', ucfirst(trim($params['page'])));

		// Get the page text
		$pagetext = $mediawiki->getpage($pagename);
		if (! $pagetext) {
			$results['errors'][] = 'Page not found';
			return;
		}

		$results['pagetext'] = $pagetext;

		// Get the abstract
		$value = $mediawiki->getPageLead($pagename);
		if (empty($value)) $value = str_replace('_', ' ', $pagename);

		$results['abstract'] = $value;

		// Get the categories
		$results['categories'] = array();
	    $catparams = array(
            'titles' => $pagename,
            'clprop' => 'hidden',
            'cllimit' => Config::get(MediaWiki::WIKIPAGEINCREMENT)
        );

        $continue = array('continue' => '');

        while ($continue !== false) {
            $clparams = array_merge($catparams, $continue);

            $ret = $mediawiki->getProp('categories', $clparams);

            if (isset($ret['error'])) throw new Exception('PageTools/UIHelper failed ' . $ret['error']);
            if (isset($ret['continue'])) $continue = $ret['continue'];
            else $continue = false;

	        if (! empty($ret['query']['pages'])) {
	        	$page = reset($ret['query']['pages']);
	        	if (isset($page['categories'])) {
		        	foreach ($page['categories'] as $cat) {
		        		$hidden = isset($cat['hidden']);
		        		$cattitle = substr($cat['title'], 9);
		        		$results['categories'][$cattitle] = $hidden;
		        	}
	        	}
	        }
        }

        // Get the wikidata match and/or likely matches
        $dbh_wikidata = $this->serviceMgr->getDBConnection('wikidatawiki');

		$pagename = str_replace('_', ' ', $pagename);
        $sql = "SELECT ips_item_id FROM wb_items_per_site WHERE ips_site_id = ? AND ips_site_page = ?";
        $sth = $dbh_wikidata->prepare($sql);
        $sth->bindValue(1, $params['wiki']);
        $sth->bindValue(2, str_replace('_', ' ', $pagename));
        $sth->execute();

        $wikidata_ids = array();

        if ($row = $sth->fetch(PDO::FETCH_NUM)) {
        	$id = "Q{$row[0]}";
        	$wikidata_ids[$id] = array();
        	$results['wikidata_exact_match'] = true;
        } else {
        	$results['wikidata_exact_match'] = false;
        	$temppage = str_replace('_', ' ', $pagename);
        	// Strip qualifier
        	$temppage = preg_replace('! \([^\)]+\)!', '', $temppage);
			$temppage = $dbh_wikidata->quote("$temppage %"); // allow qualifier

        	$sql = "SELECT ips_item_id, ips_site_id FROM wb_items_per_site WHERE ips_site_page LIKE $temppage LIMIT 10";
        	$sth = $dbh_wikidata->prepare($sql);
        	$sth->execute();
        	$sth->setFetchMode(PDO::FETCH_NUM);

        	while ($row = $sth->fetch()) {
        		$id = "Q{$row[0]}";
        		$site = $row[1];
        		if (! isset($wikidata_ids[$id])) $wikidata_ids[$id] = array();
        		$wikidata_ids[$id][] = $site;
        	}

        	// Strip items that already have a link to our site.
        	foreach ($wikidata_ids as $id => $sites) {
        		foreach ($sites as $site) {
	        		if ($site == $params['wiki']) {
	        			unset($wikidata_ids[$id]);
	        			break;
	        		}
        		}
        	}
        }

        $sth->closeCursor();
        $sth = null;

        // Retrieve the current wikidata revisions
        $wikidatawiki = $this->serviceMgr->getWikidataWiki();
        $results['wikidata'] = $wikidatawiki->getItemsNoCache(array_keys($wikidata_ids));

		return $results;
	}
}