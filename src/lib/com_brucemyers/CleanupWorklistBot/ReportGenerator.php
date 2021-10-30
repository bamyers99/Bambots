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

namespace com_brucemyers\CleanupWorklistBot;

use PDO;
use SplFixedArray;
use com_brucemyers\Util\CSVString;
use com_brucemyers\MediaWiki\ResultWriter;
use pChart\pChart;
use pChart\pData;

class ReportGenerator
{
	public static $MONTHS = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
		7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
	var $outputdir;
	var $urlpath;
	var $tools_host;
	var $asof_date;
	var $resultWriter;
	var $categories;
	var $catobj;
	var $user;
	var $pass;

	// Key atoms to save memory
	const KEY_IMP = 0;
	const KEY_CLS = 1;
	const KEY_ISSUES = 2;
	const KEY_TITLE = 3;
	const KEY_MTH = 4;
	const KEY_YR = 5;
	const KEY_CLSSORT = 6;
	const KEY_IMPSORT = 7;
	const KEY_EARLIEST = 8;
	const KEY_EARLIESTSORT = 9;
	const KEY_CATS = 10;
	const KEY_ICOUNT = 11;
	const KEY_BLP = 12;

	const MAX_PAGE_SIZE = 5000000;

	function __construct($tools_host, $outputdir, $urlpath, $asof_date, ResultWriter $resultWriter, $catobj, $user, $pass)
	{
		$this->tools_host = $tools_host;
		$this->outputdir = $outputdir;
		$this->urlpath = $urlpath;
		$this->asof_date = $asof_date;
        $this->resultWriter = $resultWriter;
        $this->catobj = $catobj;
		$this->user = $user;
		$this->pass = $pass;
	}

	function generateReports($project, $isWikiProject, $project_pages, $member_cat_type)
	{
    	$dbh_tools = new PDO("mysql:host={$this->tools_host};dbname=s51454__CleanupWorklistBot;charset=utf8", $this->user, $this->pass);
   		$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$cleanup_pages = 0;
		$issue_count = 0;
		$added_pages = 0;
		$removed_pages = 0;
		$curclean = array();
		$asof_date = $this->asof_date['month'] . ' '. $this->asof_date['mday'] . ', ' . $this->asof_date['year'];
		$groups = array();
		$this->categories = array();
		$titles = array();
		$project_title = str_replace('_', ' ', $project);
		$filesafe_project = str_replace('/', '_', $project);
		$clquery = $dbh_tools->prepare('SELECT cat_id FROM categorylinks WHERE cl_from = ?');
		$expiry = strtotime('+1 week');
		$expiry = date('D, d M Y', $expiry) . ' 00:00:00 GMT';

		$results = $dbh_tools->query('SELECT p.`page_title`, `importance`, `class`, lp.`page_title` AS blp FROM `page` p LEFT JOIN `livingpeople` lp ON p.`page_title` = lp.`page_title`');

		while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
			$pagetitle = $row['page_title'];

			$clquery->bindValue(1, $pagetitle);
			$clquery->execute();

			if ($row['importance'] == null) $row['importance'] = '';
			if ($row['class'] == null) $row['class'] = '';
			if ($row['blp'] == null) $row['blp'] = 0; else $row['blp'] = 1;
			$curclean[$pagetitle] = [self::KEY_IMP => $row['importance'], self::KEY_CLS => $row['class'], self::KEY_BLP => $row['blp'], self::KEY_ISSUES => []];
			++$cleanup_pages;

			while ($clrow = $clquery->fetch(PDO::FETCH_ASSOC)) {
				$cat_id = (int)$clrow['cat_id'];
				++$issue_count;
				if (! isset($this->categories[$cat_id])) {
					$cat = $this->catobj->categories[$cat_id];
					$this->categories[$cat_id] = [self::KEY_TITLE => $cat['t'], self::KEY_MTH => $cat['m'], self::KEY_YR => $cat['y']];
				}
				$curclean[$pagetitle][self::KEY_ISSUES][] = $cat_id;
			}

			$clquery->closeCursor();
		}

		ksort($curclean);

		$results->closeCursor();
		$results = null;

		$artcleanpct = round(($cleanup_pages / $project_pages) * 100, 0);

		$wikiproject = ($isWikiProject) ? 'WikiProject_' : '';
		$projecturl = "https://en.wikipedia.org/wiki/Wikipedia:{$wikiproject}" . $project;
		$histurl = $this->urlpath . 'history/' . $filesafe_project . '.html';
		$bycaturl = $this->urlpath . 'bycat/' . $filesafe_project . '.html';
		$wikiprefix = 'https://en.wikipedia.org/wiki/';

