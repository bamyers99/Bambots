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

use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\RenderedWiki\RenderedWiki;
use com_brucemyers\Util\TemplateParamParser;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use PDO;

class MiscReports extends DatabaseReport
{

    public function init($apis, $params)
    {
    	if (empty($params)) return false;

    	$option = $params[0];

    	switch ($option) {
    		case 'ChemSpider':
    			$this->ChemSpider($apis['dbh_wiki'], $apis['mediawiki'], $apis['dbh_wikidata']);
    			return false;
    			break;

    		case 'Journalisted':
    			$this->Journalisted($apis['dbh_wiki'], $apis['mediawiki'], $apis['dbh_wikidata']);
    			return false;
    			break;

    		case 'WikiProjectList':
    			$this->WikiProjectList($apis['dbh_wiki']);
    			return false;
    			break;
    	}

    	return true;
    }

    public function getUsage()
    {
    	return " - Misc reports\n" .
    	"\t\tChemSpider - ChemSpider wikidata links\n";
    	"\t\tJournalisted - Journalisted wikidata links";
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

	public function getRows($apis)
	{
		$results = array();

		return $results;
	}

	/**
	 * Get a list of ChemSpider ids and corresponding wikidata item number
	 *
	 * @param PDO $dbh_wiki
	 * @param MediaWiki $mediawiki
	 * @param PDO $dbh_wikidata
	 */
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

	/**
	 * Get a list of Journalisted ids and corresponding wikidata item number
	 *
	 * @param PDO $dbh_wiki
	 * @param MediaWiki $mediawiki
	 * @param PDO $dbh_wikidata
	 */
	public function Journalisted(PDO $dbh_wiki, MediaWiki $mediawiki, PDO $dbh_wikidata)
	{
		$templates = array(
		    'Journalisted' => '1',
			'UK MP links' => 'journalisted',
			'MPLinksUK' => 'journalisted',
			'UK Peer links' => 'journalisted'
		);

		$sql = "SELECT DISTINCT page_title FROM templatelinks, page " .
				" WHERE tl_from_namespace = 0 AND tl_namespace = 10 AND tl_title IN ('Journalisted', 'UK_MP_links', 'UK_Peer_links', 'MPLinksUK') " .
				" AND page_namespace = 0 AND page_id = tl_from";
		$sth = $dbh_wiki->prepare($sql);
		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_NUM);
		$titles = array();

		while ($row = $sth->fetch()) {
			$titles[] = $row[0];
		}

		$sth->closeCursor();

		sort($titles);

		$tempfile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'JournalistedID.csv';
		$hndl = fopen($tempfile, 'w');
		fwrite($hndl, "JournalistedID,WikidataID,\"Title\"\n");

		$mediawiki->cachePages($titles);

		foreach ($titles as $page) {
//			echo "$page\n";
			$data = $mediawiki->getPageWithCache($page);

			$parsed_templates = TemplateParamParser::getTemplates($data);

			$page = str_replace('_', ' ', $page);
			$page = ucfirst($page);

			foreach ($parsed_templates as $parsed_template) {
				if (! isset($templates[$parsed_template['name']])) continue;
				$paramname = $templates[$parsed_template['name']];
				$params = $parsed_template['params'];
//				print_r($params);

				if (! empty($params[$paramname])) {
					$sql = "SELECT ips_item_id FROM wb_items_per_site WHERE ips_site_id = 'enwiki' AND ips_site_page = ?";
					$sth = $dbh_wikidata->prepare($sql);
					$sth->bindValue(1, $page);
					$sth->execute();

					$wikidata_id = 'None';
					if ($row = $sth->fetch(PDO::FETCH_NUM)) {
						$wikidata_id = "Q{$row[0]}";
					}

					$sth->closeCursor();

					fwrite($hndl, "{$params[$paramname]},$wikidata_id,\"$page\"\n");
					break;
				}
			}
		}

		fclose($hndl);
	}

	/**
	 * Generate a list of WikiProjects with status and creation date
	 *
	 * @param PDO $dbh_wiki
	 */
	public function WikiProjectList(PDO $dbh_wiki)
	{
		$catstatuses = array(
			'Active_WikiProjects' => 'Active',
			'Defunct_WikiProjects' => 'Defunct',
			'Inactive_WikiProjects' => 'Inactive',
			'Inactive_anime_and_manga-related_WikiProjects' => 'Inactive',
			'Inactive_education-related_WikiProjects' => 'Inactive',
			'Inactive_game-related_WikiProjects' => 'Inactive',
			'Inactive_geographical_WikiProjects' => 'Inactive',
			'Inactive_music-related_WikiProjects' => 'Inactive',
			'Inactive_sports-related_WikiProjects' => 'Inactive',
			'Inactive_TV-related_WikiProjects' => 'Inactive',
			'Semi-active_WikiProjects' => 'Semi-active'
		);

		$statustotals = array();
		$projectcnt = 0;


		// Retrieve with prefix WikiProject_

		$sql = "SELECT page_id, page_title, GROUP_CONCAT(cl_to SEPARATOR '#') FROM page " .
			" LEFT JOIN categorylinks ON cl_from = page_id " .
			" WHERE page_namespace = 4 AND page_title LIKE 'WikiProject\_%' AND page_title NOT LIKE '%/%' " .
			" AND page_is_redirect = 0 " .
			" GROUP BY page_title " .
			" ORDER BY page_title";

		$creationsql = "SELECT rev_timestamp FROM revision WHERE rev_page = ? ORDER BY rev_timestamp LIMIT 1";
		$createsth = $dbh_wiki->prepare($creationsql);

		$sth = $dbh_wiki->prepare($sql);
		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_NUM);
		$projects = array();

