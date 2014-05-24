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
use com_brucemyers\Util\CSVString;

class ReportGenerator
{
	static $MONTHS = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
		7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
	var $outputdir;
	var $urlpath;
	var $dbh_tools;
	var $asof_date;

	function ReportGenerator(PDO $dbh_tools, $outputdir, $urlpath, $asof_date)
	{
		$this->dbh_tools = $dbh_tools;
		$this->outputdir = $outputdir;
		$this->urlpath = $urlpath;
		$this->asof_date = $asof_date;
	}

	function generateReports($project, $isWikiProject, $project_pages)
	{
		$cleanup_pages = 0;
		$issue_count = 0;
		$curclean = array();
		$asof_date = $this->asof_date;

		$results = $this->dbh_tools->query('SELECT `page_title`, `importance`, `class`, `cat_title`, `month`, `year`
				FROM `page` p, `categorylinks` cl, `category` cat
				WHERE p.article_id = cl.cl_from AND cl.cat_id = cat.cat_id
				ORDER by `page_title`, `cat_title`');

		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			++$issue_count;
			$title = $row['page_title'];
			if (! isset($curclean[$title])) {
				if ($row['importance'] == null) $row['importance'] = '';
				if ($row['class'] == null) $row['class'] = '';
				$curclean[$title] = array('imp' => $row['importance'], 'cls' => $row['class'], 'issues' => array());
				++$cleanup_pages;
			}
			$curclean[$title]['issues'][] = array('title' => $row['cat_title'], 'mth' => $row['month'], 'yr' => $row['year']);
		}

		$results->closeCursor();

		$artcleanpct = round(($cleanup_pages / $project_pages) * 100, 0);

		$wikiproject = ($isWikiProject) ? 'WikiProject_' : '';
		$projecturl = "https://en.wikipedia.org/wiki/Wikipedia:{$wikiproject}" . urlencode($project);
		$histurl = $this->urlpath . 'history/' . urlencode($project) . '.html';
		$bycaturl = 'https://en.wikipedia.org/wiki/User:CleanupWorklistBot/lists/' . urlencode($project);

		$csvurl = $this->urlpath . 'csv/' . urlencode($project) . '.csv';
		$csvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $project . '.csv';
		$bakcsvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $project . '.csv.bak';
		$tmpcsvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $project . '.csv.tmp';

		// Save the previous csv.
		if (file_exists($csvpath)) {
			unlink($bakcsvpath);
			rename($csvpath, $bakcsvpath);
		}

		// Load the previous csv to detect changes.
		$prevclean = array();
		$hndl = fopen($bakcsvpath, 'rb');
		$x = 0;

		while (! feof($hndl)) {
			$buffer = rtrim(fgets($hndl));
			if (strlen($buffer) == 0) continue; // Skip empty lines
			if ($x++ == 0) continue; // Skip header
			$fields = CSVString::parse($buffer);
			$prevclean[$fields[0]] = $fields;
		}
		fclose($hndl);

		$csvhndl = fopen($tmpcsvpath, 'wb');
		fwrite($csvhndl, '"Article","Importance","Class","Count","Oldest month","Categories"' . "\n");

		$alphaurl = $this->urlpath . 'alpha/' . urlencode($project) . '.html';
		$alphapath = $this->outputdir . 'alpha' . DIRECTORY_SEPARATOR . $project . '.html';
		$alphahndl = fopen($alphapath, 'wb');
		$wikiproject = ($isWikiProject) ? 'WikiProject ' : '';
		fwrite($alphahndl, "<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
    		<title>Cleanup listing for {$wikiproject}{$project}</title>
			</head><body>
			<p>Cleanup listing for <a href='$projecturl'>{$wikiproject}{$project}</a> as of $asof_date.</p>
			<p>Of the $project_pages articles in this project $cleanup_pages or $artcleanpct % are marked for cleanup, with $issue_count issues in total.</p>
			<table><tr><th>Article</th><th>Importance</th><th>Class</th><th>Count</th><th>Oldest month</th><th>Categories</th></tr>
    		");

		// Write alpha and csv

		foreach ($curclean as $title => $art) {
			$cats = implode(', ', $this->_consolidateCats($art['issues']));
			$icount = count($art['issues']);
			fwrite($csvhndl, CSVString::format(array($title, $art['imp'], $art['cls'], $icount, $cats['earliest'], $cats['issues'])) . "\n");

			fwrite($alphahndl, "<tr><td>$title</td><td>{$art['imp']}</td><td>{$art['cls']}</td><td>$icount</td><td>{$cats['earliest']}</td><td>{$cats['issues']}</td></tr>\n");
		}

		fwrite($alphahndl, '</table></body></html>');

		$this->generateByCat();
		$this->generateHistory();
	}

	/**
	 * Consolidate issues that have multiple dates.
	 * Determine the earliest date.
	 *
	 * @param array $issues keys = 'title', 'mth', 'yr'
	 * @return array Earliest date 'earliest', 'issues' Consolidated issues, one string per category
	 */
	function _consolidateCats(&$issues)
	{
		$results = array();
		$earliestyear = 9999;
		$earliestmonth = 99;

		foreach ($issues as $issue) {
			$cat = $issue['title'];
			if (! isset($results[$cat])) {
				$results[$cat] = array();
			}

			if ($issue['yr'] != null) {
				$intyear = (int)$issue['yr'];
				$month = '';

				if ($issue['mth'] != null) {
					$intmonth = (int)$issue['mth'];
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

				$results[$cat][] = $month . $issue['yr'];
			}
		}

		$cats = array();

		foreach ($results as $cat => $dates) {
			$dates = implode(', ', $dates);
			if (! empty($dates)) $cat .= " ($dates)";
			$cats[] = $cat;
		}

		$earliestdate = '';
		if ($earliestyear != 9999) {
			$month = '';
			if ($earliestmonth != 99) $month = self::$MONTHS[$earliestmonth] . ' ';
			$earliestdate = $month . $earliestyear;
		}

		return array('earliest' => $earliestdate, 'issues' => $cats);
	}
}