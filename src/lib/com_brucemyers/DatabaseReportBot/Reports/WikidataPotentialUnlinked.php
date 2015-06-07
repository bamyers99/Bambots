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

namespace com_brucemyers\DatabaseReportBot\Reports;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\RenderedWiki\RenderedWiki;
use com_brucemyers\MediaWiki\WikidataItem;
use com_brucemyers\Util\FileCache;
use PDO;

class WikidataPotentialUnlinked extends DatabaseReport
{
	public function init($apis, $params)
	{
		if (empty($params)) return true;

		$option = $params[0];

		switch ($option) {
			case 'dumpmissingenwiki':
				$this->dumpmissingenwiki($apis['dbh_wikidata']);
				return false;
				break;
		}

		return true;
	}

    public function getUsage()
    {
    	return " - Look for potential unlinked enwiki -> wikidata items" .
    	"\t\tdumpmissingenwiki - dump wikidata items missing enwiki link";
    }

	public function getTitle()
	{
		return 'Wikidata potential unlinked';
	}

	public function getIntro()
	{
		return 'Wikidata potential unlinked; data as of <onlyinclude>%s</onlyinclude>.';
	}

	public function getHeadings()
	{
		return array('Article', 'Potential wikidata item', 'Existing wikidata item to merge');
	}

	public function getRows($apis)
	{
		$dbh_wikidata = $apis['dbh_wikidata'];
		$dbh_wiki = $apis['dbh_wiki'];
		$datawiki = $apis['datawiki'];

		// Get the max epp_entity_id
		$sql = "SELECT MAX(epp_entity_id) FROM wb_entity_per_page";
		$sth = $dbh_wikidata->query($sql);
		$row = $sth->fetch(PDO::FETCH_NUM);
		$maxid = (int)$row[0];
		$sth->closeCursor();

		// Retrieve 10,000 ids at a time
		$startid = 0;
		$endid = 10000;

		$results = array();

		$sth3 = $dbh_wikidata->prepare("SELECT ips_item_id FROM wb_items_per_site WHERE ips_site_id = 'enwiki' AND ips_site_page = ?");
		$sth4 = $dbh_wikidata->prepare("SELECT ips_site_page FROM wb_items_per_site WHERE ips_item_id = ?");

		while ($startid < $maxid) {
			// Retrieve the wikidata items without enwiki links

			$sql = "SELECT epp_entity_id FROM wb_entity_per_page epp
					LEFT JOIN wb_items_per_site ips ON ips.ips_item_id = epp.epp_entity_id AND ips.ips_site_id = 'enwiki'
					WHERE epp_entity_id > $startid AND epp_entity_id <= $endid AND
						epp.epp_entity_type = 'item' AND ips.ips_row_id IS NULL";
			$sth = $dbh_wikidata->query($sql);
			$sth->setFetchMode(PDO::FETCH_NUM);
			$ids = array();

			while ($row = $sth->fetch()) {
				$ids[] = (int)$row[0];
			}
			$sth->closeCursor();

			// Get non-enwiki language links

			foreach ($ids as $id) {
				$sth4->bindValue(1, $id);
				$sth4->execute();
				$sth4->setFetchMode(PDO::FETCH_NUM);
				$titles = array();

				while ($row = $sth4->fetch()) {
					$title = str_replace(' ', '_', $row[0]);
					$titles[$title] = true; // Removes duplicates
				}
				$sth4->closeCursor();

				if (empty($titles)) continue;

				// See if enwiki has a page with one of the names
				$sql = "SELECT page_title, page_is_redirect FROM page WHERE page_namespace = 0 AND page_title IN ( " .
					implode(',', array_fill(0, count($titles), '?')) . ")";

				$sth2 = $dbh_wiki->prepare($sql);
				$sth2->execute(array_keys($titles));

				$enrows = $sth2->fetchAll(PDO::FETCH_NUM);
				$sth2->closeCursor();

				foreach ($enrows as $enrow) {
					$title = str_replace('_', ' ', $enrow[0]);
					if (strpos($title, ' ') === false) continue; // Skip single words
					$is_redirect = (int)$enrow[1];

					// See if this page already has a wikidata link
					$sth3->bindValue(1, $title);
					$sth3->execute();
					$already_id = '';
					if ($row = $sth3->fetch(PDO::FETCH_NUM)) $already_id = $row[0];
					$sth3->closeCursor();

					$already_url = '';
					if (! empty($already_id)) {
						if ($is_redirect) continue; // Skip if redirect
						$already_url = "[https://www.wikidata.org/wiki/Q$already_id Q$already_id]";
					}

					// See if this item is a dab page
					$item = $datawiki->getItemWithCache("Q$id");
					$instancesof = $item->getStatementsOfType(WikidataItem::TYPE_INSTANCE_OF);
					if (in_array(WikidataItem::INSTANCE_OF_DISAMBIGUATION, $instancesof)) continue 2; // Doing here so above continues reduce calls

					$firstcol = "[[$title]]";
					if ($is_redirect) $firstcol .= ' (redirect)';

					$results[] = array($firstcol, "[https://www.wikidata.org/wiki/Q$id Q$id]", $already_url);
					break;
				}
			}

			$startid = $endid;
			$endid += 10000;
		}

		usort($results, function($a, $b) {
			$diff = strcmp($a[2], $b[2]); // Sort by already url
			if ($diff != 0) return $diff;
			return strcmp($a[0], $b[0]); // Sort by title
		});

		$results['linktemplate'] = false;

		return $results;
	}

	function dumpmissingenwiki($dbh_wikidata)
	{
		// Retrieve 10,000 ids at a time
		$startid = 0;
		$endid = 10000;
		$tempfile = self::getMissingEnwikiPath();
		$hndl = fopen($tempfile, 'w');

		// Get the max epp_entity_id
		$sql = "SELECT MAX(epp_entity_id) FROM wb_entity_per_page";
		$sth = $dbh_wikidata->query($sql);
		$row = $sth->fetch(PDO::FETCH_NUM);
		$maxid = (int)$row[0];
		$sth->closeCursor();

		while ($startid < $maxid) {
			// Retrieve the wikidata items without enwiki links

			$sql = "SELECT DISTINCT epp.epp_entity_id, neips.ips_site_page as site_page FROM wb_entity_per_page epp
					LEFT JOIN wb_items_per_site ips ON ips.ips_item_id = epp.epp_entity_id AND ips.ips_site_id = 'enwiki'
					LEFT JOIN wb_items_per_site neips ON epp.epp_entity_id = neips.ips_item_id
					WHERE epp.epp_entity_id > $startid AND epp.epp_entity_id <= $endid AND
						epp.epp_entity_type = 'item' AND ips.ips_row_id IS NULL
						AND LOCATE(' ', neips.ips_site_page) > 0 AND LOCATE(':', neips.ips_site_page) = 0";
			$sth = $dbh_wikidata->query($sql);
			$sth->setFetchMode(PDO::FETCH_NUM);
			$ids = array();

			while ($row = $sth->fetch()) {
				fwrite($hndl, "{$row[0]}\t{$row[1]}\n");
			}

			$sth->closeCursor();
			$startid = $endid;
			$endid += 10000;
		}

		$sth->closeCursor();
		fclose($hndl);
	}

	/**
	 * Get the missing enwiki file path
	 *
	 * @return string
	 */
	static function getMissingEnwikiPath()
	{
		return FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'missingenwiki';
	}
}