		$csvurl = $this->urlpath . 'csv/' . $filesafe_project . '.csv';
		$csvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $filesafe_project . '.csv';
		$bakcsvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $filesafe_project . '.csv.bak';
		$tmpcsvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $filesafe_project . '.csv.tmp';

		// Save the previous csv.
		if (file_exists($csvpath)) {
			@unlink($bakcsvpath);
			rename($csvpath, $bakcsvpath);
		}

		// Load the previous csv to detect changes.
		$prevclean = array();

		if (file_exists($bakcsvpath)) {
			$hndl = fopen($bakcsvpath, 'rb');
			$x = 0;

			while (! feof($hndl)) {
				$buffer = rtrim(fgets($hndl));
				if (strlen($buffer) == 0) continue; // Skip empty lines
				if ($x++ == 0) continue; // Skip header
				$fields = CSVString::parse($buffer);
				$title = $fields[0];
				array_shift($fields);
				if (isset($curclean[$title])) $prevclean[$title] = true;
				else $prevclean[$title] = SplFixedArray::fromArray($fields, false);
			}
			fclose($hndl);
		}

		//
		// Write alpha and csv
		//

		$csvhndl = fopen($tmpcsvpath, 'wb');
		fwrite($csvhndl, '"Article","Importance","Class","Count","Oldest month","Categories"' . "\n");

		$alphaurl = $this->urlpath . 'alpha/' . $filesafe_project . '.html';
		$alphapath = $this->outputdir . 'alpha' . DIRECTORY_SEPARATOR . $filesafe_project . '.html';
		$alphahndl = fopen($alphapath, 'wb');
		$wikiproject = ($isWikiProject) ? 'WikiProject ' : '';
		$page_number = 1;
		$page_size = 0;
		$blps = [];

		$this->writeAlphaHeader($alphahndl, $expiry, $wikiproject, $project_title, $projecturl, $project_pages, $cleanup_pages,
		    $artcleanpct, $issue_count, $bycaturl, $csvurl, $histurl, $page_number, $asof_date);

		foreach ($curclean as $title => &$art) {
			$arturl = $wikiprefix . urlencode(str_replace(' ', '_', $title));
			$consolidated = $this->_consolidateCats($art[self::KEY_ISSUES]);
			$cats = implode(', ', $consolidated['issues']);
			$icount = count($art[self::KEY_ISSUES]);

			fwrite($csvhndl, CSVString::format(array($title, $art[self::KEY_IMP], $art[self::KEY_CLS], $icount, $consolidated['earliest'], $cats)) . "\n");

			$consolidated = $this->_consolidateCats($art[self::KEY_ISSUES], true);
			$cats = implode(', ', $consolidated['issues']);

			$clssort = CreateTables::$CLASSES[$art[self::KEY_CLS]];
			$impsort = CreateTables::$IMPORTANCES[$art[self::KEY_IMP]];

			if ($page_size > self::MAX_PAGE_SIZE) {
			    ++$page_number;
			    $this->writeAlphaFooter($alphahndl, $page_number, $filesafe_project);
			    fclose($alphahndl);

			    $alphapath = $this->outputdir . 'alpha' . DIRECTORY_SEPARATOR . $filesafe_project . "{$page_number}.html";
			    $alphahndl = fopen($alphapath, 'wb');
			    $this->writeAlphaHeader($alphahndl, $expiry, $wikiproject, $project_title, $projecturl, $project_pages, $cleanup_pages,
			        $artcleanpct, $issue_count, $bycaturl, $csvurl, $histurl, $page_number, $asof_date);
			    $page_size = 0;
			}

			$blp = '';
			if ($art[self::KEY_BLP]) {
			    $blp = ' (BLP)';
			    $blps[] = $title;
			}

			$data_line = "<tr><td><a href=\"$arturl\">" . htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a>$blp</td><td data-sort-value='$impsort'>{$art[self::KEY_IMP]}</td>
				<td data-sort-value='$clssort'>{$art[self::KEY_CLS]}</td><td align='right'>$icount</td>
				<td data-sort-value='{$consolidated['earliestsort']}'>{$consolidated['earliest']}</td><td>$cats</td></tr>\n";
			fwrite($alphahndl, $data_line);
			$page_size += strlen($data_line);

			$titles[$title] = array(self::KEY_CLS => $art[self::KEY_CLS], self::KEY_BLP => $art[self::KEY_BLP],
				self::KEY_CLSSORT => $clssort, self::KEY_IMP => $art[self::KEY_IMP],
				self::KEY_IMPSORT => $impsort, self::KEY_EARLIEST => $consolidated['earliest'],
				self::KEY_EARLIESTSORT => $consolidated['earliestsort'],
				self::KEY_CATS => $consolidated['issues'], self::KEY_ICOUNT => $icount);

			// Group by cat
			foreach ($art[self::KEY_ISSUES] as $cat_id) {
				$cat = str_replace('_', ' ', $this->categories[$cat_id][self::KEY_TITLE]);
				if (! isset($groups[$cat])) $groups[$cat] = array();
				$groups[$cat][$title] = true;
			}

			if (isset($prevclean[$title])) $art = true; // Free up the memory
		}
		unset($art);

