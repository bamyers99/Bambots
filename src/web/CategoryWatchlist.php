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

use com_brucemyers\CategoryWatchlistBot\UIHelper;
use com_brucemyers\Util\MySQLDate;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CategoryWatchlistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
define('COOKIE_QUERYID', 'catwl:queryid');

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
	setcookie(COOKIE_QUERYID, $hash, strtotime('+60 days'));
	$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
	header("Location: $protocol://$host$uri/$extra", true, 302);
	exit;
}

display_form();

/**
 * Display new pages for a user
 *
 */
function display_form()
{
	global $uihelper, $params, $wikis, $options;
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Category Watchlist<?php if (! empty($params['cn1'])) echo ' : ' . htmlentities($params['cn1'], ENT_COMPAT, 'UTF-8'); ?></title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
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
		</script>
	</head>
	<body>
		<script type='text/javascript'>
			$(document).ready(function()
			    {
		        $('.tablesorter').tablesorter();
			    }
			);
		</script>
		<div style="display: table; margin: 0 auto;">
		<h2>Category Watchlist <span style="font-size: 75%">(additions only)</span></h2>
		<?php
		if ($params['catcount'] && isset($options['query'])) {
			echo '<div>';
			echo '<span id="expandericon1" class="expandericon"><a href="#" onclick="toggleExpander(\'1\'); return false">+</a></span><a class="expandertitle" href="#" onclick="toggleExpander(\'1\'); return false"> Show categories</a>';
 			echo '</div>';
 			echo '<div id="expanderbody1" style="display: none">';
		}
		?>
        <form action="CategoryWatchlist.php" method="post"><table class="form">
        <tr><td colspan='3'><b>Wiki</b> <select name="wiki" id="testfield1"><?php
        foreach ($wikis as $wikiname => $wikidata) {
			$wikititle = htmlentities($wikidata['title'], ENT_COMPAT, 'UTF-8');
			$selected = '';
			if ($wikiname == $params['wiki']) $selected = ' selected="1"';
			echo "<option value='$wikiname'$selected>$wikititle</option>";
		}
        ?></select></td></tr>
        <tr><td colspan='3'><b>Days</b> <input name="days" type="text" size="2" value="<?php echo $params['days'] ?>" />
        	<?php echo $uihelper->max_watch_days ?> days maximum</td></tr>
        <tr><td>&nbsp;</td><td><b>Categories</b> (minimum 1)</td><td><b>Sub-category depth (0-10)</b></td></tr>
        <?php
        	for ($x=1; $x <= 10; ++$x) {
				echo "<tr><td><b>$x</b></td><td><input name='cn$x' type='text' size='30' value='" .
					htmlentities($params["cn$x"], ENT_COMPAT, 'UTF-8') .
        			"' /></td><td><input name='sd$x' type='text' size='2' value='{$params["sd$x"]}' /></td></tr>";
			}
        	?>
        <tr><td colspan='3'><input type="submit" value="Submit" /></td></tr>
        </table></form>

        <script type="text/javascript">
            if (document.getElementById) {
                document.getElementById('testfield1').focus();
            }
        </script>
    <?php
    if ($params['catcount'] && isset($options['query'])) {
		echo '</div>';
	}

    if ($params['catcount'] && isset($options['query']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
        display_diffs();
    }

    ?></div><br /><div style="display: table; margin: 0 auto;">
    <a href="https://en.wikipedia.org/wiki/User:Bamyers99/CategoryWatchlist">Documentation</a> <b>&bull;</b>
    Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99">Bamyers99</a></div></body></html><?php
}

/**
 * Display category differences
 */
function display_diffs()
{
	global $uihelper, $params, $wikis, $options;

	$results = $uihelper->getResults($params);
	if (empty($results['results'])) $results['errors'][] = 'No results';

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (! empty($results['results'])) {
		$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
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

		foreach ($dategroups as $date => &$dategroup) {
			usort($dategroup, 'resultgroupsort');
			$date = date('F j, Y', MySQLDate::toPHP($date));
			echo "<h3>$date</h3>";
			echo "<table class='wikitable tablesorter'><thead><tr><th>Page</th><th>Categories</th></tr></thead><tbody>\n";
			$x = 0;
			$prevtitle = '';

			foreach ($dategroup as &$result) {
				$title = $result['title'];
				$category = htmlentities($result['category'], ENT_COMPAT, 'UTF-8');

				if ($title == $prevtitle) {
					echo ", $category";
				} else {
					if ($x++ > 0) echo "</td></tr>\n";
					echo "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
						htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a></td>
						<td>$category";
				}
				$prevtitle = $title;
			}

			echo "</td></tr>\n";

			echo "</tbody></table>\n";
			echo '<div>Results include category additions and sort key updates.</div>';
			echo "<div>Categories watched: {$results['catcount']}</div>";

			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = "CategoryWatchlist.php?action=atom&amp;query={$options['hash']}";
			$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

			echo "<div><a href='$protocol://$host$uri/$extra'><img src='img/icon-atom.gif' title='Subscribe to updates' /></a></div>";
		}
		unset($dategroup);
		unset($result);
	}
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params, $wikis, $uihelper, $options;

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

	$params['wiki'] = isset($_REQUEST['wiki']) ? $_REQUEST['wiki'] : '';
	if (! isset($wikis[$params['wiki']])) $params['wiki'] = 'enwiki';

	$days = isset($_REQUEST['days']) ? trim($_REQUEST['days']) : 1;
	if (! is_numeric($days)) $days = 1;
	$days = (int)$days;
	if ($days > $uihelper->max_watch_days) $days = $uihelper->max_watch_days;
	elseif ($days < 1) $days = 1;
	$params['days'] = $days;

	$catcount = 0;
	$cats = array();

	for ($x=1; $x <= 10; ++$x) {
		$fieldname = "cn$x";
		$catname = isset($_REQUEST[$fieldname]) ? ucfirst(trim($_REQUEST[$fieldname])) : '';
		$catname = str_replace('_', ' ', $catname);
		if (strpos($catname, 'Category:') === 0) $catname = ucfirst(substr($catname, 9));

		$fieldname = "sd$x";
		$subdepth = isset($_REQUEST[$fieldname]) ? trim($_REQUEST[$fieldname]) : 0;
		if (! is_numeric($subdepth)) $subdepth = 0;
		$subdepth = (int)$subdepth;
		if ($subdepth > 10) $subdepth = 10;
		elseif ($subdepth < 0) $subdepth = 0;

		if (! empty($catname)) $cats[$catname] = $subdepth;
	}

	ksort($cats);

	foreach ($cats as $catname => $subdepth) {
		++$catcount;
		$params["cn$catcount"] = $catname;
		$params["sd$catcount"] = $subdepth;
	}

	for ($x=$catcount; $x < 10;) {
		++$x;
		$params["cn$x"] = '';
		$params["sd$x"] = 0;
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
		$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
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
	$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

	header("Location: $protocol://$host$uri/$extra", true, 302);
}
?>