		while ($row = $sth->fetch()) {
			$status = 'Uncategorized';
			$pageid = $row[0];
			$pagename = str_replace('_', ' ', $row[1]);

			$cats = $row[2];
			if (empty($cats)) $cats = ''; // Handles nulls
			$cats = explode('#', $cats);

			foreach ($cats as $cat) {
				if (isset($catstatuses[$cat])) {
					$status = $catstatuses[$cat];
					break;
				}
			}

			// Retrieve the creation date
			$createsth->bindValue(1, $pageid);
			$createsth->execute();
			$creationdate = 'Unknown';
			if ($creationrow = $createsth->fetch(PDO::FETCH_NUM)) {
				$creationdate = $creationrow[0];
				$creationdate = substr($creationdate, 0, 4) . '-' . substr($creationdate, 4, 2) . '-' . substr($creationdate, 6, 2);
			}

			$createsth->closeCursor();

			$projects[$pagename] = array('status' => $status, 'created' => $creationdate);

			if (! isset($statustotals[$status])) $statustotals[$status] = 0;
			++$statustotals[$status];
			++$projectcnt;
		}

		$sth->closeCursor();

		// Retrieve by category

		$sql = "SELECT page_id, page_title FROM page, categorylinks " .
			" WHERE page_namespace = 4 AND page_title NOT LIKE '%/%' " .
			" AND cl_from = page_id AND cl_to = ? " .
			" AND page_is_redirect = 0 ";
		$sth = $dbh_wiki->prepare($sql);

		foreach ($catstatuses as $catname => $status) {
			$sth->bindValue(1, $catname);
			$sth->execute();
			$sth->setFetchMode(PDO::FETCH_NUM);

			while ($row = $sth->fetch()) {
				$pageid = $row[0];
				$pagename = str_replace('_', ' ', $row[1]);
				if (isset($projects[$pagename])) continue;

				// Retrieve the creation date
				$createsth->bindValue(1, $pageid);
				$createsth->execute();
				$creationdate = 'Unknown';
				if ($creationrow = $createsth->fetch(PDO::FETCH_NUM)) {
					$creationdate = $creationrow[0];
					$creationdate = substr($creationdate, 0, 4) . '-' . substr($creationdate, 4, 2) . '-' . substr($creationdate, 6, 2);
				}

				$createsth->closeCursor();

				$projects[$pagename] = array('status' => $status, 'created' => $creationdate);

				if (! isset($statustotals[$status])) $statustotals[$status] = 0;
				++$statustotals[$status];
				++$projectcnt;
			}

			$sth->closeCursor();
		}

		// Generate the report

		$totalline = array();
		ksort($statustotals);
		ksort($projects);

		foreach ($statustotals as $status => $total) {
			$totalline[] = "$status: $total";
		}

		$totalline = implode(' ', $totalline);

        $asof_date = getdate();
        $asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
		$path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'WikiProjectList.html';
		$hndl = fopen($path, 'wb');

		// Header
		fwrite($hndl, "<!DOCTYPE html>
		<html><head>
		<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
		<title>WikiProject List</title>
		<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
		<script type='text/javascript' src='../js/jquery-2.1.1.min.js'></script>
		<script type='text/javascript' src='../js/jquery.tablesorter.min.js'></script>
		</head><body>
		<script type='text/javascript'>
			$(document).ready(function()
			    {
			        $('#myTable').tablesorter({});
			    }
			);
		</script>
		<div style='display: table; margin: 0 auto;'>
		<h2>WikiProject List as of $asof_date</h2>
		<p>Project count: $projectcnt</p>
		<p>Status counts - $totalline</p>
		<table id='myTable' class='wikitable'><thead><tr><th>Project</th><th>Status</th><th>Created</th></tr></thead><tbody>
		");

		// Body
		foreach ($projects as $title => $project) {
			$status = $project['status'];
			$created = $project['created'];
			$projurl = 'https://en.wikipedia.org/wiki/Wikipedia:' . urlencode(str_replace(' ', '_', $title));

			fwrite($hndl, "<tr><td><a href=\"$projurl\">" . htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a></td><td>$status</td>
			<td>$created</td></tr>\n");
		}

		// Footer
		fwrite($hndl, "</tbody></table></div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
		fclose($hndl);

	}
}