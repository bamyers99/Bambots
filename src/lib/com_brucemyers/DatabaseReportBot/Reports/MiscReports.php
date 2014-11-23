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
use com_brucemyers\Util\TemplateParamParser;
use com_brucemyers\Util\FileCache;
use PDO;

class MiscReports extends DatabaseReport
{

    public function init(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, $params, PDO $dbh_wikidata)
    {
    	if (empty($params)) return false;

    	$option = $params[0];

    	switch ($option) {
    		case 'ChemSpider':
    			$this->ChemSpider($dbh_wiki, $mediawiki, $dbh_wikidata);
    			return false;
    			break;
    	}

    	return true;
    }

    public function getUsage()
    {
    	return " - Misc reports\n" .
    	"\t\tChemSpider - ChemSpider wikidata links";
    }

	public function getTitle()
	{
		return 'Misc reports';
	}

	public function getIntro()
	{
		return 'Misc reports';
	}

	public function getHeadings()
	{
		return array();
	}

	public function getRows(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, RenderedWiki $renderedwiki, PDO $dbh_wikidata)
	{
		$results = array();

		return $results;
	}

	public function ChemSpider(PDO $dbh_wiki, MediaWiki $mediawiki, PDO $dbh_wikidata)
	{
		$paramnames = array('ChemSpiderID', 'ChemSpiderID1', 'ChemSpiderID2', 'ChemSpiderID3', 'ChemSpiderID4', 'ChemSpiderID5', 'ChemSpiderIDOther');

		$sql = "SELECT page_title FROM templatelinks, page " .
				" WHERE tl_from_namespace = 0 AND tl_namespace = 10 AND tl_title = ? " .
				" AND page_namespace = 0 AND page_id = tl_from";
		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, 'Chembox_Identifiers');
		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_NUM);
		$titles = array();

		while ($row = $sth->fetch()) {
			$titles[] = $row[0];
		}

		$sth->closeCursor();

		sort($titles);

		$tempfile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'ChemSpiderID.csv';
		$ChemSpiderID = fopen($tempfile, 'w');
		fwrite($ChemSpiderID, "ChemSpiderID,WikidataID,\"Title\"\n");
		$tempfile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'ChemSpiderIDs.csv';
		$ChemSpiderIDs = fopen($tempfile, 'w');
		fwrite($ChemSpiderIDs, "ChemSpiderID,ChemSpiderID1,ChemSpiderID2,ChemSpiderID3,ChemSpiderID4,ChemSpiderID5,\"ChemSpiderIDOther\",WikidataID,\"Title\"\n");

		$mediawiki->cachePages($titles);

		foreach ($titles as $page) {
//			echo "$page\n";
			$data = $mediawiki->getPageWithCache($page);

			$parsed_templates = TemplateParamParser::getTemplates($data);

			$page = str_replace('_', ' ', $page);
			$page = ucfirst($page);

			foreach ($parsed_templates as $parsed_template) {
				if ($parsed_template['name'] != 'Chembox Identifiers') continue;
				$params = $parsed_template['params'];
//				print_r($params);
				$paramdata = array();

				foreach ($paramnames as $paramname) {
					if (! empty($params[$paramname])) {
						$paramdata[$paramname] = $params[$paramname];
					}
				}

				if (! empty($paramdata)) {
					$sql = "SELECT ips_item_id FROM wb_items_per_site WHERE ips_site_id = 'enwiki' AND ips_site_page = ?";
					$sth = $dbh_wikidata->prepare($sql);
					$sth->bindValue(1, $page);
					$sth->execute();

					if ($row = $sth->fetch(PDO::FETCH_NUM)) {
						$wikidata_id = $row[0];
						if (isset($paramdata['ChemSpiderID'])) {
							fwrite($ChemSpiderID, "{$paramdata['ChemSpiderID']},Q$wikidata_id,\"$page\"\n");
						}

						foreach ($paramnames as $paramname) {
							if ($paramname == 'ChemSpiderIDOther') fwrite($ChemSpiderIDs, '"');
							$paramvalue = '';
							if (isset($paramdata[$paramname])) $paramvalue = $paramdata[$paramname];
							fwrite($ChemSpiderIDs, $paramvalue);
							if ($paramname == 'ChemSpiderIDOther') fwrite($ChemSpiderIDs, '"');
							fwrite($ChemSpiderIDs, ',');
						}
						fwrite($ChemSpiderIDs, "Q$wikidata_id,\"$page\"\n");
					}

					$sth->closeCursor();
				}
			}
		}

		fclose($ChemSpiderID);
		fclose($ChemSpiderIDs);
	}
}