		$this->writeAlphaFooter($alphahndl, 0, '');

		fclose($csvhndl);
		fclose($alphahndl);

		// Calculate section anchors

		$anchors = array();

		foreach (Categories::$CATEGORIES as $catname => $catparams) {
			$displayname = $catname;
			if (isset($catparams['display'])) $displayname = $catparams['display'];

			if (! isset($anchors[$displayname])) $anchors[$displayname] = array();
			if (! in_array($displayname, $anchors[$displayname])) $anchors[$displayname][] = $displayname;
			if (isset($catparams['display'])) $anchors[$displayname][] = $catname;
		}

		foreach (Categories::$SHORTCATS as $catname => $displayname) {
			if (! isset($anchors[$displayname])) $anchors[$displayname] = array();
			if (! in_array($displayname, $anchors[$displayname])) $anchors[$displayname][] = $displayname;
			$anchors[$displayname][] = $catname;
		}

		// Group the cats

		$catgroups = array();
		ksort($groups);

		foreach ($groups as $cat => &$group) {
			if (isset(Categories::$parentCats[$cat])) $testcat = Categories::$parentCats[$cat];
			else $testcat = $cat;

			if (isset(Categories::$CATEGORIES[$testcat]['group'])) $catgroup = Categories::$CATEGORIES[$testcat]['group'];
			else $catgroup = 'General';

			if (isset(Categories::$CATEGORIES[$cat]['display'])) $cat = Categories::$CATEGORIES[$cat]['display'];
			elseif (isset(Categories::$SHORTCATS[$cat])) $cat = Categories::$SHORTCATS[$cat];

			if (! isset($catgroups[$catgroup])) $catgroups[$catgroup] = array();
			if (isset($catgroups[$catgroup][$cat])) {
				$catgroups[$catgroup][$cat] = $catgroups[$catgroup][$cat] + $group;
			}
			else $catgroups[$catgroup][$cat] = $group;
		}
		unset($group);
		unset($groups);

		ksort($catgroups);

		// Calulate start page for sections

		$page_number = 1;
		$page_size = 0;
		$section_pages = array();

		foreach ($catgroups as $catgroup => &$cats) {
		    ksort($cats);
		    if (! isset($section_pages[$catgroup])) $section_pages[$catgroup] = $page_number;

		    foreach ($cats as $cat => &$arts) {
		        if (! isset($section_pages[$cat])) $section_pages[$cat] = $page_number;
		        $catlen = strlen($cat);

		        foreach ($arts as $title => $dummy) {

		            //Strip the current cat prefix to make page smaller
		            $art = $titles[$title];
		            $keycats = $art[self::KEY_CATS];
		            foreach ($keycats as $key => $value) {
		                if (strpos($value, $cat) === 0) {
		                    if (strlen($value) > $catlen) $keycats[$key] = '...' . substr($value, $catlen);
		                    else unset ($keycats[$key]);
		                }
		            }
		            $artcats = implode(', ', $keycats);

		            $blp = $art[self::KEY_BLP] ? ' (BLP)' : '';

		            $data_line = "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
		                htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a>$blp</td>
						<td data-sort-value='{$art[self::KEY_IMPSORT]}'>{$art[self::KEY_IMP]}</td>
						<td data-sort-value='{$art[self::KEY_CLSSORT]}'>{$art[self::KEY_CLS]}</td><td align='right'>{$art[self::KEY_ICOUNT]}</td>
						<td data-sort-value='{$art[self::KEY_EARLIESTSORT]}'>{$art[self::KEY_EARLIEST]}</td><td>{$artcats}</td></tr>\n";

		            $page_size += strlen($data_line);

		            if ($page_size > self::MAX_PAGE_SIZE) {
		                ++$page_number;
		                $page_size = 0;
		            }

		        }
		    }
		    unset($arts);
		}
		unset($cats);

		$bycatpath = $this->outputdir . 'bycat' . DIRECTORY_SEPARATOR . $filesafe_project . '.html';
		$bycathndl = fopen($bycatpath, 'wb');
		$page_number = 1;

