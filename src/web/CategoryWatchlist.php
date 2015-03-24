<?php
/**
 Copyright 2015 Myers Enterprises II

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

use com_brucemyers\CategoryWatchlistBot\UIHelper;
use com_brucemyers\Util\MySQLDate;
use com_brucemyers\Util\DateUtil;
use com_brucemyers\Util\HttpUtil;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CategoryWatchlistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
define('COOKIE_QUERYID', 'catwl:queryid');

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$uihelper = new UIHelper();
$wikis = $uihelper->getWikis();
$params = array();
$options = array();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'atom':
		$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';
    	if (empty($query)) break;
    	if (! $uihelper->generateAtom($query)) break;
    	exit;

    case 'admin':
    	display_admin();
    	exit;

    case 'approve':
    	query_approve_deny(-4);
    	exit;

    case 'deny':
    	query_approve_deny(-3);
    	exit;
}

get_params();

// Redirect to get the results so have a bookmarkable url
if ($params['catcount'] && ! isset($options['query']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$serialized = serialize($params);
	$hash = md5($serialized);
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'CategoryWatchlist.php?query=' . $hash;
	setcookie(COOKIE_QUERYID, $hash, strtotime('+180 days'));
	$protocol = HttpUtil::getProtocol();
	header("Location: $protocol://$host$uri/$extra", true, 302);
	exit;
}

display_form();

/**
 * Display form
 *
 */
