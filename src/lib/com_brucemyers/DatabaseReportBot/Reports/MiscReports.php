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

namespace com_brucemyers\DatabaseReportBot\Reports;

use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\MediaWiki\WikidataSPARQL;
use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\Util\TemplateParamParser;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use MediaWiki\Sanitizer;
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

    		case 'AgeAnomaly':
    			$this->AgeAnomaly($apis['dbh_wiki']);
    			return false;
    			break;

    		case 'MoreCategories':
    			$this->MoreCategories($apis['dbh_wiki']);
    			return false;
    			break;

    		case 'WikidataPeopleAuthCtrl':
    			$this->WikidataPeopleAuthCtrl($apis['dbh_wiki']);
    			return false;
    			break;

    		case 'WikidataPropertyCounts':
    			$this->WikidataPropertyCounts($apis['dbh_wiki'], $apis['mediawiki'], $apis['dbh_wikidata']);
    			return false;
    			break;
    	}

    	return true;
    }

    public function getUsage()
    {
    	return " - Misc reports\n" .
    	"\t\tChemSpider - ChemSpider wikidata links\n" .
    	"\t\tJournalisted - Journalisted wikidata links\n" .
    	"\t\tWikiProjectList - WikiProject list\n" .
    	"\t\tAgeAnomaly - Age anomaly report\n" .
    	"\t\tMoreCategories - People needing a notability category\n" .
    	"\t\tWikidataPeopleAuthCtrl - Wikidata people authority control properties";
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

	/**
	 * Look for age anomolies
	 *
	 * @param PDO $dbh_wiki
	 */
	public function AgeAnomaly(PDO $dbh_wiki)
	{
		// Too old/young
		$dbh_wiki->exec("DROP TABLE IF EXISTS s51454__wikidata.deathcats");
		$dbh_wiki->exec("DROP TABLE IF EXISTS s51454__wikidata.deadpeople");

		$sql = "CREATE TABLE s51454__wikidata.deathcats SELECT cat_title FROM enwiki_p.category
			WHERE cat_title REGEXP '^(17|18|19|20|21)[[:digit:]]{2}_deaths$' AND cat_pages > 0";
		$dbh_wiki->exec($sql);

		$sql = "ALTER TABLE s51454__wikidata.deathcats ADD UNIQUE INDEX cat_title (cat_title)";
		$dbh_wiki->exec($sql);

		$sql = "CREATE TABLE s51454__wikidata.deadpeople SELECT cldeath.cl_from AS page_id, LEFT(cldeath.cl_to, 4) AS year
			FROM s51454__wikidata.deathcats deathcats
			JOIN enwiki_p.categorylinks cldeath ON cldeath.cl_to = deathcats.cat_title";
		$dbh_wiki->exec($sql);

		$sql = "ALTER TABLE s51454__wikidata.deadpeople ADD INDEX page_id (page_id)";
		$dbh_wiki->exec($sql);

		$sql = "SELECT DISTINCT deadpeople.page_id AS page_id,
					CONVERT(LEFT(clbirth.cl_to, 4) USING utf8) as birthyear,
					CONVERT(deadpeople.year USING utf8) as deathyear,
					deadpeople.year - LEFT(clbirth.cl_to, 4) as age
				FROM s51454__wikidata.deadpeople deadpeople
				JOIN enwiki_p.categorylinks clbirth ON clbirth.cl_from = deadpeople.page_id
				WHERE clbirth.cl_to REGEXP '^[[:digit:]]{4}_births$'
				HAVING (age > 120 OR age < 1)";

		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);

		$skip_ids = array(325918,42433680,1302587,12761471,32578395,4140251,21204233,3795672,8628592,5862569,22177303,
				36286513,26501900,15776396,39753940,21308617,32062007,33468662,25575648,12255705,20755930,18048964,24351991,
				9545191,24211762,18928421,38684243,584368,38676683,38659124,38655048,38643903,38632860,38619056,38619050,
				38619045,853159,36509372,36509341,36509226,20396608,44457462,45040011,34169594,44818111,44918849,43574713,
				45206896,44868847,5820690,12255259,5631166,43716686,44210847,3253032,49673186,50729227,12119190,9939398,
				51410097,1214420,30387944,40245763,51435709,53296694,4293277,54749940,54835048
		);

		$badages = array();

		while ($row = $sth->fetch()) {
			$id = (int)$row['page_id'];
			if (in_array($id, $skip_ids)) continue;
			$badages[$id] = $row;
			$byear = $row['birthyear'];
			$dyear = $row['deathyear'];
			$age = $row['age'];
		}

		$sth->closeCursor();
		ksort($badages);

		// Living dead
		$sql = "SELECT cld1.cl_from FROM categorylinks AS cld1
				STRAIGHT_JOIN categorylinks AS cll ON cld1.cl_from = cll.cl_from
				WHERE cll.cl_to = 'Living_people' AND cld1.cl_to LIKE '20%\_deaths'";

		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);

		$skip_ids = array(32816757,21213768,32992276,1855946,13981330,7268384,1801200,35801372,44838496);

		$livingdead = array();

		while ($row = $sth->fetch()) {
			$id = (int)$row[0];
			if (in_array($id, $skip_ids)) continue;
			$livingdead[] = $id;
		}

		$sth->closeCursor();
		sort($livingdead);

		$asof_date = getdate();
		$asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
		$path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'AgeAnomaly.html';
		$hndl = fopen($path, 'wb');

		// Header
		fwrite($hndl, "<!DOCTYPE html>
		<html><head>
		<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
		<title>Age Anomalies</title>
		<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
		</head><body>
		<div style='display: table; margin: 0 auto;'>
		<h1>Age Anomalies</h1>
		<h3>As of $asof_date</h3>
		");

		// Body
		fwrite($hndl, "<h2>Bad Ages</h2>\n");
		if (empty($badages)) fwrite($hndl, "None\n");
		else {
		fwrite($hndl, "<table class='wikitable'><thead><tr><th>Article</th><th>Birth</th><th>Death</th><th>Age</th></tr></thead><tbody>\n");

				foreach ($badages as $id => $badage) {
				$byear = $badage['birthyear'];
						$dyear = $badage['deathyear'];
								$age = $badage['age'];
								$url = "https://en.wikipedia.org/w/index.php?curid=$id";
								fwrite($hndl, "<tr><td><a href=\"$url\">$id</a></td><td>$byear</td><td>$dyear</td><td>$age</td></tr>\n");
				}

				fwrite($hndl, "</tbody></table>\n");
		}

		fwrite($hndl, "<h2>Living Dead</h2>\n");
		if (empty($livingdead)) fwrite($hndl, "None\n");
		else {
			fwrite($hndl, "<table class='wikitable'><thead><tr><th>Article</th></tr></thead><tbody>\n");

			foreach ($livingdead as $id) {
				$url = "https://en.wikipedia.org/w/index.php?curid=$id";
				fwrite($hndl, "<tr><td><a href=\"$url\">$id</a></td></tr>\n");
			}

			fwrite($hndl, "</tbody></table>\n");
		}

		// Footer
		fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
		fclose($hndl);
	}

	/**
	 * Look for more categories needed
	 *
	 * @param PDO $dbh_wiki
	 */
	public function MoreCategories(PDO $dbh_wiki)
	{
		// Get the people page ids
		$dbh_wiki->exec("DROP TABLE IF EXISTS s51454__wikidata.people");
		$dbh_wiki->exec("DROP TABLE IF EXISTS s51454__wikidata.people2");

		$sql = "CREATE TABLE s51454__wikidata.people (page_id int unsigned NOT NULL, PRIMARY KEY (page_id))";
		$dbh_wiki->exec($sql);
		$sql = "CREATE TABLE s51454__wikidata.people2 (page_id int unsigned NOT NULL, PRIMARY KEY (page_id))";
		$dbh_wiki->exec($sql);

		$people_cats = array('Living_people', 'Possibly_living_people', 'Year_of_birth_missing_(living_people)',
			'Year_of_birth_missing', 'Year_of_birth_unknown', 'Year_of_death_missing', 'Year_of_death_unknown');

		foreach ($people_cats as $cat) {
			$sql = "INSERT IGNORE INTO s51454__wikidata.people SELECT cl_from FROM enwiki_p.categorylinks WHERE cl_to = '$cat'";
			$dbh_wiki->exec($sql);
		}

		$people_regexes = array('^(17|18|19|20)[[:digit:]]{2}s?_deaths$', '^(17|18|19|20)[[:digit:]]{2}s?_births$');

		foreach ($people_regexes as $regex) {
			$dbh_wiki->exec("DROP TABLE IF EXISTS s51454__wikidata.deathcats");

			$sql = "CREATE TABLE s51454__wikidata.deathcats SELECT cat_title FROM enwiki_p.category
				WHERE cat_title REGEXP '$regex' AND cat_pages > 0";
			$dbh_wiki->exec($sql);

			$sql = "ALTER TABLE s51454__wikidata.deathcats ADD UNIQUE INDEX cat_title (cat_title)";
			$dbh_wiki->exec($sql);

			$sql = "INSERT IGNORE INTO s51454__wikidata.people SELECT cldeath.cl_from
				FROM s51454__wikidata.deathcats deathcats
				JOIN enwiki_p.categorylinks cldeath ON cldeath.cl_to = deathcats.cat_title";
			$dbh_wiki->exec($sql);
		}

		// Retrieve the peoples visible categories 10000 at a time
		$offset = 0;
		$needmorecats = array();

		while (true) {
			$dbh_wiki->exec("TRUNCATE s51454__wikidata.people2");

			$sql = "INSERT INTO s51454__wikidata.people2 SELECT page_id FROM s51454__wikidata.people ORDER BY page_id LIMIT $offset, 10000";
			$dbh_wiki->exec($sql);

			$sql = "SELECT cl_from, GROUP_CONCAT(cl_to SEPARATOR ' ') AS cl_to FROM s51454__wikidata.people2 people
				LEFT JOIN enwiki_p.categorylinks ON cl_from = people.page_id
				LEFT JOIN enwiki_p.page ON page.page_title = cl_to AND page.page_namespace = 14
				LEFT JOIN enwiki_p.page_props ON pp_page = page.page_id AND pp_propname = 'hiddencat'
				WHERE pp_propname IS NULL
	            GROUP BY cl_from";

			$sth = $dbh_wiki->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$rowcnt = 0;

			while ($row = $sth->fetch()) {
				$page_id = $row['cl_from'];
				$cats = explode(' ', $row['cl_to']);

				foreach ($cats as $key => $cat) {
					$wscat = str_replace('_', ' ', $cat); // _ is considered a regex word character

					if (in_array($cat, $people_cats)) unset($cats[$key]);
					elseif (preg_match('! stubs$!', $wscat)) unset($cats[$key]);
					elseif (preg_match('!\balumni\b!i', $wscat)) unset($cats[$key]);
					elseif (preg_match('!\bpeople from !i', $wscat)) unset($cats[$key]);
					elseif (preg_match('!^\d{4}s? deaths$!', $wscat)) unset($cats[$key]);
					elseif (preg_match('!^\d{4}s? births$!', $wscat)) unset($cats[$key]);
				}

				if (empty($cats)) $needmorecats[] = $page_id;
				++$rowcnt;
			}

			$sth->closeCursor();
			$sth = null;

			if (! $rowcnt) break;

			$offset += 10000;
		}

		if (empty($needmorecats)) return;

		$already_templated = array('Improve_categories', 'Uncategorized', 'Uncategorized_stub');

		// Retrieve the page title and templates
		$sql = "SELECT page_title, GROUP_CONCAT(tl_title SEPARATOR ' ') AS tl_title
			FROM enwiki_p.page
			LEFT JOIN enwiki_p.templatelinks ON page_id = tl_from
			WHERE page_id IN (" . implode(',', $needmorecats) . ")
			GROUP BY page_id
			ORDER BY page_title";

		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$page_titles = array();

		while ($row = $sth->fetch()) {
			$page_title = str_replace('_', ' ', $row['page_title']);
			if (strpos($page_title, 'Deaths in') === 0) continue;

			$templates = $row['tl_title'];
			if (is_null($templates)) $templates = '';
			$templates = explode(' ', $templates);

			foreach ($templates as $template) {
				if (in_array($template, $already_templated)) continue 2;
			}

			$page_titles[] = $page_title;
		}

		$sth->closeCursor();
		$sth = null;

		$asof_date = getdate();
		$asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
		$path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'MoreCategories.html';
		$hndl = fopen($path, 'wb');

		// Header
		fwrite($hndl, "<!DOCTYPE html>
		<html><head>
		<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
		<title>More Categories</title>
		<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
		</head><body>
		<div style='display: table; margin: 0 auto;'>
		<h1>More Categories</h1>
		<h3>As of $asof_date</h3>
		");

		// Body
		if (empty($page_titles)) fwrite($hndl, "None\n");
		else {
			fwrite($hndl, "<table class='wikitable'><thead><tr><th>Article</th></tr></thead><tbody>\n");

			foreach ($page_titles as $page_title) {
				$url = "https://en.wikipedia.org/wiki/" . urlencode(str_replace(' ', '_', $page_title));
				$page_title = htmlentities($page_title, ENT_COMPAT, 'UTF-8');
				fwrite($hndl, "<tr><td><a href=\"$url\">$page_title</a></td></tr>\n");
			}

			fwrite($hndl, "</tbody></table>\n");
		}

		// Footer
		fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
		fclose($hndl);
	}

	/**
	 * Wikidata people authority control properties
	 *
	 * @param PDO $dbh_wiki
	 */
	public function WikidataPeopleAuthCtrl(PDO $dbh_wiki)
	{
		$wdwiki = new WikidataWiki();

		// Get the auth control props with instance of 'Wikidata property for authority control for people'

		$query = 'SELECT%20%3Fprop%20%3FpropLabel%20%3FpropDescription%20%28SAMPLE%28%3Fexample_target%29%20AS%20%3Fexample_target%29%0AWHERE%0A{%0A%20%20%3Fprop%20wdt%3AP31%20wd%3AQ19595382%20.%0A%20%20OPTIONAL%20{%3Fprop%20wdt%3AP1855%20%3Fexample_target}%20.%0A%20%20SERVICE%20wikibase%3Alabel%20{%0A%20%20%20%20bd%3AserviceParam%20wikibase%3Alanguage%20%22en%22%0A%20%20}%0A}%0AGROUP%20BY%20%3Fprop%20%3FpropLabel%20%3FpropDescription%0AORDER%20BY%20UCASE%28%3FpropLabel%29';

		$sparql = new WikidataSPARQL();

		$rows = $sparql->query($query);

		$props = array(
				'P213' => array('label' => 'ISNI', 'exampleid' => 'Q21930050', 'people' => false),
				'P269' => array('label' => 'SUDOC AUTHORITIES', 'exampleid' => 'Q535', 'people' => false),
				'P906' => array('label' => 'SELIBR', 'exampleid' => 'Q762', 'people' => false),
				'P349' => array('label' => 'NDL ID', 'exampleid' => 'Q307', 'people' => false),
				'P244' => array('label' => 'LCAUTH ID', 'exampleid' => 'Q5582', 'people' => false),
				'P227' => array('label' => 'GND ID', 'exampleid' => 'Q212190', 'people' => false),
				'P396' => array('label' => 'SBN ID', 'exampleid' => 'Q307', 'people' => false),
				'P950' => array('label' => 'BNE ID', 'exampleid' => 'Q79822', 'people' => false),
				'P214' => array('label' => 'VIAF ID', 'exampleid' => 'Q447070', 'people' => false),
				'P268' => array('label' => 'BNF ID', 'exampleid' => 'Q7836', 'people' => false),
				'P549' => array('label' => 'MATHEMATICS GENEALOGY PROJECT ID', 'exampleid' => 'Q7604', 'people' => false),
				'P949' => array('label' => 'NATIONAL LIBRARY OF ISRAEL ID', 'exampleid' => 'Q42', 'people' => false),
				'P409' => array('label' => 'NLA (AUSTRALIA) ID', 'exampleid' => 'Q436699', 'people' => false),
				'P691' => array('label' => 'NKCR AUT ID', 'exampleid' => 'Q57434', 'people' => false),
				'P1005' => array('label' => 'PTBNP ID', 'exampleid' => 'Q134461', 'people' => false),
				'P1017' => array('label' => 'BAV ID', 'exampleid' => 'Q551550', 'people' => false),
				'P646' => array('label' => 'FREEBASE ID', 'exampleid' => 'Q307', 'people' => false),
				'P1273' => array('label' => 'CANTIC-ID', 'exampleid' => 'Q561147', 'people' => false),
				'P1207' => array('label' => 'NUKAT (WARSAWU) AUTHORITIES', 'exampleid' => 'Q42552', 'people' => false),
				'P1309' => array('label' => 'EGAXA ID', 'exampleid' => 'Q307', 'people' => false),
				'P1422' => array('label' => 'SANDRART.NET PERSON ID', 'exampleid' => 'Q312304', 'people' => false),
				'P866' => array('label' => 'PERLENTAUCHER ID', 'exampleid' => 'Q307', 'people' => false),
				'P1670' => array('label' => 'LAC ID', 'exampleid' => 'Q307', 'people' => false),
				'P1368' => array('label' => 'LNB ID', 'exampleid' => 'Q615419', 'people' => false),
				'P1695' => array('label' => 'NLP ID', 'exampleid' => 'Q12904', 'people' => false),
				'P1375' => array('label' => 'NSK ID', 'exampleid' => 'Q336571', 'people' => false),
				'P723' => array('label' => 'DBNL ID', 'exampleid' => 'Q2359791', 'people' => false),
				'P1741' => array('label' => 'GTAA ID', 'exampleid' => 'Q523644', 'people' => false),
				'P648' => array('label' => 'OPEN LIBRARY ID', 'exampleid' => 'Q5685', 'people' => false),
				'P1871' => array('label' => 'CERL ID', 'exampleid' => 'Q307', 'people' => false),
				'P2163' => array('label' => 'FAST-ID', 'exampleid' => 'Q307', 'people' => false),
				'P865' => array('label' => 'BMLO', 'exampleid' => 'Q11933906', 'people' => false),
				'P998' => array('label' => 'DMOZ ID', 'exampleid' => 'Q255', 'people' => false),
				'P1248' => array('label' => 'KULTURNAV-ID', 'exampleid' => 'Q959698', 'people' => false),
				'P1902' => array('label' => 'SPOTIFY ARTIST ID', 'exampleid' => 'Q2757867', 'people' => false),
				'P1430' => array('label' => 'OPENPLAQUES SUBJECT ID', 'exampleid' => 'Q207', 'people' => false),
				'P1284' => array('label' => 'MUNZINGER IBA', 'exampleid' => 'Q1684721', 'people' => false),
				'P1839' => array('label' => 'US FEDERAL ELECTION COMMISSION ID', 'exampleid' => 'Q516515', 'people' => false),
				'P1296' => array('label' => 'GRAN ENCICLOPEDIA CATALANA ID', 'exampleid' => 'Q207', 'people' => false),
				'P1749' => array('label' => 'PARLEMENT & POLITIEK ID', 'exampleid' => 'Q57792', 'people' => false),
				'P1048' => array('label' => 'NCL ID', 'exampleid' => 'Q228889', 'people' => false),
				'P2390' => array('label' => 'BALLOTPEDIA ID', 'exampleid' => 'Q76', 'people' => false),
				'P1417' => array('label' => 'ENCYCLOPEDIA BRITANNICA ONLINE ID', 'exampleid' => 'Q7374', 'people' => false),
				'P1003' => array('label' => 'NLR (ROMANIA) ID', 'exampleid' => 'Q77177', 'people' => false),
				'P651' => array('label' => 'BIOGRAFISH PORTAAL NUMBER', 'exampleid' => 'Q2929721', 'people' => false),
				'P902' => array('label' => 'HDS ID', 'exampleid' => 'Q435456', 'people' => false),
				'P1286' => array('label' => 'MUNZINGER POP ID', 'exampleid' => 'Q272203', 'people' => false),
				'P1280' => array('label' => 'CONOR ID', 'exampleid' => 'Q1031', 'people' => false),
				'P863' => array('label' => 'INPHO ID', 'exampleid' => 'Q219368', 'people' => false),
				'P1565' => array('label' => 'ENCICLOPEDIA DE LA LITERATURA EN MEXICO ID', 'exampleid' => 'Q8962435', 'people' => false),
				'P2267' => array('label' => 'POLITIFACT PERSONALITY ID', 'exampleid' => 'Q76', 'people' => false),
				'P1225' => array('label' => 'NATIONAL ARCHIVES IDENTIFIER', 'exampleid' => 'Q1387214', 'people' => false),
				'P3338' => array('label' => 'ENCYCLOPEDIA OF SURFING ID', 'exampleid' => 'Q3190749', 'people' => false),
				'P1615' => array('label' => 'CLARA-ID', 'exampleid' => 'Q6781930', 'people' => false),
				'P951' => array('label' => 'NSZL ID', 'exampleid' => 'Q763890', 'people' => false),
				'P3368' => array('label' => 'PRABOOK ID', 'exampleid' => 'Q4495505', 'people' => false),
				'P3385' => array('label' => 'JAPAN SUMO ASSOCIATION ID', 'exampleid' => 'Q448054', 'people' => false),
				'P3478' => array('label' => 'SONGKICK ARTIST ID', 'exampleid' => 'Q26695', 'people' => false),
				'P3476' => array('label' => 'PSA WORLDTOUR ID', 'exampleid' => 'Q2935075', 'people' => false),
				'P3475' => array('label' => 'SANU MEMBER ID', 'exampleid' => 'Q9036', 'people' => false),
				'P3468' => array('label' => 'NATIONAL INVENTORS HALL OF FAME ID', 'exampleid' => 'Q4273363', 'people' => false),
				'P3539' => array('label' => 'NFL.COM ID', 'exampleid' => 'Q24810030', 'people' => false),
				'P3538' => array('label' => 'FUSSBALLDATEN.DE ID', 'exampleid' => 'Q1081201', 'people' => false),
				'P3536' => array('label' => 'EUROLEAGUE.NET ID', 'exampleid' => 'Q3849644', 'people' => false),
				'P3533' => array('label' => 'DRAFTEXPRESS.COM ID', 'exampleid' => 'Q3849644', 'people' => false),
				'P3532' => array('label' => 'DATABASEFOOTBALL.COM ID', 'exampleid' => 'Q5672702', 'people' => false),
				'P3531' => array('label' => 'AZBILLIARDS ID', 'exampleid' => 'Q1162866', 'people' => false),
				'P3527' => array('label' => 'EUROBASKET.COM ID', 'exampleid' => 'Q4721816', 'people' => false),
				'P3526' => array('label' => 'WISDENINDIA.COM ID', 'exampleid' => 'Q6080718', 'people' => false),
				'P3525' => array('label' => 'ACB.COM ID', 'exampleid' => 'Q3849644', 'people' => false),
				'P3506' => array('label' => 'LUDING DESIGNER ID', 'exampleid' => 'Q61088', 'people' => false),
				'P3505' => array('label' => 'BOARDGAMEGEEK DESIGNER ID', 'exampleid' => 'Q61088', 'people' => false),
				'P3502' => array('label' => 'AMEBLO USERNAME', 'exampleid' => 'Q50025', 'people' => false),
				'P3603' => array('label' => 'MINNEAPOLIS INSTITUTE OF ART CONSTITUENT ID', 'exampleid' => 'Q1383354', 'people' => false),
				'P3751' => array('label' => 'SHOFTIM BEISRAEL JUDGE ID', 'exampleid' => 'Q18097436', 'people' => false),
				'P3817' => array('label' => 'FI WARSAMPO PERSON ID', 'exampleid' => 'Q2632168', 'people' => false),
				'P3788' => array('label' => 'BNA AUTHOR ID', 'exampleid' => 'Q832085', 'people' => false),
				'P3845' => array('label' => 'TV GUIDE PERSON ID', 'exampleid' => 'Q106126', 'people' => false),
				'P3857' => array('label' => 'CINENACIONAL.COM PERSON ID', 'exampleid' => 'Q4888833', 'people' => false),
				'P3847' => array('label' => 'OPEN LIBRARY SUBJECT ID', 'exampleid' => 'Q152384', 'people' => false),
				'P3955' => array('label' => 'NLL PLAYER ID', 'exampleid' => 'Q6374142', 'people' => false),
				'P3953' => array('label' => 'ALPG GOLFER ID', 'exampleid' => 'Q25936013', 'people' => false),
				'P3949' => array('label' => 'JUWRA.COM ID', 'exampleid' => 'Q7855', 'people' => false),
				'P3948' => array('label' => 'MLL PLAYER ID', 'exampleid' => 'Q24259938', 'people' => false),
				'P3946' => array('label' => 'DIRECTORIO GRIERSON ID', 'exampleid' => 'Q233985', 'people' => false),
				'P3943' => array('label' => 'TUMBLR ID', 'exampleid' => 'Q3013276', 'people' => false),
				'P3942' => array('label' => 'BMX-RESULTS.COM RIDER ID', 'exampleid' => 'Q3183914', 'people' => false),
				'P3965' => array('label' => 'BRIDGEMAN ARTIST ID', 'exampleid' => 'Q1282413', 'people' => false),
				'P3960' => array('label' => 'BASE BIOGRAPHIQUE AUTOR ID', 'exampleid' => 'Q3438834', 'people' => false),
				'P3365' => array('label' => 'ENCICLOPEDIA TRECCANI ID', 'exampleid' => 'Q7317', 'people' => false),
				'P3995' => array('label' => 'FILMWEB.PL ID', 'exampleid' => 'Q68537', 'people' => false),
				'P3988' => array('label' => 'NATIONAL LIBRARY BOARD SINGAPORE ID', 'exampleid' => 'Q5052793', 'people' => false),
				'P3987' => array('label' => 'SHARE CATALOGUE AUTHOR ID', 'exampleid' => 'Q2755854', 'people' => false),
				'P4008' => array('label' => 'EARLY AVIATORS PEOPLE ID', 'exampleid' => 'Q436102', 'people' => false),
				'P4040' => array('label' => 'ROCK.COM.AR ARTIST ID', 'exampleid' => 'Q957627', 'people' => false),
				'P4034' => array('label' => 'SHIRONET ARTIST ID', 'exampleid' => 'Q258991', 'people' => false),
				'P4114' => array('label' => 'ADK MEMBER ID', 'exampleid' => 'Q25973', 'people' => false),
				'P4112' => array('label' => 'DANSKFILMOGTV PERSON', 'exampleid' => 'Q232404', 'people' => false),
				'P4104' => array('label' => 'CARNEGIE HALL AGENT ID', 'exampleid' => 'Q131861', 'people' => false),
				'P4130' => array('label' => 'USHMM PERSON ID', 'exampleid' => 'Q7336', 'people' => false),
				'P4169' => array('label' => 'YCBA AGENT ID', 'exampleid' => 'Q18826502', 'people' => false),
				'P4180' => array('label' => 'GUJLIT PERSON ID', 'exampleid' => 'Q2724598', 'people' => false),
				'P4186' => array('label' => 'AUSTRALIAN WOMEN\'S REGISTER ID', 'exampleid' => 'Q5271387', 'people' => false),
				'P4208' => array('label' => 'BILLBOARD ARTIST ID', 'exampleid' => 'Q2067434', 'people' => false),
				'P4206' => array('label' => 'FLEMISH ORGANIZATION FOR IMMOVABLE HERITAGE PERSON ID', 'exampleid' => 'Q154083', 'people' => false),
				'P4198' => array('label' => 'GOOGLE PLAY MUSIC ARTIST ID', 'exampleid' => 'Q4276848', 'people' => false),
				'P4193' => array('label' => 'FAMILYPEDIA PERSON ID', 'exampleid' => 'Q5335826', 'people' => false),
				'P4228' => array('label' => 'ENCYCLOPEDIA OF AUSTRALIAN SCIENCE ID', 'exampleid' => 'Q38734568', 'people' => false),
				'P4287' => array('label' => 'RIIGIKOGU ID', 'exampleid' => 'Q3785077', 'people' => false),
				'P4357' => array('label' => 'MUSIKVERKET PERSON ID', 'exampleid' => 'Q4945718', 'people' => false),
				'P4351' => array('label' => 'CRAVO ALBIN ARTIST ID', 'exampleid' => 'Q200131', 'people' => false),
				'P4349' => array('label' => 'LOTSAWA HOUSE INDIAN AUTHOR ID', 'exampleid' => 'Q320150', 'people' => false),
				'P4348' => array('label' => 'LOTSAWA HOUSE TIBETAN AUTHOR ID', 'exampleid' => 'Q25252', 'people' => false)
		);

		foreach ($rows as $row) {
			$label = strtoupper($row['propLabel']['value']);
			$propid = pathinfo($row['prop']['value'], PATHINFO_BASENAME);
			if (isset($row['example_target'])) $exampleid = pathinfo($row['example_target']['value'], PATHINFO_BASENAME);
			elseif (isset($props[$propid]['exampleid'])) $exampleid = $props[$propid]['exampleid'];
			else $exampleid = '';

			$props[$propid] = array('label' => $label, 'exampleid' => $exampleid);
		}

		uasort($props, function ($a, $b) {
			return strcmp($a['label'], $b['label']);
		});

			// Get the properties and example items
			$items = array();

			foreach ($props as $propid => $prop) {
				$items[] = "Property:$propid";
				if (! empty($prop['exampleid'])) $items[] = $prop['exampleid'];
			}

			$items = $wdwiki->getItemsWithCache($items);

			$propertyitems = array();
			$exampleitems = array();

			foreach ($items as $item) {
				$id = $item->getId();
				if (empty($id)) continue;
				if ($id[0] == 'Q') $exampleitems[$id] = $item;
				else $propertyitems[$id] = $item;
			}

			// Get the usage counts
			$counts = $wdwiki->getPageWithCache('Template:Property_uses');
			preg_match_all('!(\d+)\s*=\s*(\d+)!', $counts, $matches, PREG_SET_ORDER);
			$counts = array();
			foreach ($matches as $match) {
				$counts['P' . $match[1]] = $match[2];
			}

			$asof_date = getdate();
			$asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
			$path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'WikidataPeopleAuthCtrl.html';
			$hndl = fopen($path, 'wb');

			// Header
			fwrite($hndl, "<!DOCTYPE html>
					<html><head>
					<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
					<title>Wikidata people authority control properties</title>
					<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
					</head><body>
					<div style='display: table; margin: 0 auto;'>
					<h1>Wikidata people authority control properties</h1>
					<h3>As of $asof_date</h3>
					");

			// Body

			$wikitext = "<noinclude><languages/></noinclude>\n\n{{anchor|Authority control}}\n<translate>\n==Authority control== <!--T:1-->\n</translate>\n";
			$wikitext .= "<table class='wikitable sortable'><tr><th scope='col'>{{int:wm-license-artwork-title}}</th><th scope='col'>ID</th><th scope='col'>{{int:wikibase-propertypage-datatype}}</th><th scope='col'>{{int:listfiles_description}}</th><th scope='col'>{{int:apisandbox-examples}}</th><th scope='col'>&nbsp;Count&nbsp;</th></tr>\n";
			$nonpeople = array();

			foreach ($props as $propid => $prop) {
				$intid = substr($propid, 1);
				$example_subject = $prop['exampleid'];
				$example_object = '';
				if (! isset($propertyitems[$propid])) continue;
				$property = $propertyitems[$propid];
				$datatype = $property->getDatatype();

				if (! empty($example_subject)) {
					$examples = $property->getStatementsOfType('P1855');
					$occurrence = false;

					foreach ($examples as $key => $example) {
						if ($example == $example_subject) {
							$occurrence = $key;
							break;
						}
					}

					if ($occurrence === false) {
						if (! empty($examples)) $occurrence = 0;
					}

					if ($occurrence === false) {
						$propvalues = $exampleitems[$example_subject]->getStatementsOfType($propid);
						if (! empty($propvalues)) $example_object = $propvalues[0];
					} else {
						$qualifiers = $property->getStatementQualifiers('P1855', $occurrence);
						if (isset($qualifiers[$propid])) $example_object = $qualifiers[$propid][0];
					}

					if (! empty($example_object)) {
						$urlformatter = $property->getStatementsOfType('P1630');
						if (! empty($urlformatter)) {
							$example_object = '[' . Sanitizer::escapeWikitextInUrl(str_replace('$1', $example_object, $urlformatter[0])) . ' ' .
									Sanitizer::wfEscapeWikiText($example_object) . ']';
						}
					}
				}

				if (isset($prop['people'])) {
					$label = str_replace(' ', '&nbsp;', $property->getLabelDescription('label', 'en'));
					$nonpeople[] = "[[Property:$propid|$label&nbsp;($propid)]]";
				}

				if (isset($counts[$propid])) $count = $counts[$propid];
				else $count = 0;

				$wikitext .= "<tr><td>{{label|$propid}}</td><td>[[Property:$propid|$propid]]</td><td>$datatype</td><td>{{autodescription|$propid}}</td><td>$example_object</td>";
				$wikitext .= "<td style='text-align:right' data-sort-value='$count'>" . number_format($count, 0, '', '&thinsp;');
				$wikitext .= "</td></tr>\n";
			}

			$nonpeople = implode(', ', $nonpeople);

			$wikitext .= <<<END
</table>

{{anchor|Query}}
<translate>

== Query == <!--T:2-->
The following [https://query.wikidata.org/ SPARQL query] was used to generate this list of properties with {{Statement||31|19595382}}:
</translate>

{{SPARQL|query=SELECT ?prop ?propLabel ?propDescription (SAMPLE(?example_target) AS ?example_target)
WHERE
{
  ?prop wdt:P31 wd:Q19595382 .
  OPTIONAL {?prop wdt:P1855 ?example_target} .
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en"
  }
}
GROUP BY ?prop ?propLabel ?propDescription
ORDER BY UCASE(?propLabel)
}}

<translate>
<!--T:3-->
The following additional properties are also included:
</translate>

:$nonpeople
END;

			fwrite($hndl, '<form><textarea rows="40" cols="100" name="wikitable" id="wikitable">' . htmlspecialchars($wikitext) .
					'</textarea></form>');

			// Footer
			fwrite($hndl, '<br />Property count: ' . count($props));
			fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
			fclose($hndl);
	}

	/**
	 * Wikidata property counts
	 *
	 * @param PDO $dbh_wiki
	 */
	public function WikidataPropertyCounts(PDO $dbh_wiki, MediaWiki $mediawiki, PDO $dbh_wikidata)
	{
		$wdwiki = new WikidataWiki();

		// Get the usage counts
		$counts = $wdwiki->getPageWithCache('Template:Property_uses');
		preg_match_all('!(\d+)\s*=\s*(\d+)!', $counts, $matches, PREG_SET_ORDER);
		$counts = array();
		foreach ($matches as $match) {
			$counts[$match[1]] = array('count' => $match[2], 'label' => "P{$match[1]}", 'desc' => '');
		}

		// Get the labels and descriptions
		$ids = implode(',', array_keys($counts));

		$sql = "SELECT term_entity_id, term_type, term_text " .
			" FROM wb_terms " .
			" WHERE term_language = 'en' AND term_entity_id IN ($ids) AND term_entity_type = 'property' ";

		$sth = $dbh_wikidata->prepare($sql);
		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$term_type = $row['term_type'];
			$entity_id = $row['term_entity_id'];

			if ($term_type == 'label') $counts[$entity_id]['label'] = $row['term_text'];
			elseif ($term_type == 'description') $counts[$entity_id]['desc'] = $row['term_text'];
		}

		$asof_date = getdate();
		$asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
		$path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'WikidataPropertyCounts.html';
		$hndl = fopen($path, 'wb');

		// Header
		fwrite($hndl, "<!DOCTYPE html>
		<html><head>
		<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
		<title>Wikidata property usage counts</title>
		<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
		<script type='text/javascript' src='../js/jquery-2.1.1.min.js'></script>
		<script type='text/javascript' src='../js/jquery.tablesorter.min.js'></script>
		<script type='text/javascript'>
			$(document).ready(function()
			    {
			        $('#myTable').tablesorter({});
			    }
			);
		</script>
		</head><body>
		<div style='display: table; margin: 0 auto;'>
		<h1>Wikidata property usage counts</h1>
		<h3>As of $asof_date</h3>
		");

		// Body

		fwrite($hndl, "<table id='myTable' class='wikitable'><thead><tr><th>Title</th><th>ID</th><th>Description</th><th>Count</th></tr></thead><tbody>\n");

		foreach ($counts as $id => $data) {
			$url = "https://www.wikidata.org/wiki/Property:P$id";
			$label = htmlspecialchars($data['label']);
			$desc = htmlspecialchars($data['desc']);
			$count = $data['count'];

			fwrite($hndl, "<tr><td>$label</td><td data-sort-value='$id'><a href=\"$url\">P$id</a></td><td>$desc</td>" .
				"<td style='text-align:right' data-sort-value='$count'>" . number_format($count, 0, '', '&thinsp;') . "</td></tr>\n");
		}

		fwrite($hndl, "</tbody></table>\n");

		// Footer
		fwrite($hndl, '<br />Property count: ' . count($counts));
		fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
		fclose($hndl);
	}
}