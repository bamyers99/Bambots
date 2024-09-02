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
use com_brucemyers\MediaWiki\AllPagesLister;
use com_brucemyers\Util\TemplateParamParser;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\WikitableParser;
use com_brucemyers\Util\Curl;
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
    			$this->AgeAnomaly($apis['dbh_wiki'], $apis['dbh_tools']);
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
			    $this->WikidataPropertyCounts($params[1]);
			    return false;
			    break;

    		case 'NRHPID':
    		    $this->NRHPID($apis['dbh_wiki'], $apis['mediawiki']);
    		    return false;
    		    break;

    		case 'NRHPOther':
    		    $this->NRHPOther($apis['dbh_wiki'], $apis['mediawiki']);
    		    return false;
    		    break;

    		case 'WikidataEntitySchemaDirectory':
    		    $obj = new MiscReports\WikidataEntitySchemaDirectory();
    		    $obj->main($params[1]);
    		    return false;
    		    break;

    		case 'dumpChangeTags':
    		    $this->dumpChangeTags($apis['dbh_wikidata']);
    		    return false;
    		    break;

    		case 'WikidataGadgetUsage':
    		    $obj = new MiscReports\WikidataGadgetUsage();
    		    $obj->main($params[1], $apis['dbh_wikidata'], isset($params[2]), $apis['user'], $apis['pass']);
    		    return false;
    		    break;
    		    
    		case 'WikidataDeletedProperties':
    		    $this->WikidataDeletedProperties();
    		    return false;
    		    break;
    		    
    		case 'WikidataExternalIdentifierCompleteness':
    		    $this->WikidataExternalIdentifierCompleteness($params[1]);
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
	 * @param PDO $dbh_tools
	 */
	public function AgeAnomaly(PDO $dbh_wiki, PDO $dbh_tools)
	{
		// Too old/young
		$dbh_tools->exec("DROP TABLE IF EXISTS s51454__wikidata.deadpeople");

		// death cats

		$deathcats = array();
		$sql = "SELECT cat_title FROM enwiki_p.category
			WHERE cat_title REGEXP '^(17|18|19|20|21)[[:digit:]]{2}_deaths$' AND cat_pages > 0";

		$sth = $dbh_wiki->prepare( $sql );
		$sth->setFetchMode( PDO::FETCH_NUM );
		$sth->execute();

		while ( $row = $sth->fetch() ) {
			$deathcats[] = $row[0];
		}

		$sth->closeCursor();
		$sth = null;

		// dead people

		$sql = "CREATE TABLE s51454__wikidata.deadpeople (page_id int unsigned NOT NULL, deathyear int unsigned NOT NULL)";
		$dbh_tools->exec($sql);

		$sql = "SELECT cl_from, LEFT(cl_to, 4) AS year " .
			" FROM enwiki_p.categorylinks WHERE cl_to = ?";
		$sth = $dbh_wiki->prepare($sql);
		$isth = $dbh_tools->prepare( 'INSERT INTO s51454__wikidata.deadpeople VALUES (?,?)' );

		foreach ($deathcats as $deathcat) {
			$sth->setFetchMode( PDO::FETCH_NUM );
			$sth->execute(array($deathcat));
			$dbh_tools->beginTransaction();
			$count = 0;

			while ( $row = $sth->fetch() ) {
				++$count;
				if ($count % 5000 == 0) {
					$dbh_tools->commit();
					$dbh_tools->beginTransaction();
				}

				$isth->execute( array($row[0], $row[1]) );
			}

			$sth->closeCursor();
			$dbh_tools->commit();
		}

		$sql = "ALTER TABLE s51454__wikidata.deadpeople ADD INDEX page_id (page_id)";
		$dbh_tools->exec($sql);

		$offset = 0;
		$limit = 500;
		$badages = array();
		$skip_ids = array(325918,42433680,1302587,12761471,32578395,4140251,21204233,3795672,8628592,5862569,22177303,
			36286513,26501900,15776396,39753940,21308617,32062007,33468662,25575648,12255705,20755930,18048964,24351991,
			9545191,24211762,18928421,38684243,584368,38676683,38659124,38655048,38643903,38632860,38619056,38619050,
			38619045,853159,36509372,36509341,36509226,20396608,44457462,45040011,34169594,44818111,44918849,43574713,
			45206896,44868847,5820690,12255259,5631166,43716686,44210847,3253032,49673186,50729227,12119190,9939398,
			51410097,1214420,30387944,40245763,51435709,53296694,4293277,54749940,54835048,55914231,11085274,51605368,
		    53724687,57988713,59825988,9652293,60401574,60523882,61176484,6242139,63283128,63688875,64502019,29607598,
		    64187214,65375924,65556618,893030,7528496,66773904,64052555,3775042,13684882,2093563,70785885,33337151,
		    33337156,71628171,72002912,65628934,74436172,53350196,68215933,75950612,75975550,2952341,77012421,2880227
		);

		while (1 == 1) {
			$sql = "SELECT page_id, deathyear FROM s51454__wikidata.deadpeople ORDER BY page_id LIMIT $offset, $limit";

			$result = $dbh_tools->query($sql);
			$deads = $result->fetchAll( PDO::FETCH_NUM );
			if (count($deads) == 0) break;

			$pages = array();
			foreach ($deads as $row) {
				$pages[$row[0]] = $row[1];
			}
			$page_ids = implode(',', array_keys($pages));

			$sql = "SELECT cl_from, LEFT(cl_to, 4) AS year " .
					" FROM enwiki_p.categorylinks " .
					" WHERE cl_from IN ($page_ids) AND cl_to REGEXP '^[[:digit:]]{4}_births$'";

			$sth = $dbh_wiki->query($sql);

			while ($row = $sth->fetch(PDO::FETCH_NUM)) {
				$id = (int)$row[0];
				if (in_array($id, $skip_ids) || ! isset($pages[$id])) continue;

				$birth_year = (int)$row[1];
				$death_year = $pages[$id];
				$age = $death_year - $birth_year;

				if ($age > 120 || $age < 1) {
					$badages[$id] = array('birthyear' => $birth_year, 'deathyear' => $death_year, 'age' => $age);
					unset($pages[$id]); // only want reported once
				}
			}

			$offset += $limit;
			if (count($deads) < $limit) break;
		}

		ksort($badages);

		// Living dead
// 		$sql = "SELECT cld1.cl_from FROM categorylinks AS cld1
// 				STRAIGHT_JOIN categorylinks AS cll ON cld1.cl_from = cll.cl_from
// 				WHERE cll.cl_to = 'Living_people' AND cld1.cl_to LIKE '20%\_deaths'";

// 		$sth = $dbh_wiki->query($sql);
// 		$sth->setFetchMode(PDO::FETCH_NUM);

// 		$skip_ids = array(32816757,21213768,32992276,1855946,13981330,7268384,1801200,35801372,44838496);

// 		$livingdead = array();

// 		while ($row = $sth->fetch()) {
// 			$id = (int)$row[0];
// 			if (in_array($id, $skip_ids)) continue;
// 			$livingdead[] = $id;
// 		}

// 		$sth->closeCursor();
// 		sort($livingdead);

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

// 		fwrite($hndl, "<h2>Living Dead</h2>\n");
// 		if (empty($livingdead)) fwrite($hndl, "None\n");
// 		else {
// 			fwrite($hndl, "<table class='wikitable'><thead><tr><th>Article</th></tr></thead><tbody>\n");

// 			foreach ($livingdead as $id) {
// 				$url = "https://en.wikipedia.org/w/index.php?curid=$id";
// 				fwrite($hndl, "<tr><td><a href=\"$url\">$id</a></td></tr>\n");
// 			}

// 			fwrite($hndl, "</tbody></table>\n");
// 		}

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
				'P4348' => array('label' => 'LOTSAWA HOUSE TIBETAN AUTHOR ID', 'exampleid' => 'Q25252', 'people' => false),
				'P4411' => array('label' => 'QUORA USERNAME', 'exampleid' => 'Q23034479', 'people' => false),
				'P4389' => array('label' => 'SCIENCE MUSEUM PEOPLE ID', 'exampleid' => 'Q7186', 'people' => false),
				'P4439' => array('label' => 'MNCARS ARTIST ID', 'exampleid' => 'Q450236', 'people' => false),
				'P4438' => array('label' => 'BFI Film and TV ID', 'exampleid' => 'Q52392', 'people' => false),
				'P4434' => array('label' => 'LESBIOGRAPHIES.COM ID', 'exampleid' => 'Q984375', 'people' => false),
				'P4432' => array('label' => 'AKL ONLINE ARTIST ID', 'exampleid' => 'Q23469792', 'people' => false),
				'P4429' => array('label' => 'PRO14 PLAYER ID', 'exampleid' => 'Q912889', 'people' => false),
				'P4422' => array('label' => 'U.S. SKI AND SNOWBOARD HALL OF FAME ID', 'exampleid' => 'Q444809', 'people' => false),
				'P4418' => array('label' => 'NEW ZEALAND SPORTS HALL OF FAME ID', 'exampleid' => 'Q551581', 'people' => false),
				'P4417' => array('label' => 'RFPL.ORG PLAYER ID', 'exampleid' => 'Q4054222', 'people' => false),
				'P4416' => array('label' => 'PANTHEON DES SPORTS DU QUEBEC ID', 'exampleid' => 'Q616056', 'people' => false),
				'P4415' => array('label' => 'SPORT AUSTRALIA HALL OF FAME INDUCTEE ID', 'exampleid' => 'Q538855', 'people' => false),
				'P4414' => array('label' => 'NEW BRUNSWICK SPORTS HALL OF FAME ATHLETE ID', 'exampleid' => 'Q6377273', 'people' => false),
				'P4413' => array('label' => 'MANITOBA SPORTS HALL OF FAME ATHLETE ID', 'exampleid' => 'Q27063443', 'people' => false),
				'P4470' => array('label' => 'BRITISH BOBSLEIGH & SKELETON ASSOCIATION ID', 'exampleid' => 'Q658596', 'people' => false),
				'P4469' => array('label' => 'IGHOF ATHLETE ID', 'exampleid' => 'Q270400', 'people' => false),
				'P4464' => array('label' => 'RUGBY LEAGUE PROJECT PLAYER ID', 'exampleid' => 'Q127618', 'people' => false),
				'P4463' => array('label' => 'CANADIAN SKI HALL OF FAME INDUCTEE ID', 'exampleid' => 'Q1684883', 'people' => false),
				'P4462' => array('label' => 'CANADIAN BASEBALL HALL OF FAME INDUCTEE ID', 'exampleid' => 'Q1134401', 'people' => false),
				'P4461' => array('label' => 'WORLD GOLF HALL OF FAME PLAYER ID', 'exampleid' => 'Q5044549', 'people' => false),
				'P4454' => array('label' => 'ARGENTINE CHAMBER OF DEPUTIES ID', 'exampleid' => 'Q5668850', 'people' => false),
				'P4453' => array('label' => 'ARGENTINE SENATE MEMBER ID', 'exampleid' => 'Q4678262', 'people' => false),
				'P4451' => array('label' => 'SCORESWAY HANDBALL PERSON ID', 'exampleid' => 'Q12313923', 'people' => false),
				'P4450' => array('label' => 'HAL AUTHOR ID', 'exampleid' => 'Q42308182', 'people' => false),
				'P4531' => array('label' => 'CHINESEPOSTERS ARTIST ID', 'exampleid' => 'Q42430450', 'people' => false),
				'P4574' => array('label' => 'NORWEGIAN HISTORICAL REGISTER OF PERSONS ID', 'exampleid' => 'Q926', 'people' => false),
				'P4598' => array('label' => 'FAPESP RESEARCHER ID', 'exampleid' => 'Q3891586', 'people' => false),
				'P4802' => array('label' => 'BVPB AUTHORITY ID', 'exampleid' => 'Q312747', 'people' => false),
				'P4808' => array('label' => 'ROYAL ACADEMY NEW IDENTIFIER', 'exampleid' => 'Q240526', 'people' => false)
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
	 * @param string $language
	 */
	public function WikidataPropertyCounts($language)
	{
	    static $NOVALUE = 'Q19798647';
	    
	    $type_colors = [
	        'WikibaseItem' => ['color' => '', 'abbrev' => 'WI'],
	        'CommonsMedia' => ['color' => '#faebd7', 'abbrev' => 'CM'], // antiquewhite
	        'ExternalId' => ['color' => '#CDC', 'abbrev' => 'EI'],
	        'String' => ['color' => '#fff0f5', 'abbrev' => 'S'], // lavenderblush
	        'Quantity' => ['color' => 'oldlace', 'abbrev' => 'Q'],
	        'Time' => ['color' => '#ffffe0', 'abbrev' => 'T'], // lightyellow
	        'Monolingualtext' => ['color' => 'mintcream', 'abbrev' => 'MT'],
	        'Url' => ['color' => 'honeydew', 'abbrev' => 'U'],
	        'Math' => ['color' => '#EBC79E', 'abbrev' => 'M'],
	        'GeoShape' => ['color' => '', 'abbrev' => 'GS'],
	        'WikibaseSense' => ['color' => '', 'abbrev' => 'WS'],
	        'WikibaseLexeme' => ['color' => '', 'abbrev' => 'WL'],
	        'MusicalNotation' => ['color' => '', 'abbrev' => 'MN'],
	        'WikibaseProperty' => ['color' => '', 'abbrev' => 'WP'],
	        'TabularData' => ['color' => '', 'abbrev' => 'TD'],
	        'GlobeCoordinate' => ['color' => 'lavender', 'abbrev' => 'GC'],
	        'WikibaseForm' => ['color' => '', 'abbrev' => 'WF'],
	        'EntitySchema' => ['color' => '', 'abbrev' => 'ES']
	    ];
	    
	    $deleted = [
	        'P7478' => true,
	        'P7628' => true,
	        'P7664' => true,
	        'P9127' => true,
	        'P9499' => true,
	        'P9582' => true
	    ];
	    
	    $constraints = [
	        'Q54828448' => ['abbrev' => 'M', 'page' => 'Template:Number_of_main_statements_by_property', 'rdf' => 'http://www.wikidata.org/prop/', 'counts' => []],
	        'Q54828449' => ['abbrev' => 'Q', 'page' => 'Template:Number_of_qualifiers_by_property', 'rdf' => 'http://www.wikidata.org/prop/qualifier/', 'counts' => []],
	        'Q54828450' => ['abbrev' => 'R', 'page' => 'Template:Number_of_references_by_property', 'rdf' => 'http://www.wikidata.org/prop/reference/', 'counts' => []],
	        $NOVALUE => ['abbrev' => 'N', 'page' => null, 'rdf' => 'http://www.wikidata.org/prop/novalue/', 'counts' => []]
	    ];
	    
	    $wdwiki = new WikidataWiki();
	    
	    // Get the property list
	    
	    $query = "SELECT%20(STRAFTER(STR(%3Fprop)%2C%20'P')%20AS%20%3Fid)%20%3FpropLabel%20%3FpropDescription%20(STRAFTER(STR(%3FpropertyType)%2C%20'%23')%20AS%20%3Ftype)%0AWHERE%0A{%0A%20%20%3Fprop%20wikibase%3ApropertyType%20%3FpropertyType%20.%0A%20%20SERVICE%20wikibase%3Alabel%20{%0A%20%20%20%20bd%3AserviceParam%20wikibase%3Alanguage%20\"$language%2Cen\"%0A%20%20}%0A}%0A";
	    
	    $sparql = new WikidataSPARQL();
	    
	    $rows = $sparql->query($query);
	    
	    $props = [];
	    
	    foreach ($rows as $row) {
	        $propid = (int)$row['id']['value'];
	        
	        if (isset($deleted["P$propid"])) continue;
	        
	        $label = isset($row['propLabel']['value']) ? $row['propLabel']['value'] : '';
	        $description = isset($row['propDescription']['value']) ? $row['propDescription']['value'] : '';
	        $datatype = $row['type']['value'];
	        
	        $props[$propid] = ['id' => $propid, 'label' => $label, 'description' => $description, 'datatype' => $datatype, 'constraints' => []];
	    }
	    
	    // Get the scope constraints
	    
	    $query = "SELECT%20(STRAFTER(STR(%3Fprop)%2C%20'P')%20AS%20%3Fid)%20(CONCAT('Q'%2C%20STRAFTER(STR(%3Fconstraint)%2C%20'Q'))%20AS%20%3Fconstraints)%0AWHERE%0A{%0A%20%20%3Fprop%20wikibase%3ApropertyType%20%3FpropertyType%20.%0A%20%20%3Fprop%20p%3AP2302%20[pq%3AP5314%20%3Fconstraint]%20.%0A}%0A";
	    
	    $rows = $sparql->query($query);
	    
	    foreach ($rows as $row) {
	        $propid = (int)$row['id']['value'];
	        $props[$propid]['constraints'][$row['constraints']['value']] = true;
	    }
	    
	    usort($props, function ($a, $b) {
	        if ($a['id'] > $b['id']) return 1;
	        if ($a['id'] < $b['id']) return -1;
	        return 0;
	    });
	        
        // Get the usage counts
        
	    $rdfdata = Curl::getUrlContents('https://query.wikidata.org/sparql');
	    
	    if ($rdfdata === false || Curl::$lastResponseCode != 200) {
	        echo "fallback to prop count pages\n";
	        
	        foreach ($constraints as &$constaint) {
	            if (empty($constaint['page'])) continue;
	            $counts = $wdwiki->getPage($constaint['page']);
	            preg_match_all('!(\d+)\s*=\s*(\d+)!', $counts, $matches, PREG_SET_ORDER);
	            
	            foreach ($matches as $match) {
	                $constaint['counts'][$match[1]] = $match[2];
	            }
	        }
	        
	        unset($constaint);
	    } else {
	        
	        foreach ($constraints as &$constaint) {
	            preg_match_all("!{$constaint['rdf']}P(\d+).*?>(\d+)</triples>!s", $rdfdata, $matches, PREG_SET_ORDER);
	            
	            foreach ($matches as $match) {
	                $constaint['counts'][$match[1]] = $match[2];
	            }
	        }
	        
	        unset($constaint);
	    }
        
        $asof_date = getdate();
        $asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
        $path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'WikidataPropertyCounts.html';
        $hndl = fopen($path, 'wb');
        
        // Header
        fwrite($hndl, "<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
			<title>Wikidata property counts</title>
			<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
			</head><body>
			<div style='display: table; margin: 0 auto;'>
			<h1>Wikidata property counts</h1>
			<h3>As of $asof_date</h3>
			");
        
        // Body
        $typeref = '<ref>Datatype key: CM = CommonsMedia, EI = ExternalId, ES = EntitySchema, GC = GlobeCoordinate, GS = GeoShape, M = Math, MN = MusicalNotation, MT = Monolingualtext, Q = Quantity, S = String, T = Time, TD = TabularData, U = Url, WF = WikibaseForm, WI = WikibaseItem, WL = WikibaseLexeme, WP = WikibaseProperty, WS = WikibaseSense</ref>';
        $wikitext = "{{Languages|en=Wikidata:Database reports/List of properties/all}}\n";
        $wikitext .= "{| class=\"wikitable sortable\"\n|-\n! ID\n! {{I18n|label}}\n! {{I18n|description}}\n! {{I18n|datatype}}$typeref\n! data-sort-type=\"number\" | Counts<ref>Count type key: M = Main value, Q = Qualifier, R = Reference, N = Novalue</ref>\n";
        
        foreach ($props as $prop) {
            $propid = $prop['id'];
            $label = $prop['label'];
            $description = $prop['description'];
            $datatype = $prop['datatype'];
            
            if (isset($type_colors[$datatype]) && ! empty($type_colors[$datatype]['color'])) $color = " style=\"background:{$type_colors[$datatype]['color']}\"";
            else $color = '';
            
            if (isset($type_colors[$datatype])) $datatype = $type_colors[$datatype]['abbrev'];
            
            $counts = [];
            
            foreach ($constraints as $qid => $constraint) {
                if (($qid == $NOVALUE || isset($prop['constraints'][$qid])) && isset($constraint['counts'][$propid]) ) {
                    if ($constraint['counts'][$propid] != 0) $counts[$constraint['abbrev']] = $constraint['counts'][$propid];
                }
            }
            
            arsort($counts, SORT_NUMERIC);
            
            foreach ($counts as $abbrev => &$count) {
                $count = number_format($count, 0) . ' ' . $abbrev;
            }
            
            unset($count);
            
            if (empty($counts)) {
                $counts[] = '0';
            }
            
            $wikitext .= "|-$color\n|[[P:P$propid|P$propid]]||$label||$description||$datatype||";
            
            $wikitext .= implode('<br />', $counts);
            $wikitext .= "\n";
        }
        
        // Footnotes
        $wikitext .= "|}\n\n{{reflist}}";
        
        $wikitext .= "\n\n[[Category:Database reports]]\n[[Category:Properties]]";
        
        fwrite($hndl, '<form><textarea rows="40" cols="100" name="wikitable" id="wikitable">' . htmlspecialchars($wikitext) .
            '</textarea></form>');
        
        // Footer
        fwrite($hndl, '<br />Property count: ' . count($props));
        fwrite($hndl, '<br />Language: ' . $language);
        fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
        fclose($hndl);
	}
	
	/**
	 * Wikidata external identifier completeness
	 *
	 * @param string $language
	 */
	public function WikidataExternalIdentifierCompleteness($language)
	{
	    $ranks = [
	        'http://wikiba.se/ontology#PreferredRank' => 1,
	        'http://wikiba.se/ontology#NormalRank' => 2,
	        'http://wikiba.se/ontology#DeprecatedRank' => 3
	    ];
	    
	    $expectedcompletes = [
	        'Q21873886' => 'always incomplete',
	        'Q21873974' => 'eventually complete',
	        'Q21874050' => 'is complete',
	        'Q47169297' => 'is complete, but new values may exist in the future',
	        'Q60447462' => 'is complete for existing Wikidata items only',
	        'unknown' => 'unknown'
	    ];
	    
	    $wdwiki = new WikidataWiki();
	    
	    // Get the property list
	    
	    $query = <<<EOT
SELECT (STRAFTER(STR(?prop), 'P') AS ?id) ?propLabel ?count ?rank ?pointintime ?expectedcomplete
{
  ?prop rdf:type wikibase:Property .
  ?prop p:P4876 ?stmt .
  ?stmt ps:P4876 ?count .
  ?stmt psv:P4876 ?stmtval .
  ?stmtval rdf:type wikibase:QuantityValue .
  ?stmt wikibase:rank ?rank .
  OPTIONAL {
    ?stmt pq:P585 ?pointintime .
    }
  OPTIONAL {
    ?prop wdt:P2429 ?expectedcomplete .
    }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "$language,en"  }
}
ORDER BY ?prop ?pointintime
EOT;
	    
	    $sparql = new WikidataSPARQL();
	    
	    $rows = $sparql->query(urlencode($query));
	    
	    $proprows = [];
	    
	    foreach ($rows as $row) {
	        $propid = (int)$row['id']['value'];
	        $label = isset($row['propLabel']['value']) ? $row['propLabel']['value'] : '';
	        $sourcecnt = (int)$row['count']['value'];
	        $rank = $ranks[$row['rank']['value']];
	        $pointintime = isset($row['pointintime']['value']) ? $row['pointintime']['value'] : '';
	        $expectedcomplete = isset($row['expectedcomplete']['value']) ? $row['expectedcomplete']['value'] : '';
	        
	        if (! empty($pointintime) && isset($row['pointintime']['datatype']) &&
	            $row['pointintime']['datatype'] == 'http://www.w3.org/2001/XMLSchema#dateTime') {
	            $pointintime = substr($pointintime, 0, 10);
	        } else {
	            $pointintime = '0000-00-00';
	        }
	        
	        if (! empty($expectedcomplete) && preg_match('!http://www.wikidata.org/entity/(Q\d+)!', $expectedcomplete, $matches)) {
	            $expectedcomplete = $matches[1];
	        } else {
	            $expectedcomplete = 'unknown';
	        }
	        
	        if (! isset($proprows[$propid])) $proprows[$propid] = [];
	        $proprows[$propid][] = ['label' => $label, 'sourcecnt' => $sourcecnt, 'rank' => $rank, 'pointintime' => $pointintime,
	            'expectedcomplete' => $expectedcomplete];
	    }
	    
	    $props = [];
	    
	    foreach ($proprows as $propid => $proprow) {
	        // sort by rank, pointintime desc, count desc
	        usort($proprow, function($a, $b) {
	            $arank = $a['rank'];
	            $brank = $b['rank'];
	            
	            if ($arank != $brank) return $arank <=> $brank;
	            
	            $apit = $a['pointintime'];
	            $bpit = $b['pointintime'];
	            
	            if ($apit != $bpit) return $bpit <=> $apit;
	            
	            $acnt = $a['sourcecnt'];
	            $bcnt = $b['sourcecnt'];
	            
	            return $bcnt <=> $acnt;
	        });
	        
	        $props[$propid] = ['label' => $proprow[0]['label'], 'sourcecnt' => $proprow[0]['sourcecnt'],
	            'expectedcomplete' => $proprow[0]['expectedcomplete'], 'pointintime' => $proprow[0]['pointintime']];
	    }
	    
	    ksort($props);
	        
        // Get use counts
	    $rdfdata = Curl::getUrlContents('https://query.wikidata.org/sparql');
	    
	    if ($rdfdata === false || Curl::$lastResponseCode != 200) {
	        echo "fallback to prop count pages\n";
	        
    	    $usecounts = $wdwiki->getPage('Template:Number_of_main_statements_by_property');
            preg_match_all('!(\d+)\s*=\s*(\d+)!', $usecounts, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $propid = $match[1];
                
                if (isset($props[$propid])) {
                    $props[$propid]['usecnt'] = $match[2];
                }
            }
	    } else {
	        preg_match_all("!http://www.wikidata.org/prop/P(\d+).*?>(\d+)</triples>!s", $rdfdata, $matches, PREG_SET_ORDER);
	        
	        foreach ($matches as $match) {
	            $propid = $match[1];
	            
	            if (isset($props[$propid])) {
	                $props[$propid]['usecnt'] = $match[2];
	            }
	        }
	        
	    }
        
        // Get use dates
        
        $url = "https://bambots.brucemyers.com/NavelGazer.php?action=getPropMeta";
        $propmeta = Curl::getUrlContents($url);
        $pmlines = explode("\n", $propmeta);
        
        foreach ($pmlines as $pmline) {
            if (empty($pmline)) continue;
            
            list($propid, $firstuse, $lastuse) = explode(',', $pmline);
            
            if (isset($props[$propid])) {
                $props[$propid]['firstuse'] = $firstuse;
                $props[$propid]['lastuse'] = $lastuse;
            }
        }
        
        date_default_timezone_set('UTC');
        
        $current_date = date('Y-m-d H:i');
        
        $asof_date = getdate();
        $asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
        $path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'WikidataExternalIdentifierCompleteness.html';
        $hndl = fopen($path, 'wb');
        
        // Header
        fwrite($hndl, "<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
			<title>Wikidata external identifier completeness</title>
			<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
			</head><body>
			<div style='display: table; margin: 0 auto;'>
			<h1>Wikidata external identifier completeness</h1>
			<h3>As of $asof_date</h3>
			");
        
        // Body
        $wikitext = "External identifier completeness.\n\n";
        
        $wikitext .= "Updated: <onlyinclude>$current_date (UTC)</onlyinclude>\n";
        $wikitext .= "{| class=\"wikitable sortable\"\n|-\n! ID\n! {{I18n|label}}\n! Source count\n! Wikidata count\n! % complete\n! Source date\n!Expected completeness\n! Last use\n";
        
        foreach ($props as $propid => $prop) {
            $label = $prop['label'];
            $sourcecnt = $prop['sourcecnt'];
            $pointintime = $prop['pointintime'];
            $usecnt = isset($prop['usecnt']) ? $prop['usecnt'] : 0;
            $expectedcomplete = $expectedcompletes[$prop['expectedcomplete']];
            $firstuse = isset($prop['firstuse']) ? $prop['firstuse'] : '';
            $lastuse = isset($prop['lastuse']) ? $prop['lastuse'] : '';
            
            $pctcomplete = round(($usecnt / $sourcecnt) * 100, 3);
            $decimalpos = strpos($pctcomplete, '.');
            
            if ($decimalpos !== false) {
                list($wholepct, $fractionpct) = explode('.', $pctcomplete);
                $paddingcnt = 3 - strlen($fractionpct);
            } else {
                $paddingcnt = 3;
                $pctcomplete .= '&thinsp;';
            }
            
            for (; $paddingcnt > 0; --$paddingcnt) $pctcomplete .= '&numsp;';
            
            $sourcecntfmt = number_format($sourcecnt, 0, '', '&thinsp;');
            $usecntfmt = number_format($usecnt, 0, '', '&thinsp;');
            
            $wikitext .= "|-\n|[[P:P$propid|P$propid]]||$label||data-sort-value=\"$sourcecnt\" style=\"text-align: right;\" |$sourcecntfmt||data-sort-value=\"$usecnt\" style=\"text-align: right;\" |$usecntfmt||style=\"text-align: right;\" |$pctcomplete||$pointintime||$expectedcomplete||$lastuse\n";
        }
        
        // Footnotes
        $wikitext .= "|}\n\nNote: Numbers are formatted with the ISO recommended international thousands separator 'thin space'.";
        
        $wikitext .= "\n\n[[Category:Database reports]]";
        
        fwrite($hndl, '<form><textarea rows="40" cols="100" name="wikitable" id="wikitable">' . htmlspecialchars($wikitext) .
            '</textarea></form>');
        
        // Footer
        fwrite($hndl, '<br />Property count: ' . count($props));
        fwrite($hndl, '<br />Language: ' . $language);
        fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
        fclose($hndl);
	}
	
	/**
	 * Wikidata deleted properties
	 *
	 * @param string $language
	 */
	public function WikidataDeletedProperties()
	{
	    $suppress = [67, 104, 145, 152, 153, 174, 182, 226, 250, 252, 253, 254, 255, 257, 259, 290, 292, 293, 294, 362, 363, 365,
	        383, 384, 385, 386, 445, 446, 581, 848, 1009, 1105, 1328, 1426, 1452, 1719, 1720, 1742, 2420, 2609, 2932, 3278, 4499,
	        7082, 8288, 9582, 11142, 12497, 3736, 12540
	    ];
	    
	    $wdwiki = new WikidataWiki();
	    
	    // Get the current properties
	    
	    $curprops = [];
	    
	    $lister = new AllPagesLister($wdwiki, '120');
	    
	    while (($pages = $lister->getNextBatch()) !== false) {
	        foreach ($pages as $page) {
	            $id = (int)substr($page['title'], 10);
	            $curprops[] = $id;
	        }
	    }
	    
	    echo 'Current props = ' . count($curprops) . "\n";
	    
	    $allprops = range(6, max($curprops));
	    $delpropids = array_diff($allprops, $curprops);
	    
	    echo 'Deleted props = ' . count($delpropids) . "\n";
	    
	    // Get the configuration
	    $delprops = [];
	    $config = $wdwiki->getPage('Wikidata:Database reports/Deleted properties');
	    //$config = file_get_contents('/home/bruce/Downloads/DelPropConfig.txt');
	    
	    $configtable = WikitableParser::getTables($config)[0];
	    
	    foreach ($configtable['rows'] as $row) {
	        preg_match('!P\d+!', $row[0], $matches);
	        $pid = $matches[0];
	        $label = $row[1];
	        $description = $row[2];
	        $deldate = $row[3];
	        $count = $row[4];
	        $proposalpage = $row[5];
	        $deletepage = $row[6];
	        
	        $delprops[$pid] = ['id' => $pid, 'proposalpage' => $proposalpage, 'deletepage' => $deletepage, 'label' => $label,
	            'description' => $description, 'deldate' => $deldate, 'count' => $count
	        ];
	    }
	    
	    // See if can find property proposals
	    $newconfig = [];
	    
	    foreach ($delpropids as $pid) {
	        if (isset($delprops["P$pid"])) continue;
	        if (in_array($pid, $suppress)) continue;
	        
	        // See if any were added
	        $url = "https://bambots.brucemyers.com/NavelGazer.php?action=getCount&property=P$pid";
	        $count = Curl::getUrlContents($url);
	        if ($count == 0) continue;
	        
	        $title = "Property:P$pid";
	        
	        // Get the deletion date
	        
	        $ret = $wdwiki->getList('logevents', ['letitle' => $title, 'letype' => 'delete']);
	        $logevent = reset($ret['query']['logevents']);
	        if ($logevent === false) continue;
	        
	        $deldate = substr($logevent['timestamp'], 0, 10);
	        
	        echo "Processing P$pid\n";
	        $ret = $wdwiki->getList('backlinks', ['bltitle' => $title, 'blnamespace' => '4', 'bllimit' => 'max']); // Wikidata namespace
	        
	        $proposalpages = [];
	        $deletepage = '';
	        $proposalpage = '';
	        $label = '';
	        $description = '';
	        
	        if (isset($ret['query']['backlinks'])) {
    	        foreach ($ret['query']['backlinks'] as $backlink) {
    	            $bltitle = $backlink['title'];
    	            if (strpos($bltitle, 'Wikidata:Property proposal/') === 0 && strpos($bltitle, 'Archive') === false) $proposalpages[] = $bltitle;
    	            if ($bltitle == "Wikidata:Properties for deletion/P$pid") $deletepage = $bltitle;
    	        }
	        }
	        
	        // Find the right proposal
	        
	        foreach ($proposalpages as $pp) {
	            $page = $wdwiki->getPageWithCache($pp);
	            $templates = TemplateParamParser::getTemplates($page);
	            
	            foreach ($templates as $template) {
	                if ($template['name'] == 'Property proposal') {
	                    if ($template['params']['status'] == $pid) {
	                        $proposalpage = $pp;
	                        $label = substr($pp, 27);
	                        
	                        $templates = TemplateParamParser::getTemplates($template['params']['description']);
	                        foreach ($templates as $template) {
	                            if ($template['name'] == 'TranslateThis') {
	                                if (isset($template['params']['en'])) $description = $template['params']['en'];
	                                break;
	                            }
	                        }
	                        
	                        break 2;
	                    }
	                }
	            }
	        }
	        
	        $newconfig[] = ['id' => $pid, 'proposalpage' => $proposalpage, 'deletepage' => $deletepage, 'label' => $label,
	            'description' => $description, 'deldate' => $deldate, 'count' => $count
	        ];
	    }
        
        $asof_date = getdate();
        $asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
        $path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'WikidataDeletedProperties.html';
        $hndl = fopen($path, 'wb');
        
        // Header
        fwrite($hndl, "<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
			<title>Wikidata deleted properties</title>
			<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
			</head><body>
			<div style='display: table; margin: 0 auto;'>
			<h1>Wikidata deleted properties</h1>
			<h3>As of $asof_date</h3>
			");
        
        // Body
        date_default_timezone_set('UTC');
        
        $current_date = date('Y-m-d H:i');
        
        $wikitext = "Deleted properties that were used in items.\n\n";
        
        $wikitext .= "Updated: <onlyinclude>$current_date (UTC)</onlyinclude>\n";
        $wikitext .= "{| class=\"wikitable sortable\"\n|-\n! ID\n! {{I18n|label}}\n! {{I18n|description}}\n! Deletion Date\n! Uses\n! Creation Proposal\n! Deletion\n";
        
        foreach ($delprops as $prop) {
            $propid = $prop['id'];
            $label = $prop['label'];
            $description = $prop['description'];
            $proposalpage = $prop['proposalpage'];
            $deletepage = $prop['deletepage'];
            $deletedate = $prop['deldate'];
            $count = $prop['count'];
            
            $wikitext .= "|-\n|[[Property:$propid|$propid]] || $label || $description || $deletedate || $count || $proposalpage || $deletepage";

            $wikitext .= "\n";
        }
        
        // Footnotes
        
        $wikitext .= "|}";
        
        $wikitext .= "\n\n[[Category:Database reports]]\n[[Category:Properties]]";
        
        fwrite($hndl, '<textarea rows="40" cols="100" name="wikitable" id="wikitable">' . htmlspecialchars($wikitext) .
            '</textarea>');
        
        $wikitext = '';
        
        foreach ($newconfig as $prop) {
            $pid = $prop['id'];
            $label = $prop['label'];
            $description = $prop['description'];
            $proposalpage = $prop['proposalpage'];
            $deletepage = $prop['deletepage'];
            $deletedate = $prop['deldate'];
            $count = $prop['count'];
            
            $wikitext .= "|-\n|[[Property:P$pid|P$pid]] || $label || $description || $deletedate || $count || ";
            if (! empty($proposalpage)) $wikitext .= "[[$proposalpage|Proposal]]";
            $wikitext .= " || ";
            if (! empty($deletepage)) $wikitext .= "[[$deletepage|Deletion]]";
            $wikitext .= "\n";
        }
        
        fwrite($hndl, '<textarea rows="40" cols="100" name="newwikitable" id="newwikitable">' . htmlspecialchars($wikitext) .
            '</textarea></form>');
        
        
        // Footer
        fwrite($hndl, '<br />Deleted count: ' . (count($delprops) + count($newconfig)));
        fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
        fclose($hndl);
	}
	    
	/**
	 * Get a list of template parameters
	 *
	 * @param PDO $dbh_wiki
	 * @param MediaWiki $mediawiki
	 * @param PDO $dbh_wikidata
	 */
	public function TemplateParams(PDO $dbh_wiki, MediaWiki $mediawiki, PDO $dbh_wikidata)
	{
	    $templates = array(
	        'Infobox UK school',
	        'Infobox English public school',
	        'Infobox GB school',
	        'Infobox School GB',
	        'Infobox Scotland school',
	        'Infobox UKschool'
	    );

	    $templist = array();
	    foreach ($templates as $template) {
	        $templist[] = "'" . str_replace(' ', '_', $template) . "'";
	    }

	    $templist = implode(',', $templist);

	    $sql = "SELECT DISTINCT page_title FROM templatelinks, page " .
	   	    " WHERE tl_from_namespace = 0 AND tl_namespace = 10 AND tl_title IN ($templist) " .
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
	        }
	    }
	    $asof_date = getdate();
	    $asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
	    $path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'AgeAnomaly.html';
	    $hndl = fopen($path, 'wb');

	    // Header
	    fwrite($hndl, "<!DOCTYPE html>
		<html><head>
		<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
		<title>Template Parameters</title>
		<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
		</head><body>
		<div style='display: table; margin: 0 auto;'>
		<h1>Template Parameters</h1>
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
	 * Get a list of NRHP ids and corresponding wikidata item number
	 *
	 * @param PDO $dbh_wiki
	 * @param MediaWiki $mediawiki
	 */
	public function NRHPID(PDO $dbh_wiki, MediaWiki $mediawiki)
	{
	    $templates = [
	        'Infobox NRHP' => 'refnum',
	        'Infobox nrhp' => 'refnum'
	    ];

	    $sql = "SELECT DISTINCT page_title, pp_value FROM templatelinks, page, page_props " .
	   	    " WHERE tl_from_namespace = 0 AND tl_namespace = 10 AND tl_title IN ('Infobox_NRHP', 'Infobox_nrhp') " .
	   	    " AND page_namespace = 0 AND page_id = tl_from AND page_id = pp_page AND pp_propname = 'wikibase_item' ";
	    $sth = $dbh_wiki->prepare($sql);
	    $sth->execute();
	    $sth->setFetchMode(PDO::FETCH_NUM);
	    $titles = [];

	    while ($row = $sth->fetch()) {
	        $titles[$row[0]] = $row[1];
	    }

	    $sth->closeCursor();

	    ksort($titles);

	    $tempfile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'NRHPID.tsv';
	    $hndl = fopen($tempfile, 'w');
	    fwrite($hndl, "NRHPID\tWikidataID\tTitle\n");

	    $mediawiki->cachePages(array_keys($titles));

	    foreach ($titles as $page => $wikidata_id) {
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

	            if (! empty($params[$paramname]) && preg_match('!^\s*(\d+)!', $params[$paramname], $matches)) {
	                $value = $matches[1];

	                fwrite($hndl, "$value\t$wikidata_id\t$page\n");
	                break;
	            }
	        }
	    }

	    fclose($hndl);
	}

	/**
	 * Get a list of NRHP other ids and corresponding wikidata item number
	 *
	 * @param PDO $dbh_wiki
	 * @param MediaWiki $mediawiki
	 */
	public function NRHPOther(PDO $dbh_wiki, MediaWiki $mediawiki)
	{
	    $templates = [
	        'Infobox NRHP' => ['name' => 'designated_other1', 'id' => 'designated_other1_number'],
	        'Infobox nrhp' => ['name' => 'designated_other1', 'id' => 'designated_other1_number']
	    ];

	    $sql = "SELECT DISTINCT page_title, pp_value FROM templatelinks, page, page_props " .
	   	    " WHERE tl_from_namespace = 0 AND tl_namespace = 10 AND tl_title IN ('Infobox_NRHP', 'Infobox_nrhp') " .
	   	    " AND page_namespace = 0 AND page_id = tl_from AND page_id = pp_page AND pp_propname = 'wikibase_item' ";
	    $sth = $dbh_wiki->prepare($sql);
	    $sth->execute();
	    $sth->setFetchMode(PDO::FETCH_NUM);
	    $titles = [];

	    while ($row = $sth->fetch()) {
	        $titles[$row[0]] = $row[1];
	    }

	    $sth->closeCursor();

	    ksort($titles);

	    $tempfile = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . 'NRHPOtherID.tsv';
	    $hndl = fopen($tempfile, 'w');
	    fwrite($hndl, "NRHPOtherName\tNRHPOtherID\tWikidataID\tTitle\n");

	    $mediawiki->cachePages(array_keys($titles));

	    foreach ($titles as $page => $wikidata_id) {
	        //			echo "$page\n";
	        $data = $mediawiki->getPageWithCache($page);

	        $parsed_templates = TemplateParamParser::getTemplates($data);

	        $page = str_replace('_', ' ', $page);
	        $page = ucfirst($page);

	        foreach ($parsed_templates as $parsed_template) {
	            if (! isset($templates[$parsed_template['name']])) continue;
	            $paramdata = $templates[$parsed_template['name']];
	            $paramname = $paramdata['name'];
	            $paramid = $paramdata['id'];
	            $params = $parsed_template['params'];
	            //				print_r($params);

	            if (! empty($params[$paramname]) && ! empty($params[$paramid])) {
	                fwrite($hndl, "{$params[$paramname]}\t{$params[$paramid]}\t$wikidata_id\t$page\n");
	                break;
	            }
	        }
	    }

	    fclose($hndl);
	}

	/**
	 * Dump change tags
	 */
	public function dumpChangeTags(PDO $dbh_wikidata)
	{
	    $outputDir = Config::get(DatabaseReportBot::OUTPUTDIR);
	    $outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
	    $outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
	    $outputDir .= DIRECTORY_SEPARATOR;
	    $hndl = fopen($outputDir . 'changetags.tsv', 'w');
	    $wdwiki = new WikidataWiki();

	    $sql = 'SELECT ctd_id, ctd_name, ctd_count FROM change_tag_def';
	    $result = $dbh_wikidata->query($sql);
	    $result->setFetchMode(PDO::FETCH_ASSOC);

	    $tags = [];

	    foreach ($result as $tag) {
	        $tagname = $tag['ctd_name'];
	        if (strstr($tagname, 'OAuth CID:') === false && strstr($tagname, 'openrefine') === false
	            && strstr($tagname, 'WikibaseJS-cli') === false && strstr($tagname, 'WikibaseJS-edit') === false
	            && strstr($tagname, 'InfoboxExport gadget') === false && strstr($tagname, 'wwwyzzerdd') === false
	            && strstr($tagname, 'wikidata-for-firefox') === false) continue;
	        $tags[$tagname] = ['id' => $tag['ctd_id'], 'count' => $tag['ctd_count']];
	    }

	    $tagmetas = $wdwiki->getList('tags', ['tgprop' => 'description|displayname', 'tglimit' => 'max']);

	    foreach ($tagmetas['query']['tags'] as $tagmeta) {
	        $tagname = $tagmeta['name'];
	        $tagdisplayname = isset($tagmeta['displayname']) ? preg_replace('!\s!', ' ', $tagmeta['displayname']) : '';
	        $tagdesc = isset($tagmeta['description']) ? preg_replace('!\s!', ' ', $tagmeta['description']) : '';

	        if (isset($tags[$tagname])) {
	            if (preg_match('!>OpenRefine</a>(.+)!', $tagdisplayname, $matches)) { // OpenRefine
	                $tagdisplayname = "OpenRefine{$matches[1]}";
	            } elseif (preg_match('!>([^<]+?)<!', $tagdisplayname, $matches)) { // OAuth or anchor
	                $tagdisplayname = $matches[1];
	            }
	            
                fwrite($hndl, "{$tags[$tagname]['id']}\t$tagname\t$tagdisplayname\t$tagdesc\t{$tags[$tagname]['count']}\n");
	        }
	    }

	    // Add the no tool tagdef
	    fwrite($hndl, "0\tNo tool\tNo tool\t\t101\n");

	    fclose($hndl);
	}
}