function display_form()
{
	global $uihelper, $params, $wikis, $options;
	$title = '';
	if (! empty($params['title'])) $title = ' : ' . $params['title'];
	else if (!empty($params['cn1'])) $title = ' : ' . $params['cn1'];
	$title = htmlentities($title, ENT_COMPAT, 'UTF-8');
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Category / Template Watchlist<?php echo $title?></title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
	    <style>
	        .plusminus {
                text-align: center;
            }
			table tr td.tabborderr {
				border-right: 2px solid #aaa;
			}
		</style>
    	<script type='text/javascript' src='js/jquery-2.1.1.min.js'></script>
		<script type='text/javascript' src='js/jquery.tablesorter.min.js'></script>
		<script type='text/javascript'>
			function toggleExpander(id) {
				var ei = $('#expandericon' + id);
				if (ei == null) return;
				ei = ei.children(":first-child");
				var value = (ei.contents().first().text() == '+') ? '-&nbsp;' : '+';
				ei.html(value);
				$('#expanderbody' + id).toggle();
			}
			function clearForm(form) {
				form.title.value = '';
	        	for (var x=1; x <= 10; ++x) {
		        	form['cn'+x].value = '';
		        	form['mt'+x][0].checked = true;
		        	form['mt'+x][1].checked = false;
		        	form['rt'+x][0].checked = true;
		        	form['rt'+x][1].checked = false;
		        	form['rt'+x][2].checked = false;
		        }
			}
		</script>
	</head>
	<body>
		<script type='text/javascript'>
			$(document).ready(function()
			    {
		        $('.tablesorter').tablesorter({ headers: { 1: {sorter:"text"} } });
			    }
			);
		</script>
		<div style="display: table; margin: 0 auto;">
		<h2><a href="CategoryWatchlist.php<?php if (! empty($options['hash'])) echo "?query={$options['hash']}" ?>" class="novisited">Category / Template Watchlist</a></h2>
		<?php
		if ($params['catcount'] && isset($options['query'])) {
			echo '<div>';
			echo '<span id="expandericon1" class="expandericon"><a href="#" onclick="toggleExpander(\'1\'); return false">+</a></span><a class="expandertitle" href="#" onclick="toggleExpander(\'1\'); return false"> Show categories / templates</a>';
 			echo '</div>';
 			echo '<div id="expanderbody1" style="display: none">';
		}
		?>
		<div>Categories that are included by templates are not watched. Watch the template instead.</div>
        <form action="CategoryWatchlist.php" method="post"><table class="form">
        <tr><td colspan='3'><b>Title</b> <input name="title" id="testfield1" type="text" size="40" value="<?php echo $params['title'] ?>" /></td></tr>
        <tr><td colspan='3'><b>Wiki</b> <select name="wiki"><?php
        foreach ($wikis as $wikiname => $wikidata) {
			$wikititle = htmlentities($wikidata['title'], ENT_COMPAT, 'UTF-8');
			$selected = '';
			if ($wikiname == $params['wiki']) $selected = ' selected="1"';
			echo "<option value='$wikiname'$selected>$wikititle</option>";
		}
        ?></select></td></tr>
        <tr><td>&nbsp;</td><td><b>Categories / Templates</b> (enclose in {{ }})</td>
        	<td style="text-align:center;"><b>Match type</b></td><td style="text-align:center;"><b>Report changes</b></td></tr>
        <?php
        	for ($x=1; $x <= 10; ++$x) {
				$checkedRTB = '';
				$checkedRTP = '';
				$checkedRTM = '';
				if ($params["rt$x"] == 'P') $checkedRTP = " checked='1'";
				elseif ($params["rt$x"] == 'M') $checkedRTM = " checked='1'";
				else $checkedRTB = " checked='1'";

				$checkedMTE = '';
				$checkedMTP = '';
				if ($params["mt$x"] == 'E') $checkedMTE = " checked='1'";
				else $checkedMTP = " checked='1'";

				$catname = $params["cn$x"];
				if ($params["pt$x"] == 'T') $catname = '{{' . $catname . '}}';

				echo "<tr><td><b>$x</b></td><td><input name='cn$x' type='text' size='30' value='" .
					htmlentities($catname, ENT_COMPAT, 'UTF-8') . "' /></td>" .
	       			"<td class='tabborderr'>Exact <input type='radio' name='mt$x' value='E'$checkedMTE> Partial <input type='radio' name='mt$x' value='P'$checkedMTP</td>" .
        			"<td>Both <input type='radio' name='rt$x' value='B'$checkedRTB> Add <input type='radio' name='rt$x' value='P'$checkedRTP> Remove <input type='radio' name='rt$x' value='M'$checkedRTM></td></tr>";
			}
        	?>
        <tr><td colspan='3'><input type="submit" value="Save" />&nbsp;&nbsp;&nbsp;<input type="submit" value="New" onclick="clearForm(this.form);return false;"/></td></tr>
        </table></form>

        <script type="text/javascript">
            if (document.getElementById) {
                document.getElementById('testfield1').focus();
            }
        </script>
    <?php
    if ($params['catcount'] && isset($options['query'])) {
		echo '<div>If any parameters above are changed, the query id in the url and atom feed will change.</div>';
		echo '<dl><dt><b>Categories / Templates</b></dt><dd>Enclose templates in {{ }}</dd><dd>Categories that are included by templates are not watched (ie. stubs, cleanup, WikiProject). Watch the template instead.</dd><dd>Template redirects are followed.</dd></dl>';
		echo '<dl><dt><b>Match type</b></dt><dd>Exact = Specified category/template must match exactly.</dd><dd>Partial = Specified category/template can match any part.</dd></dl>';
		echo '<dl><dt><b>Report changes</b></dt><dd>Both = Report additions and removals.</dd><dd>Add = Report only additions.</dd><dd>Remove = Report only removals.</dd></dl>';
		echo '</div>';
	}

    if ($params['catcount'] && isset($options['query']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
        display_diffs();
    }

    ?></div><br /><div style="display: table; margin: 0 auto;">
    <a href="RecentCategoryChanges.php" class='novisited'>Recent Category Changes</a> <b>&bull;</b>
    <a href="https://en.wikipedia.org/wiki/User:CategoryWatchlistBot" class='novisited'>Documentation</a> <b>&bull;</b>
    Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99" class='novisited'>Bamyers99</a></div></body></html><?php
}

/**
 * Display category differences
 */
function display_diffs()
{
	global $uihelper, $params, $wikis, $options;

	$results = $uihelper->getResults($params, $options['page'], 100);
	if (empty($results['results'])) $results['errors'][] = 'No more results';

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (! empty($results['results'])) {
		$protocol = HttpUtil::getProtocol();
		$domain = $wikis[$params['wiki']]['domain'];
		$wikiprefix = "$protocol://$domain/wiki/";

		// Sort by date, namespace, title
		$dategroups = array();
		foreach ($results['results'] as &$result) {
			$date = $result['diffdate'];
			unset($result['diffdate']);
			if (! isset($dategroups[$date])) $dategroups[$date] = array();
			$dategroups[$date][] = $result;
		}
		unset($result);

		echo "<table class='wikitable tablesorter'><thead><tr><th>Page</th><th>+/&ndash;</th><th>Category / Template</th></tr></thead><tbody>\n";

		foreach ($dategroups as $date => &$dategroup) {
			usort($dategroup, 'resultgroupsort');
			$displaydate = date('F j, Y G', MySQLDate::toPHP($date));
			$ord = DateUtil::ordinal(date('G', MySQLDate::toPHP($date)));
			echo "<tr><td data-sort-value='~'><i>$displaydate$ord hour</i></td><td data-sort-value='~'>&nbsp;</td><td data-sort-value='~'>&nbsp;</td>\n";
			$x = 0;
			$prevtitle = '';
			$prevaction = '';

			foreach ($dategroup as &$result) {
				$title = $result['title'];
				$action = $result['plusminus'];
				$category = htmlentities($result['category'], ENT_COMPAT, 'UTF-8');
				if ($result['cat_template'] == 'T') $category = '{{' . $category . '}}';

				if ($title == $prevtitle && $action == $prevaction) {
					echo "; $category";
				} else {
					$displayaction = ($action == '-') ? '&ndash;' : $action;
					if ($x++ > 0) echo "</td></tr>\n";
					echo "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
						htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a></td><td class='plusminus' data-sort-value='$action'>$displayaction</td><td>$category";
				}
				$prevtitle = $title;
				$prevaction = $action;
			}

			if ($x > 0) echo "</td></tr>\n";

		}

		echo "</tbody></table>\n";
		unset($dategroup);
		unset($result);

		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$protocol = HttpUtil::getProtocol();

		if (count($results['results']) == 100) {
			$extra = "CategoryWatchlist.php?query={$options['hash']}&amp;page=" . ($options['page'] + 1);
			echo "<div style='padding-bottom: 5px;' class='novisited'><a href='$protocol://$host$uri/$extra'>Next page</a></div>";
		}

		echo '<div style="padding-bottom: 5px;">+ = Added<br />&ndash; = Removed</div>';

		$extra = "CategoryWatchlist.php?action=atom&amp;query={$options['hash']}";
		echo "<div><a href='$protocol://$host$uri/$extra'><img src='img/icon-atom.gif' title='Subscribe to updates' /></a></div>";
	}
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params, $wikis, $uihelper, $options;

	$options['page'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';

	if (! isset($_REQUEST['wiki']) && (isset($_REQUEST['query']) || isset($_COOKIE[COOKIE_QUERYID]))) {
		if (isset($_REQUEST['query'])) $hash = $_REQUEST['query'];
		else $hash = $_COOKIE[COOKIE_QUERYID];

		$params = $uihelper->fetchParams($hash);
		if (! empty($params)) {
			if (isset($_REQUEST['query'])) $options['query'] = true;
			$options['hash'] = $hash;
			return;
		}
	}

	$params = array();

	$params['title'] = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';

	$params['wiki'] = isset($_REQUEST['wiki']) ? $_REQUEST['wiki'] : '';
	if (! isset($wikis[$params['wiki']])) $params['wiki'] = 'enwiki';

	$catcount = 0;
	$cats = array();
	$pagetypes = array('C','T');
	$matchtypes = array('E','P');
	$reporttypes = array('B','P','M');

	for ($x=1; $x <= 10; ++$x) {
		$fieldname = "cn$x";
		$catname = isset($_REQUEST[$fieldname]) ? ucfirst(trim($_REQUEST[$fieldname])) : '';
		$catname = str_replace('_', ' ', $catname);
		if (strpos($catname, 'Category:') === 0) $catname = ucfirst(substr($catname, 9));

		$templatefound = false;
		if (strpos($catname, 'Template:') === 0) {
			$catname = ucfirst(substr($catname, 9));
			$templatefound = true;
		}

		$pagetype = 'C';
		if (preg_match('/{{\\s*(.*)\\s*}}/', $catname, $matches)) {
			$catname = $matches[1];
			$templatefound = true;
		}

		if ($templatefound) {
			$pagetype = 'T';
			$catname = $uihelper->processTemplateRedirect($params['wiki'], $catname);
		}

		$fieldname = "mt$x";
		$matchtype = isset($_REQUEST[$fieldname]) ? $_REQUEST[$fieldname] : 'E';
		if (! in_array($matchtype, $matchtypes)) $matchtype = 'E';

		$fieldname = "rt$x";
		$reporttype = isset($_REQUEST[$fieldname]) ? $_REQUEST[$fieldname] : 'B';
		if (! in_array($reporttype, $reporttypes)) $reporttype = 'B';

		if (! empty($catname)) $cats[$catname] = array('pt' => $pagetype, 'mt' => $matchtype, 'rt' => $reporttype);
	}

	foreach ($cats as $catname => $sdrt) {
		++$catcount;
		$params["cn$catcount"] = $catname;
		$params["pt$catcount"] = $sdrt['pt'];
		$params["mt$catcount"] = $sdrt['mt'];
		$params["rt$catcount"] = $sdrt['rt'];
	}

	for ($x=$catcount; $x < 10;) {
		++$x;
		$params["cn$x"] = '';
		$params["pt$x"] = 'C';
		$params["mt$x"] = 'E';
		$params["rt$x"] = 'B';
	}

	$params['catcount'] = $catcount;

	if ($catcount) $uihelper->saveQuery($params);
}

/**
 * Sort a result group by namespace, title
 *
 * @param unknown $a
 * @param unknown $b
 * @return number
 */
function resultgroupsort($a, $b)
{
	$ans = $a['ns'];
	$bns = $b['ns'];

	if ($ans > $bns) return 1;
	if ($ans < $bns) return -1;
	return strcmp($a['title'], $b['title']);
}

/**
 * Display admin page
 */
function display_admin()
{
	global $uihelper;
	$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : '';
	if (empty($pass)) return;
	if (! $uihelper->checkPassword($pass)) return;
	/*
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<title>Category Watchlist : Admin</title>
		<link rel='stylesheet' type='text/css' href='css/catwl.css' />
	</head>
	<body><table class="wikitable"><tr><th>Wiki</th><th>Query</th><th>Approve</th><th>Deny</th></tr>
		<?php
		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$protocol = HttpUtil::getProtocol();
		$baseurl = "$protocol://$host$uri/CategoryWatchlist.php?";
		$pass = urlencode($pass);

		$results = $uihelper->getUnapproveds();

		foreach ($results as $row) {
			echo "<tr><td>{$row['wikiname']}</td><td><a href='{$baseurl}query={$row['hash']}'>query</a></td>" .
				"<td><a href='{$baseurl}action=approve&amp;query={$row['hash']}&amp;pass=$pass'>approve</a></td>" .
				"<td><a href='{$baseurl}action=deny&amp;query={$row['hash']}&amp;pass=$pass'>deny</a></td></tr>";
		}

	</table></body>
		<?php
		*/
}

/**
 * Approve or deny a query
 *
 * @param int $status
 */
function query_approve_deny($status)
{
	global $uihelper;
	$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : '';
	if (empty($pass)) return;
	if (! $uihelper->checkPassword($pass)) return;
	$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';

	$uihelper->setQueryStatus($query, $status);

	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$pass = urlencode($pass);
	$extra = 'CategoryWatchlist.php?action=admin&pass=' . $pass;
	$protocol = HttpUtil::getProtocol();

	header("Location: $protocol://$host$uri/$extra", true, 302);
}
?>