		$this->writeBycatHeader($bycathndl, $expiry, $wikiproject, $project_title, $projecturl, $asof_date, $project_pages,
		    $cleanup_pages, $artcleanpct, $issue_count, $alphaurl, $csvurl, $histurl, $page_number);

		// Write the TOC

		fwrite($bycathndl, "<div class='toc'><center>Contents</center>\n");
		fwrite($bycathndl, "<ul>\n");

		if (! empty($prevclean)) {
			fwrite($bycathndl, "<li><a href='#Changes since last update'>Changes since last update</a></li>\n");
			fwrite($bycathndl, "<ul>\n");
			$newarts = array_diff_key($curclean, $prevclean);
			$artcount = count($newarts);
			$added_pages = $artcount;

			fwrite($bycathndl, "<li><a href='#New articles'>New articles ($artcount)</a></li>\n");

			$resarts = array_diff_key($prevclean, $curclean);
			$artcount = count($resarts);
			$removed_pages = $artcount;

			fwrite($bycathndl, "<li><a href='#Resolved articles'>Resolved articles ($artcount)</a></li>\n");
			fwrite($bycathndl, "</ul>\n");
		}

		if (! empty($blps) && ! in_array($project_title, ['Biography','Football'])) {
		    fwrite($bycathndl, "<li><a href='#Biographies of living persons'>Biographies of living persons</a></li>\n");
		    fwrite($bycathndl, "<ul>\n");

		    $artcount = count($blps);

		    fwrite($bycathndl, "<li><a href='#BLPs'>BLPs ($artcount)</a></li>\n");
		    fwrite($bycathndl, "</ul>\n");
		}

		foreach ($catgroups as $catgroup => &$cats) {
		    $page_name = '';
		    if ($section_pages[$catgroup] > 1) $page_name = $filesafe_project . "{$section_pages[$catgroup]}.html";
		    fwrite($bycathndl, "<li><a href='$page_name#$catgroup'>$catgroup</a></li>\n");
			fwrite($bycathndl, "<ul>\n");

			foreach ($cats as $cat => &$arts) {
			    $page_name = '';
			    if ($section_pages[$cat] > 1) $page_name = $filesafe_project . "{$section_pages[$cat]}.html";
			    $artcount = count($arts);
				fwrite($bycathndl, "<li><a href='$page_name#$cat'>$cat ($artcount)</a></li>\n");
			}
			unset($arts);

			fwrite($bycathndl, "</ul>\n");
		}
		unset($cats);

		fwrite($bycathndl, "</ul></div>\n");

		// Write the changes

