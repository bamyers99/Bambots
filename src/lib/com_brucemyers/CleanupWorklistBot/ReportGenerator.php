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
use com_brucemyers\MediaWiki\ResultWriter;
use com_brucemyers\CleanupWorklistBot\CreateTables;
use com_brucemyers\CleanupWorklistBot\Categories;

class ReportGenerator
{
	static $MONTHS = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
		7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
	var $outputdir;
	var $urlpath;
	var $dbh_tools;
	var $asof_date;
	var $resultWriter;

	function __construct(PDO $dbh_tools, $outputdir, $urlpath, $asof_date, ResultWriter $resultWriter)
	{
		$this->dbh_tools = $dbh_tools;
		$this->outputdir = $outputdir;
		$this->urlpath = $urlpath;
		$this->asof_date = $asof_date;
        $this->resultWriter = $resultWriter;
	}

	function generateReports($project, $isWikiProject, $project_pages)
	{
		$cleanup_pages = 0;
		$issue_count = 0;
		$curclean = array();
		$asof_date = $this->asof_date;
		$groups = array();
		$project_title = str_replace('_', ' ', $project);

		$results = $this->dbh_tools->query('SELECT `page_title`, `importance`, `class`, `cat_title`, `month`, `year`
				FROM `page` p, `categorylinks` cl, `category` cat
				WHERE p.article_id = cl.cl_from AND cl.cat_id = cat.cat_id
				ORDER by `page_title`, `cat_title`');

		while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
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

		if (file_exists($bakcsvpath)) {
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
		}

		//
		// Write alpha and csv
		//

		$csvhndl = fopen($tmpcsvpath, 'wb');
		fwrite($csvhndl, '"Article","Importance","Class","Count","Oldest month","Categories"' . "\n");

		$alphaurl = $this->urlpath . 'alpha/' . urlencode($project) . '.html';
		$alphapath = $this->outputdir . 'alpha' . DIRECTORY_SEPARATOR . $project . '.html';
		$alphahndl = fopen($alphapath, 'wb');
		$wikiproject = ($isWikiProject) ? 'WikiProject ' : '';
		fwrite($alphahndl, "<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
    		<title>Cleanup listing for {$wikiproject}{$project_title}</title>
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
			<p>Cleanup listing for <a href='$projecturl'>{$wikiproject}{$project_title}</a> as of $asof_date.</p>
			<p>Of the $project_pages articles in this project $cleanup_pages or $artcleanpct % are marked for cleanup, with $issue_count issues in total.</p>
			<p>Listings: Alphabetic <b>·</b> <a href='$bycaturl'>By Category</a> <b>·</b> <a href='$csvurl'>CSV</a> <b>·</b> <a href='$histurl'>History</a></p>
			<table id='myTable' class='wikitable'><thead><tr><th>Article</th><th>Importance</th><th>Class</th><th>Count</th>
				<th>Oldest</th><th class='unsortable'>Categories</th></tr></thead><tbody>
    		");

		foreach ($curclean as $title => $art) {
			$arturl = 'https://en.wikipedia.org/wiki/' . urlencode($title);;
			$title = str_replace('_', ' ', $title);
			$consolidated = $this->_consolidateCats($art['issues']);
			$cats = implode(', ', $consolidated['issues']);
			$icount = count($art['issues']);

			fwrite($csvhndl, CSVString::format(array($title, $art['imp'], $art['cls'], $icount, $consolidated['earliest'], $cats)) . "\n");

			$clssort = CreateTables::$CLASSES[$art['cls']];
			$impsort = CreateTables::$IMPORTANCES[$art['imp']];

			fwrite($alphahndl, "<tr><td><a href='$arturl'>$title</a></td><td data-sort-value='$impsort'>{$art['imp']}</td>
				<td data-sort-value='$clssort'>{$art['cls']}</td><td align='right'>$icount</td>
				<td data-sort-value='{$consolidated['earliestsort']}'>{$consolidated['earliest']}</td><td>$cats</td></tr>\n");

			// Group by cat
			foreach ($art['issues'] as $issue) {
				$cat = str_replace('_', ' ', $issue['title']);
				if (! isset($groups[$cat])) $groups[$cat] = array();
				$groups[$cat][$title] = array('cls' => $art['cls'], 'clssort' => $clssort, 'imp' => $art['imp'],
					'impsort' => $impsort, 'earliest' => $consolidated['earliest'], 'earliestsort' => $consolidated['earliestsort'],
					'cats' => $cats);
			}
		}

		fwrite($alphahndl, "</tbody></table>Generated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot'>CleanupWorklistBot</a></body></html>");

		fclose($csvhndl);
		fclose($alphahndl);

		//
		// Write the By Cat list
		//

		$output = "<noinclude>__NOINDEX__</noinclude>Cleanup listing for [[Wikipedia:{$wikiproject}{$project}|{$wikiproject}{$project_title}]] as of $asof_date.\n\n";
		$output .= "Of the $project_pages articles in this project $cleanup_pages or $artcleanpct % are marked for cleanup, with $issue_count issues in total.\n\n";
		$output .= "Listings: [$alphaurl Alphabetic] <b>·</b> By Category <b>·</b> [$csvurl CSV] <b>·</b> [$histurl History]\n\n";

		// Group the cats
		$catgroups = array();
		ksort($groups);

		foreach ($groups as $cat => $group) {
			if (isset(Categories::$parentCats[$cat])) $testcat = Categories::$parentCats[$cat];
			else $testcat = $cat;

			if (isset(Categories::$CATEGORIES[$testcat]['group'])) $catgroup = Categories::$CATEGORIES[$testcat]['group'];
			else $catgroup = 'General';

			if (! isset($catgroups[$catgroup])) $catgroups[$catgroup] = array();
			$catgroups[$catgroup][$cat] = $group;
		}

		ksort($catgroups);

		foreach ($catgroups as $catgroup => $cats) {
			$output .= "==$catgroup==\n<section begin='$catgroup' />\n";

			foreach ($cats as $cat => $arts) {
				$artcount = count($arts);
				$output .= "===$cat ($artcount)===\n";
				$output .= "<section begin='$cat' />\n";
				$output .= "{| class='wikitable sortable'\n|-\n!Article!!Importance!!Class!!Earliest!!class='unsortable'|Categories\n";

				foreach ($arts as $title => $art) {
					// a space is added after values that can be empty so ||| does not happen
					$output .= "|-\n|[[$title]]||data-sort-value='{$art['impsort']}'|{$art['imp']} ||data-sort-value='{$art['clssort']}'|{$art['cls']} ||data-sort-value='{$art['earliestsort']}'|{$art['earliest']} ||{$art['cats']}\n";
				}

				$output .= "|}\n";
				$output .= "<section end='$cat' />\n";
			}

			$output .= "<section end='$catgroup' />\n";
		}

		$output .= "\nGenerated by [[User:CleanupWorklistBot|CleanupWorklistBot]]\n";

		$this->resultWriter->writeResults("User:CleanupWorklistBot/lists/$project", $output, "most recent results, articles: $cleanup_pages, issues: $issue_count");

        //
		// Write the history list
		//

		//Finished successfully
		rename($tmpcsvpath, $csvpath);
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
			$cat = str_replace('_', ' ', $issue['title']);
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
}