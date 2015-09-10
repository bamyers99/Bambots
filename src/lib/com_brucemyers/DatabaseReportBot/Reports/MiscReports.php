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

    		case 'AgeAnomaly':
    			$this->AgeAnomaly($apis['dbh_wiki']);
    			return false;
    			break;

    		case 'MoreCategories':
    			$this->MoreCategories($apis['dbh_wiki']);
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
    	"\t\tWikiProjectList - WikiProject list\n";
    	"\t\tAgeAnomaly - Age anomaly report";
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
				45206896,44868847,5820690,12255259);

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

		$skip_ids = array(32816757,21213768,32992276);

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
}