		if (! empty($prevclean)) {
			fwrite($bycathndl, "<a name='Changes since last update'></a><h2>Changes since last update</h2>\n");

			$newarts = array_diff_key($curclean, $prevclean);
			$artcount = count($newarts);
			fwrite($bycathndl, "<a name='New articles'></a><h3>New articles ($artcount)</h3>\n");
			fwrite($bycathndl, "<table class='wikitable tablesorter'><thead><tr><th>Article</th><th>Importance</th><th>Class</th>
				<th class='unsortable'>Issues</th></tr></thead><tbody>\n
				");

			foreach ($newarts as $title => &$art) {
				$consolidated = $this->_consolidateCats($art[self::KEY_ISSUES], true);
				$artcats = implode(', ', $consolidated['issues']);
				$clssort = CreateTables::$CLASSES[$art[self::KEY_CLS]];
				$impsort = CreateTables::$IMPORTANCES[$art[self::KEY_IMP]];
				$blp = $art[self::KEY_BLP] ? ' (BLP)' : '';
				fwrite($bycathndl, "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
					htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a>$blp</td>
					<td data-sort-value='{$impsort}'>{$art[self::KEY_IMP]}</td>
					<td data-sort-value='{$clssort}'>{$art[self::KEY_CLS]}</td>
					<td>{$artcats}</td></tr>\n");
			}
			unset($art);

			fwrite($bycathndl, "</tbody></table>\n");

			$resarts = array_diff_key($prevclean, $curclean);
			$artcount = count($resarts);
			fwrite($bycathndl, "<a name='Resolved articles'></a><h3>Resolved articles ($artcount)</h3>\n");
			fwrite($bycathndl, "<table class='wikitable tablesorter'><thead><tr><th>Article</th><th>Importance</th><th>Class</th>
					<th class='unsortable'>Issues</th></tr></thead><tbody>\n
					");

			foreach ($resarts as $title => &$fields) {
				fwrite($bycathndl, "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
					htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a></td>
					<td>{$fields[0]}</td><td>{$fields[1]}</td><td>{$fields[4]}</td></tr>\n");
			}
			unset($fields);

			fwrite($bycathndl, "</tbody></table>\n");
		}

		// Write the blps

		if (! empty($blps) && ! in_array($project_title, ['Biography','Football'])) {
		    sort($blps);
		    fwrite($bycathndl, "<a name='Biographies of living persons'></a><h2>Biographies of living persons</h2>\n");

		    $artcount = count($blps);
		    fwrite($bycathndl, "<a name='BLPs'></a><h3>BLPs ($artcount)</h3>\n");
		    fwrite($bycathndl, "<table class='wikitable tablesorter'><thead><tr><th>Article</th><th>Importance</th><th>Class</th>
				<th class='unsortable'>Issues</th></tr></thead><tbody>\n
				");

		    foreach ($blps as $title) {
		        $art = $titles[$title];
		        $keycats = $art[self::KEY_CATS];
		        $artcats = implode(', ', $keycats);

		        $data_line = "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
		  		            htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a></td>
						<td data-sort-value='{$art[self::KEY_IMPSORT]}'>{$art[self::KEY_IMP]}</td>
						<td data-sort-value='{$art[self::KEY_CLSSORT]}'>{$art[self::KEY_CLS]}</td><td align='right'>{$art[self::KEY_ICOUNT]}</td>
						<td data-sort-value='{$art[self::KEY_EARLIESTSORT]}'>{$art[self::KEY_EARLIEST]}</td><td>{$artcats}</td></tr>\n";

		  		fwrite($bycathndl, $data_line);
		    }
		    unset($art);

		    fwrite($bycathndl, "</tbody></table>\n");
		}

		// Write the cats

		$page_size = 0;
		$need_group_heading = true;
		$need_cat_heading = true;

		foreach ($catgroups as $catgroup => &$cats) {

			foreach ($cats as $cat => &$arts) {
				$catlen = strlen($cat);
				$artcount = count($arts);

				ksort($arts);

				foreach ($arts as $title => $dummy) {
					//Strip the current cat prefix to make page smaller
					$art = $titles[$title];
					$keycats = $art[self::KEY_CATS];
					foreach ($keycats as $key => $value) {
						if (strpos($value, $cat) === 0) {
							if (strlen($value) > $catlen) $keycats[$key] = '...' . substr($value, $catlen);
							else unset ($keycats[$key]);
						}
					}
					$artcats = implode(', ', $keycats);

					if ($page_size > self::MAX_PAGE_SIZE) {
					    ++$page_number;
					    fwrite($bycathndl, "</tbody></table>\n");
					    $this->writeBycatFooter($bycathndl, $page_number, $filesafe_project);
					    fclose($bycathndl);

					    $bycatpath = $this->outputdir . 'bycat' . DIRECTORY_SEPARATOR . $filesafe_project . "{$page_number}.html";
					    $bycathndl = fopen($bycatpath, 'wb');
					    $this->writeBycatHeader($bycathndl, $expiry, $wikiproject, $project_title, $projecturl, $asof_date, $project_pages,
					        $cleanup_pages, $artcleanpct, $issue_count, $alphaurl, $csvurl, $histurl, $page_number);

					    $need_group_heading = true;
					    $need_cat_heading = true;
					    $page_size = 0;
					}

					if ($need_group_heading) {
					    fwrite($bycathndl, "<a name='$catgroup'></a><h2>$catgroup</h2>\n");
					    $need_group_heading = false;
					}

					if ($need_cat_heading) {
    					if (! isset($anchors[$cat])) fwrite($bycathndl, "<a name='$cat'></a>");
    					else foreach ($anchors[$cat] as $anchorname) fwrite($bycathndl, "<a name='$anchorname'></a>");
    					fwrite($bycathndl, "<h3>$cat ($artcount)</h3>\n");
    					fwrite($bycathndl, "<table class='wikitable tablesorter'><thead><tr><th>Article</th><th>Importance</th><th>Class</th><th>Count</th>
    					   <th>Oldest</th><th class='unsortable'>Issues</th></tr></thead><tbody>\n");
    					$need_cat_heading = false;
					}

					$blp = $art[self::KEY_BLP] ? ' (BLP)' : '';

					$data_line = "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
						htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a>$blp</td>
						<td data-sort-value='{$art[self::KEY_IMPSORT]}'>{$art[self::KEY_IMP]}</td>
						<td data-sort-value='{$art[self::KEY_CLSSORT]}'>{$art[self::KEY_CLS]}</td><td align='right'>{$art[self::KEY_ICOUNT]}</td>
						<td data-sort-value='{$art[self::KEY_EARLIESTSORT]}'>{$art[self::KEY_EARLIEST]}</td><td>{$artcats}</td></tr>\n";

					$page_size += strlen($data_line);
					fwrite($bycathndl, $data_line);
				}

				fwrite($bycathndl, "</tbody></table>\n");
				$need_cat_heading = true;
			}

			$need_group_heading = true;
			unset($arts);
		}
		unset($cats);

		$this->writeBycatFooter($bycathndl, 0, '');
		fclose($bycathndl);

        //
		// Write the history list
		//
		
		$this->generateGraph($project);

		$sth = $dbh_tools->prepare("INSERT INTO history VALUES (?, ?, $project_pages, $cleanup_pages, $issue_count, $added_pages, $removed_pages)");
		$histdate = sprintf('%d-%02d-%02d', $this->asof_date['year'], $this->asof_date['mon'], $this->asof_date['mday']);
		$sth->execute(array($project, $histdate));

		$histpath = $this->outputdir . 'history' . DIRECTORY_SEPARATOR . $filesafe_project . '.html';
		$graphurl = $this->urlpath . 'img/' . $filesafe_project . '.png';
		$histhndl = fopen($histpath, 'wb');
		fwrite($histhndl, "<!DOCTYPE html>
		<html><head>
		<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
		<title>Cleanup history for {$wikiproject}{$project_title}</title>
		<link rel='stylesheet' type='text/css' href='../../css/cwb.css' />
		</head><body>
		<p>Cleanup history for <a href=\"$projecturl\">{$wikiproject}{$project_title}</a>.</p>
		<p>Listings: <a href=\"$alphaurl\">Alphabetic</a> <b>&bull;</b> <a href=\"$bycaturl\">By category</a> <b>&bull;</b> <a href=\"$csvurl\">CSV</a> <b>&bull;</b> History</p>
        <p><img src=\"$graphurl\" /></p>
		<table class='wikitable'><thead><tr><th>Date</th><th>Total articles</th><th>Cleanup articles</th><th>Cleanup issues</th><th>New articles</th><th>Resolved articles</th></tr></thead><tbody>\n
		");

		$sth = $dbh_tools->prepare("SELECT * FROM history WHERE project = ? ORDER BY time");
		$sth->execute(array($project));

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			fwrite($histhndl, "<tr><td>{$row['time']}</td><td>{$row['total_articles']}</td><td>{$row['cleanup_articles']}</td><td>{$row['issues']}</td><td>{$row['added_articles']}</td><td>{$row['removed_articles']}</td></tr>\n");
		}

		fwrite($histhndl, "</tbody></table><a href='/privacy.html'>Privacy Policy</a> <b>&bull;</b> Generated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot' class='novisited'>CleanupWorklistBot</a></body></html>");
		fclose($histhndl);

		//Finished successfully
		rename($tmpcsvpath, $csvpath);

		$wikiproject = (($isWikiProject) ? 'WikiProject_' : '') . $project;

		$sth = $dbh_tools->prepare("DELETE FROM project WHERE `name` = ?");
		$sth->execute(array($wikiproject));
		$sth = $dbh_tools->prepare("INSERT INTO project VALUES (?, ?, ?)");
		$sth->execute(array($wikiproject, 1, $member_cat_type));

		$dbh_tools = null;

		return true;
	}

	/**
	 * Consolidate issues that have multiple dates.
	 * Determine the earliest date.
	 *
	 * @param array $cat_ids index into $this->categories keys = 'title', 'mth', 'yr'
	 * @param bool $shortnames return short category names
	 * @return array Earliest date 'earliest', 'issues', 'earliestsort' Consolidated issues, one string per category
	 */
	function _consolidateCats($cat_ids, $shortnames = false)
	{
		$results = [];
		$earliestyear = 9999;
		$earliestmonth = 99;

		foreach ($cat_ids as $cat_id) {
			$cat = str_replace('_', ' ', $this->categories[$cat_id][self::KEY_TITLE]);
			if ($shortnames)
			{
				if (isset(Categories::$CATEGORIES[$cat]['display'])) $cat = Categories::$CATEGORIES[$cat]['display'];
				elseif (isset(Categories::$SHORTCATS[$cat])) $cat = Categories::$SHORTCATS[$cat];
			}

			if (! isset($results[$cat])) $results[$cat] = array();

			if ($this->categories[$cat_id][self::KEY_YR] != null) {
				$intyear = (int)$this->categories[$cat_id][self::KEY_YR];
				$month = '';

				if ($this->categories[$cat_id][self::KEY_MTH] != null) {
					$intmonth = (int)$this->categories[$cat_id][self::KEY_MTH];
					$month = self::$MONTHS[$intmonth] . ' ';

					if ($intyear == $earliestyear && $intmonth < $earliestmonth) {
						$earliestmonth = $intmonth;
					} elseif ($intyear < $earliestyear) {
						$earliestmonth = $intmonth;
						$earliestyear = $intyear;
					}
				} elseif ($intyear < $earliestyear) {
					$earliestyear = $intyear;
					$earliestmonth = 99;
				}

				$results[$cat][] = $month . $this->categories[$cat_id][self::KEY_YR];
			}
		}

		$cats = [];

		foreach ($results as $cat => $dates) {
			$dates = implode(', ', $dates);
			if (! empty($dates)) $cat .= " ($dates)";
			$cats[] = $cat;
		}

		$earliestdate = '';
		$earliestsort = 999999;

		if ($earliestyear != 9999) {
			$month = '';
			$monthsort = 99;
			if ($earliestmonth != 99) {
				$month = self::$MONTHS[$earliestmonth] . ' ';
				$monthsort = sprintf('%02d', $earliestmonth);
			}
			$earliestdate = $month . $earliestyear;
			$earliestsort = $earliestyear . $monthsort;
		}

		return array('earliest' => $earliestdate, 'issues' => $cats, 'earliestsort' => $earliestsort);
	}

	function writeAlphaHeader($alphahndl, $expiry, $wikiproject, $project_title, $projecturl, $project_pages, $cleanup_pages,
	    $artcleanpct, $issue_count, $bycaturl, $csvurl, $histurl, $page_number, $asof_date)
	{
	    $page_title = '';
	    if ($page_number > 1) $page_title = " page $page_number";

	    fwrite($alphahndl, "<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
			<meta http-equiv='Expires' content='$expiry' />
			<title>Cleanup listing for {$wikiproject}{$project_title}{$page_title}</title>
    		<link rel='stylesheet' type='text/css' href='../../css/cwb.css' />
			<script type='text/javascript' src='../../js/jquery-2.1.1.min.js'></script>
			<script type='text/javascript' src='../../js/jquery.tablesorter.min.js'></script>
			</head><body>
			<script type='text/javascript'>
				$(document).ready(function()
				    {
				        $('#myTable').tablesorter({ headers: { 5: { sorter: false} } });
				    }
				);
			</script>
			<p>Cleanup listing for <a href=\"$projecturl\">{$wikiproject}{$project_title}</a> as of {$asof_date}{$page_title}.</p>
			<p>Of the $project_pages articles in this project $cleanup_pages or $artcleanpct% are marked for cleanup, with $issue_count issues in total.</p>
			<p>Listings: Alphabetic <b>&bull;</b> <a href=\"$bycaturl\">By category</a> <b>&bull;</b> <a href=\"$csvurl\">CSV</a> <b>&bull;</b> <a href=\"$histurl\">History</a></p>
			<table id='myTable' class='wikitable'><thead><tr><th>Article</th><th>Importance</th><th>Class</th><th>Count</th>
				<th>Oldest</th><th class='unsortable'>Issues</th></tr></thead><tbody>
    		");
	}

	function writeAlphaFooter($alphahndl, $page_number, $filesafe_project)
	{
	    fwrite($alphahndl, "</tbody></table>");

	    if ($page_number) {
	        $alphaurl = $filesafe_project . "{$page_number}.html";
	        fwrite($alphahndl, "<div><a href=\"$alphaurl\">Next page</a></div>");
	    }

	    fwrite($alphahndl, "<div>BLP = Biography of a Living Person</div><div><a href='/privacy.html'>Privacy Policy</a> <b>&bull;</b> Generated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot' class='novisited'>CleanupWorklistBot</a></div></body></html>");
	}

	function writeBycatHeader($bycathndl, $expiry, $wikiproject, $project_title, $projecturl, $asof_date, $project_pages,
	    $cleanup_pages, $artcleanpct, $issue_count, $alphaurl, $csvurl, $histurl, $page_number)
	{
	    $page_title = '';
	    if ($page_number > 1) $page_title = " page $page_number";

	    fwrite($bycathndl, "<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
			<meta http-equiv='Expires' content='$expiry' />
			<title>Cleanup listing for {$wikiproject}{$project_title}{$page_title}</title>
    		<link rel='stylesheet' type='text/css' href='../../css/cwb.css' />
			<script type='text/javascript' src='../../js/jquery-2.1.1.min.js'></script>
			<script type='text/javascript' src='../../js/jquery.tablesorter.min.js'></script>
			</head><body>
			<script type='text/javascript'>
				$(document).ready(function()
				    {
				        $('.tablesorter').tablesorter({ headers: { 6: { sorter: false} } });
				    }
				);
			</script>
			<p>Cleanup listing for <a href=\"$projecturl\">{$wikiproject}{$project_title}</a> as of {$asof_date}{$page_title}.</p>
			<p>Of the $project_pages articles in this project $cleanup_pages or $artcleanpct% are marked for cleanup, with $issue_count issues in total.</p>
			<p>Listings: <a href=\"$alphaurl\">Alphabetic</a> <b>&bull;</b> By category <b>&bull;</b> <a href=\"$csvurl\">CSV</a> <b>&bull;</b> <a href=\"$histurl\">History</a></p>
			<p>... represents the current issue name. Issue names are abbreviated category names.</p>
    		");

	}

	function writeBycatFooter($bycathndl, $page_number, $filesafe_project)
	{
	    if ($page_number) {
	        $bycaturl = $filesafe_project . "{$page_number}.html";
	        fwrite($bycathndl, "<div><a href=\"$bycaturl\">Next page</a></div>");
	    }
	    fwrite($bycathndl, "<div>BLP = Biography of a Living Person</div><div><a href='/privacy.html'>Privacy Policy</a> <b>&bull;</b> Generated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot' class='novisited'>CleanupWorklistBot</a></div></body></html>");
	}
	
	/**
	 * Generated an issue history graph
	 *
	 * @param $project
	 * @param $outputdir
	 * @param $tools_host
	 * @param $user
	 * @param $pass
	 */
	protected function generateGraph($project)
	{
	    $data = new pData();
	    
	    // Insert points
	    $issue_counts = [];
	    
	    $dbh_tools = new PDO("mysql:host={$this->tools_host};dbname=s51454__CleanupWorklistBot;charset=utf8", $this->user, $this->pass);
	    $dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $sth = $dbh_tools->prepare("SELECT YEAR(time) as `year`, issues FROM history WHERE project = ? ORDER BY time");
	    $sth->execute([$project]);
	    
	    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	        $year = $row['year'];
	        
	        if (! isset($issue_counts[$year])) $issue_counts[$year] = [];
	        $issue_counts[$year][] = $row['issues'];
	    }
	    
	    if (empty($issue_counts)) return;
	    reset($issue_counts);
	    $first_year = key($issue_counts);
	    end($issue_counts);
	    $last_year = key($issue_counts);
	    	    
	    foreach ($issue_counts as $year => $year_issue_counts) {
	        $year_count = count($year_issue_counts);
	        if ($year_count > 52) $year_count = 52;
	        
	        for ($x = 0; $x < $year_count; ++$x) {
	            $current_count = $year_issue_counts[$x];
	            $data->AddPoint($current_count, 'issues');
	            $data->AddPoint($year, 'years');
	        }
	        
	        // Fill out a short year so have 52 values
	        for (; $year != $first_year && $year != $last_year && $x < 52; ++$x) {
	            $data->AddPoint($current_count, 'issues');
	            $data->AddPoint($year, 'years');
	        }
	    }
	    	    	    
	    // Prep the data
	    $data->AddSerie('issues');
	    $data->SetSerieName('Issues', 'issues');
	    $data->SetXAxisName('Year');
	    $data->SetYAxisName('Issue count');
	    $data->SetAbsciseLabelSerie('years');
	    
	    // Prep the graph
	    $graph = new pChart(900,400);
	    $graph->drawBackground(225, 225, 225);
	    $graph->setFontProperties($graph->FontDir . 'tahoma.ttf',10);
	    $graph->setGraphArea(70,30,880,360);
	    $graph->drawGraphArea(252,252,252);
	    $graph->drawScale($data->GetData(), $data->GetDataDescription(),SCALE_NORMAL,100,100,100,TRUE,0,0,FALSE,52,FALSE,
	        52 - count($issue_counts[$first_year]));
	    $graph->setColorPalette(0, 0, 0, 255);
	    
	    // Draw the line graph
	    $graph->drawLineGraph($data->GetData(), $data->GetDataDescription());
	    
	    // Finish the graph
	    $graph->setFontProperties($graph->FontDir . 'tahoma.ttf',8);
	    $graph->drawLegend(75,35,$data->GetDataDescription(), 225, 225, 225);
	    $graph->setFontProperties($graph->FontDir . 'tahoma.ttf',12);
	    $graph->drawTitle(null,22,"$project Issues",0,0,0);
	    
	    $filesafe_project = str_replace('/', '_', $project);
	    $outfile = $this->outputdir . 'img' . DIRECTORY_SEPARATOR . $filesafe_project . '.png';
	    $graph->Render($outfile);
	}
}