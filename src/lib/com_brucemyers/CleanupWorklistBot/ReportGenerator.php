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
use com_brucemyers\MediaWiki\MediaWiki;
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
	var $categories;

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

	function __construct(PDO $dbh_tools, $outputdir, $urlpath, $asof_date, ResultWriter $resultWriter)
	{
		$this->dbh_tools = $dbh_tools;
		$this->outputdir = $outputdir;
		$this->urlpath = $urlpath;
		$this->asof_date = $asof_date;
        $this->resultWriter = $resultWriter;
	}

	function generateReports($project, $isWikiProject, $project_pages, $wiki_too_big = false, $max_page_size = MediaWiki::MAX_PAGE_SIZE,
		$write_csv = true)
	{
		$cleanup_pages = 0;
		$issue_count = 0;
		$curclean = array();
		$asof_date = $this->asof_date['month'] . ' '. $this->asof_date['mday'] . ', ' . $this->asof_date['year'];
		$groups = array();
		$this->categories = array();
		$titles = array();
		$project_title = str_replace('_', ' ', $project);
		$filesafe_project = str_replace('/', '_', $project);

		$results = $this->dbh_tools->query('SELECT `page_title`, `importance`, `class`, cat.`cat_id`, `cat_title`, `month`, `year`
				FROM `page` p, `categorylinks` cl, `category` cat
				WHERE p.article_id = cl.cl_from AND cl.cat_id = cat.cat_id
				ORDER BY `page_title`, `cat_title`');

		while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
			++$issue_count;
			$cat_id = (int)$row['cat_id'];
			$title = str_replace('_', ' ', $row['page_title']);

			if (! isset($curclean[$title])) {
				if ($row['importance'] == null) $row['importance'] = '';
				if ($row['class'] == null) $row['class'] = '';
				$curclean[$title] = array(self::KEY_IMP => $row['importance'], self::KEY_CLS => $row['class'], self::KEY_ISSUES => array());
				++$cleanup_pages;
			}
			if (! isset($this->categories[$cat_id])) $this->categories[$cat_id] = array(self::KEY_TITLE => $row['cat_title'], self::KEY_MTH => $row['month'], self::KEY_YR => $row['year']);
			$curclean[$title][self::KEY_ISSUES][] = $cat_id;
		}

		$results->closeCursor();

		$artcleanpct = round(($cleanup_pages / $project_pages) * 100, 0);

		$wikiproject = ($isWikiProject) ? 'WikiProject_' : '';
		$projecturl = "https://en.wikipedia.org/wiki/Wikipedia:{$wikiproject}" . $project;
		$histurl = $this->urlpath . 'history/' . $filesafe_project . '.html';
		if ($wiki_too_big) $bycaturl = $this->urlpath . 'bycat/' . $filesafe_project . '.html';
		else $bycaturl = 'https://en.wikipedia.org/wiki/User:CleanupWorklistBot/lists/' . $filesafe_project;
		$wikiprefix = 'https://en.wikipedia.org/wiki/';

		$csvurl = $this->urlpath . 'csv/' . $filesafe_project . '.csv';
		$csvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $filesafe_project . '.csv';
		$bakcsvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $filesafe_project . '.csv.bak';
		$tmpcsvpath = $this->outputdir . 'csv' . DIRECTORY_SEPARATOR . $filesafe_project . '.csv.tmp';

		// Save the previous csv.
		if ($write_csv && file_exists($csvpath)) {
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

		if ($write_csv) {
			$csvhndl = fopen($tmpcsvpath, 'wb');
			fwrite($csvhndl, '"Article","Importance","Class","Count","Oldest month","Categories"' . "\n");
		}

		$alphaurl = $this->urlpath . 'alpha/' . $filesafe_project . '.html';
		$alphapath = $this->outputdir . 'alpha' . DIRECTORY_SEPARATOR . $filesafe_project . '.html';
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
			<p>Cleanup listing for <a href=\"$projecturl\">{$wikiproject}{$project_title}</a> as of $asof_date.</p>
			<p>Of the $project_pages articles in this project $cleanup_pages or $artcleanpct % are marked for cleanup, with $issue_count issues in total.</p>
			<p>Listings: Alphabetic <b>&bull;</b> <a href=\"$bycaturl\">By Category</a> <b>&bull;</b> <a href=\"$csvurl\">CSV</a> <b>&bull;</b> <a href=\"$histurl\">History</a></p>
			<table id='myTable' class='wikitable'><thead><tr><th>Article</th><th>Importance</th><th>Class</th><th>Count</th>
				<th>Oldest</th><th class='unsortable'>Categories</th></tr></thead><tbody>
    		");

		foreach ($curclean as $title => &$art) {
			$arturl = 'https://en.wikipedia.org/wiki/' . str_replace(' ', '_', $title);
			$consolidated = $this->_consolidateCats($art[self::KEY_ISSUES]);
			$cats = implode(', ', $consolidated['issues']);
			$icount = count($art[self::KEY_ISSUES]);

			if ($write_csv) {
				fwrite($csvhndl, CSVString::format(array($title, $art[self::KEY_IMP], $art[self::KEY_CLS], $icount, $consolidated['earliest'], $cats)) . "\n");
			}

			$clssort = CreateTables::$CLASSES[$art[self::KEY_CLS]];
			$impsort = CreateTables::$IMPORTANCES[$art[self::KEY_IMP]];

			fwrite($alphahndl, "<tr><td><a href='$arturl'>$title</a></td><td data-sort-value='$impsort'>{$art[self::KEY_IMP]}</td>
				<td data-sort-value='$clssort'>{$art[self::KEY_CLS]}</td><td align='right'>$icount</td>
				<td data-sort-value='{$consolidated['earliestsort']}'>{$consolidated['earliest']}</td><td>$cats</td></tr>\n");

			$titles[$title]= array(self::KEY_CLS => $art[self::KEY_CLS],
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

		fwrite($alphahndl, "</tbody></table>Generated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot'>CleanupWorklistBot</a></body></html>");

		if ($write_csv) {
			fclose($csvhndl);
		}
		fclose($alphahndl);

		if ($wiki_too_big) {

			//
			// Write the By Cat list html style
			//

			// Group the cats
			$catgroups = array();
			ksort($groups);

			foreach ($groups as $cat => &$group) {
				if (isset(Categories::$parentCats[$cat])) $testcat = Categories::$parentCats[$cat];
				else $testcat = $cat;

				if (isset(Categories::$CATEGORIES[$testcat]['group'])) $catgroup = Categories::$CATEGORIES[$testcat]['group'];
				else $catgroup = 'General';

				if (! isset($catgroups[$catgroup])) $catgroups[$catgroup] = array();
				$catgroups[$catgroup][$cat] = $group;
			}
			unset($group);
			unset($groups);

			ksort($catgroups);


			$bycatpath = $this->outputdir . 'bycat' . DIRECTORY_SEPARATOR . $filesafe_project . '.html';
			$bycathndl = fopen($bycatpath, 'wb');

			fwrite($bycathndl, "<html><head>
				<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
	    		<title>Cleanup listing for {$wikiproject}{$project_title}</title>
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
				<p>Cleanup listing for <a href=\"$projecturl\">{$wikiproject}{$project_title}</a> as of $asof_date.</p>
				<p>Of the $project_pages articles in this project $cleanup_pages or $artcleanpct % are marked for cleanup, with $issue_count issues in total.</p>
				<p>Listings: <a href=\"$alphaurl\">Alphabetic</a> <b>&bull;</b> By Category <b>&bull;</b> <a href=\"$csvurl\">CSV</a> <b>&bull;</b> <a href=\"$histurl\">History</a></p>
	    		");

			//Write the TOC
			fwrite($bycathndl, "<div class='toc'><center>Contents</center>\n");
			fwrite($bycathndl, "<ul>\n");

			if (! empty($prevclean)) {
				fwrite($bycathndl, "<li><a href='#Changes since last update'>Changes since last update</a></li>\n");
				fwrite($bycathndl, "<ul>\n");
				$newarts = array_diff_key($curclean, $prevclean);
				$artcount = count($newarts);
				fwrite($bycathndl, "<li><a href='#New articles'>New articles ($artcount)</a></li>\n");

				$resarts = array_diff_key($prevclean, $curclean);
				$artcount = count($resarts);
				fwrite($bycathndl, "<li><a href='#Resolved articles'>Resolved articles ($artcount)</a></li>\n");
				fwrite($bycathndl, "</ul>\n");
			}

			foreach ($catgroups as $catgroup => &$cats) {
				fwrite($bycathndl, "<li><a href='#$catgroup'>$catgroup</a></li>\n");
				fwrite($bycathndl, "<ul>\n");

				foreach ($cats as $cat => &$arts) {
					$catlen = strlen($cat);
					$artcount = count($arts);
					fwrite($bycathndl, "<li><a href='#$cat'>$cat ($artcount)</a></li>\n");
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
					<th class='unsortable'>Categories</th></tr></thead><tbody>\n
					");

				foreach ($newarts as $title => &$art) {
					$consolidated = $this->_consolidateCats($art[self::KEY_ISSUES]);
					$artcats = implode(', ', $consolidated['issues']);
					$clssort = CreateTables::$CLASSES[$art[self::KEY_CLS]];
					$impsort = CreateTables::$IMPORTANCES[$art[self::KEY_IMP]];
					fwrite($bycathndl, "<tr><td><a href='$wikiprefix$title'>$title</a></td>
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
						<th class='unsortable'>Categories</th></tr></thead><tbody>\n
						");

				foreach ($resarts as $title => &$fields) {
					fwrite($bycathndl, "<tr><td><a href='$wikiprefix$title'>$title</a></td>
						<td>{$fields[0]}</td><td>{$fields[1]}</td><td>{$fields[4]}</td></tr>\n");
				}
				unset($fields);

				fwrite($bycathndl, "</tbody></table>\n");
			}

			// Write the cats
			foreach ($catgroups as $catgroup => &$cats) {
				fwrite($bycathndl, "<a name='$catgroup'></a><h2>$catgroup</h2>\n");

				foreach ($cats as $cat => &$arts) {
					$catlen = strlen($cat);
					$artcount = count($arts);
					fwrite($bycathndl, "<a name='$cat'></a><h3>$cat ($artcount)</h3>\n");
					fwrite($bycathndl, "<table class='wikitable tablesorter'><thead><tr><th>Article</th><th>Importance</th><th>Class</th><th>Count</th>
						<th>Oldest</th><th class='unsortable'>Categories</th></tr></thead><tbody>\n
						");

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

						fwrite($bycathndl, "<tr><td><a href='$wikiprefix$title'>$title</a></td>
							<td data-sort-value='{$art[self::KEY_IMPSORT]}'>{$art[self::KEY_IMP]}</td>
							<td data-sort-value='{$art[self::KEY_CLSSORT]}'>{$art[self::KEY_CLS]}</td><td align='right'>{$art[self::KEY_ICOUNT]}</td>
							<td data-sort-value='{$art[self::KEY_EARLIESTSORT]}'>{$art[self::KEY_EARLIEST]}</td><td>{$artcats}</td></tr>\n");
					}

					fwrite($bycathndl, "</tbody></table>\n");
				}
				unset($arts);
			}
			unset($cats);

			fwrite($bycathndl, "<br />Generated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot'>CleanupWorklistBot</a></body></html>");
			fclose($bycathndl);

			// Write stub wiki page

			$output = "<noinclude>__NOINDEX__</noinclude>Cleanup listing for [[Wikipedia:{$wikiproject}{$project}|{$wikiproject}{$project_title}]] as of $asof_date.\n\n";
			$output .= "Of the $project_pages articles in this project $cleanup_pages or $artcleanpct % are marked for cleanup, with $issue_count issues in total.\n\n";
			$output .= "Listings: [$alphaurl Alphabetic] <b>·</b> [$bycaturl By Category] <b>·</b> [$csvurl CSV] <b>·</b> [$histurl History]\n\n";
			$output .= "'''Note''': The listing is too large to fit in a wiki page. An alternate listing can be found [$bycaturl here].\n";

			$this->resultWriter->writeResults("User:CleanupWorklistBot/lists/$filesafe_project", $output, "most recent results, articles: $cleanup_pages, issues: $issue_count");

		} else {

			//
			// Write the By Cat list wiki style
			//

			$output = "<noinclude>__NOINDEX__</noinclude>Cleanup listing for [[Wikipedia:{$wikiproject}{$project}|{$wikiproject}{$project_title}]] as of $asof_date.\n\n";
			$output .= "Of the $project_pages articles in this project $cleanup_pages or $artcleanpct % are marked for cleanup, with $issue_count issues in total.\n\n";
			$output .= "Listings: [$alphaurl Alphabetic] <b>·</b> By Category <b>·</b> [$csvurl CSV] <b>·</b> [$histurl History]\n\n";
			$output .= "Sections can be transcluded using <nowiki>{{#lst:User:CleanupWorklistBot/lists/$filesafe_project|</nowiki>''section''<nowiki>}}</nowiki> Examples: <nowiki>{{#lst:User:CleanupWorklistBot/lists/$filesafe_project|Neutrality}}</nowiki>, <nowiki>{{#lst:User:CleanupWorklistBot/lists/$filesafe_project|Articles needing cleanup}}</nowiki>\n\n";

			// Write the changes
			if (! empty($prevclean)) {
				$output .= "==Changes since last update==\n<section begin='Changes since last update' />\n";

				$newarts = array_diff_key($curclean, $prevclean);
				$artcount = count($newarts);
				$output .= "===New articles ($artcount)===\n<section begin='New articles' />\n";
				$output .= "{| class='wikitable sortable'\n|-\n!Article!!Importance!!Class!!class='unsortable'|Categories\n";

				foreach ($newarts as $title => &$art) {
					$consolidated = $this->_consolidateCats($art[self::KEY_ISSUES]);
					$artcats = implode(', ', $consolidated['issues']);
					$clssort = CreateTables::$CLASSES[$art[self::KEY_CLS]];
					$impsort = CreateTables::$IMPORTANCES[$art[self::KEY_IMP]];
					$output .= "|-\n|[[$title]]||data-sort-value='{$impsort}'|{$art[self::KEY_IMP]} ||data-sort-value='{$clssort}'|{$art[self::KEY_CLS]} ||{$artcats}\n";
				}
				unset($art);
				$output .= "|}\n";
				$output .= "<section end='New articles' />\n";

				$resarts = array_diff_key($prevclean, $curclean);
				$artcount = count($resarts);
				$output .= "===Resolved articles ($artcount)===\n<section begin='Resolved articles' />\n";
				$output .= "{| class='wikitable sortable'\n|-\n!Article!!Importance!!Class!!class='unsortable'|Categories\n";

				foreach ($resarts as $title => &$fields) {
					$output .= "|-\n|[[$title]]||{$fields[0]} ||{$fields[1]} ||{$fields[4]}\n";
				}
				unset($fields);
				$output .= "|}\n";
				$output .= "<section end='Resolved articles' />\n";

				$output .= "<section end='Changes since last update' />\n";
			}

			// Group the cats
			$catgroups = array();
			ksort($groups);

			foreach ($groups as $cat => &$group) {
				if (isset(Categories::$parentCats[$cat])) $testcat = Categories::$parentCats[$cat];
				else $testcat = $cat;

				if (isset(Categories::$CATEGORIES[$testcat]['group'])) $catgroup = Categories::$CATEGORIES[$testcat]['group'];
				else $catgroup = 'General';

				if (! isset($catgroups[$catgroup])) $catgroups[$catgroup] = array();
				$catgroups[$catgroup][$cat] = $group;
			}
			unset($group);
			unset($groups);

			ksort($catgroups);

			foreach ($catgroups as $catgroup => &$cats) {
				$output .= "==$catgroup==\n<section begin='$catgroup' />\n";

				foreach ($cats as $cat => &$arts) {
					$catlen = strlen($cat);
					$artcount = count($arts);
					$output .= "===$cat ($artcount)===\n";
					$output .= "<section begin='$cat' />\n";
					$output .= "{| class='wikitable sortable'\n|-\n!Article!!Importance!!Class!!Earliest!!class='unsortable'|Categories\n";

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

						// a space is added after values that can be empty so ||| does not happen
						$output .= "|-\n|[[$title]]||data-sort-value='{$art[self::KEY_IMPSORT]}'|{$art[self::KEY_IMP]} ||data-sort-value='{$art[self::KEY_CLSSORT]}'|{$art[self::KEY_CLS]} ||data-sort-value='{$art[self::KEY_EARLIESTSORT]}'|{$art[self::KEY_EARLIEST]} ||{$artcats}\n";

						if (strlen($output) > $max_page_size) return false;
					}

					$output .= "|}\n";
					$output .= "<section end='$cat' />\n";
				}
				unset($arts);

				$output .= "<section end='$catgroup' />\n";
			}
			unset($cats);

			$output .= "\nGenerated by [[User:CleanupWorklistBot|CleanupWorklistBot]]\n";

			if (strlen($output) > $max_page_size) return false;

			$this->resultWriter->writeResults("User:CleanupWorklistBot/lists/$filesafe_project", $output, "most recent results, articles: $cleanup_pages, issues: $issue_count");
		}

        //
		// Write the history list
		//

		$sth = $this->dbh_tools->prepare("INSERT INTO history VALUES (?, ?, $project_pages, $cleanup_pages, $issue_count)");
		$histdate = sprintf('%d-%02d-%02d', $this->asof_date['year'], $this->asof_date['mon'], $this->asof_date['mday']);
		$sth->execute(array($project, $histdate));

		$histpath = $this->outputdir . 'history' . DIRECTORY_SEPARATOR . $filesafe_project . '.html';
		$histhndl = fopen($histpath, 'wb');
		fwrite($histhndl, "<html><head>
		<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
		<title>Cleanup history for {$wikiproject}{$project_title}</title>
		<link rel='stylesheet' type='text/css' href='../../css/cwb.css' />
		</head><body>
		<p>Cleanup history for <a href=\"$projecturl\">{$wikiproject}{$project_title}</a>.</p>
		<p>Listings: <a href=\"$alphaurl\">Alphabetic<a> <b>&bull;</b> <a href=\"$bycaturl\">By Category</a> <b>&bull;</b> <a href=\"$csvurl\">CSV</a> <b>&bull;</b> History</p>
		<table class='wikitable'><thead><tr><th>Date</th><th>Total articles</th><th>Cleanup articles</th><th>Cleanup issues</th></tr></thead><tbody>\n
		");

		$sth = $this->dbh_tools->prepare("SELECT * FROM history WHERE project = ? ORDER BY time");
		$sth->execute(array($project));

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			fwrite($histhndl, "<tr><td>{$row['time']}</td><td>{$row['total_articles']}</td><td>{$row['cleanup_articles']}</td><td>{$row['issues']}</td></tr>\n");
		}

		fwrite($histhndl, "</tbody></table>Generated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot'>CleanupWorklistBot</a></body></html>");
		fclose($histhndl);

		//Finished successfully
		rename($tmpcsvpath, $csvpath);

		$wikiproject = (($isWikiProject) ? 'WikiProject_' : '') . $project;

		$sth = $this->dbh_tools->prepare("DELETE FROM project WHERE `name` = ?");
		$sth->execute(array($wikiproject));
		$sth = $this->dbh_tools->prepare("INSERT INTO project VALUES (?, ?)");
		$sth->execute(array($wikiproject, $wiki_too_big ? 1 : 0));

		return true;
	}

	/**
	 * Consolidate issues that have multiple dates.
	 * Determine the earliest date.
	 *
	 * @param array $cat_ids index into $this->categories keys = 'title', 'mth', 'yr'
	 * @return array Earliest date 'earliest', 'issues', 'earliestsort' Consolidated issues, one string per category
	 */
	function _consolidateCats(&$cat_ids)
	{
		$results = array();
		$earliestyear = 9999;
		$earliestmonth = 99;

		foreach ($cat_ids as $cat_id) {
			$cat = str_replace('_', ' ', $this->categories[$cat_id][self::KEY_TITLE]);
			if (! isset($results[$cat])) {
				$results[$cat] = array();
			}

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