<?php
/**
 Copyright 2020 Myers Enterprises II

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
use com_brucemyers\Util\Convert;
use com_brucemyers\Util\HttpUtil;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CategoryWatchlistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
define('COOKIE_QUERYID', 'dumpwl:queryid');

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$uihelper = new UIHelper();
$params = [];
$options = [];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'atom':
		$query = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';
    	if (empty($query)) break;
    	if (! $uihelper->generateDumpAtom($query)) break;
    	exit;
}

get_params();

// Redirect to get the results so have a bookmarkable url
if ($params['filecount'] && ! isset($options['query']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$serialized = serialize($params);
	$hash = md5($serialized);
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'DumpWatchlist.php?query=' . $hash;
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
	global $uihelper, $params, $options;

	if (isset($options['query'])) {
	    $results = $uihelper->getDumpResults($params)['results'];
	}

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Wikimedia Dump Watchlist</title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
	    <style>
			table tr td.tabborderr {
				border-right: 2px solid #aaa;
				padding: 0px 5px;
			}
		</style>
		<script type='text/javascript'>
			function clearForm(form) {
				form.title.value = '';
	        	for (var x=1; x <= 10; ++x) {
		        	form['wn'+x].value = '';
		        	form['fn'+x].value = '';
		        	form['rt'+x][0].checked = true;
		        	form['rt'+x][1].checked = false;
		        	form['rt'+x][2].checked = false;
		        }
			}
		</script>
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2><a href="DumpWatchlist.php<?php if (! empty($options['hash'])) echo "?query={$options['hash']}" ?>" class="novisited">Wikimedia Dump Watchlist</a></h2>
        <form action="DumpWatchlist.php" method="post"><table class="form">
        <tr><td>&nbsp;</td><td><b>Wiki</b> (e.g. enwiki)</td>
        	<td style="text-align:center;"><b>Filename</b> (e.g. pages-articles.xml.bz2)</td><td style="text-align:center;"><b>Dump day(s)</b></td><td style="text-align:center;"><b>Last dump</b></td><td><b>Filesize</b></td></tr>
        <?php
        	for ($x=1; $x <= 10; ++$x) {
				$checkedRTB = '';
				$checkedRT1 = '';
				$checkedRT20 = '';
				if ($params["rt$x"] == '1') $checkedRT1 = " checked='1'";
				elseif ($params["rt$x"] == '2') $checkedRT20 = " checked='1'";
				else $checkedRTB = " checked='1'";

				$wikiname = $params["wn$x"];
				$filename = $params["fn$x"];

				$resultkey = "$wikiname\t$filename";
				$lastdump = '';
				$filesize = '';

				if (isset($results[$resultkey])) {
				    $lastdump = 'waiting';
				    $filesize = 'waiting';
				    $file = $results[$resultkey];
				    if (! is_null($file['lastdump'])) $lastdump = $file['lastdump'];
				    if (! is_null($file['filesize'])) {
				        $filesize = Convert::formatBytes($file['filesize']);
				        $filesize = $filesize[0] . ' ' . $filesize[1] . 'B';
				    }
				}

				echo "<tr><td><b>$x</b></td><td><input name='wn$x' type='text' size='15' value='" .
				    htmlentities($wikiname, ENT_COMPAT, 'UTF-8') . "' /></td><td><input name='fn$x' type='text' size='30' value='" .
				    htmlentities($filename, ENT_COMPAT, 'UTF-8') . "' /></td>" .
        			"<td class='tabborderr'>1st<input type='radio' name='rt$x' value='1'$checkedRT1 />&nbsp;&nbsp;20th<input type='radio' name='rt$x' value='2'$checkedRT20 />&nbsp;&nbsp;Both<input type='radio' name='rt$x' value='B'$checkedRTB /></td><td class='tabborderr'>$lastdump</td><td>$filesize</td></tr>";
			}
        	?>
        <tr><td colspan='4'><input type="submit" value="Save" />&nbsp;&nbsp;&nbsp;<input type="submit" value="New" onclick="clearForm(this.form);return false;"/></td></tr>
        </table></form>
    <?php
    echo '<div>If any parameters above are changed, the query id in the url and atom feed will change.</div>';
    echo '<div>Update frequency: hourly</div>';

	if (isset($options['query'])) {
	    $host  = $_SERVER['HTTP_HOST'];
	    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	    $protocol = HttpUtil::getProtocol();
	    $extra = "DumpWatchlist.php?action=atom&amp;query={$options['hash']}";
	    echo "<div><a href='$protocol://$host$uri/$extra'><img src='img/icon-atom.gif' title='Subscribe to updates' /></a></div>";
	}

    ?></div><br /><div style="display: table; margin: 0 auto;">
    <a href="/privacy.html">Privacy Policy</a> <b>&bull;</b>
    Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99" class='novisited'>Bamyers99</a></div></body></html><?php
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params, $uihelper, $options;

	if (! isset($_REQUEST['wn1']) && (isset($_REQUEST['query']) || isset($_COOKIE[COOKIE_QUERYID]))) {
		if (isset($_REQUEST['query'])) $hash = $_REQUEST['query'];
		else $hash = $_COOKIE[COOKIE_QUERYID];

		$params = $uihelper->fetchParams($hash, 'dump');
		if (! empty($params)) {
			if (isset($_REQUEST['query'])) $options['query'] = true;
			$options['hash'] = $hash;
			return;
		}
	}

	$params = [];
	$files = [];
	$filecnt = 0;

	$reporttypes = ['1','2','B'];

	for ($x=1; $x <= 10; ++$x) {
	    $fieldname = "wn$x";
	    $wikiname = isset($_REQUEST[$fieldname]) ? trim($_REQUEST[$fieldname]) : '';

	    $fieldname = "fn$x";
	    $filename = isset($_REQUEST[$fieldname]) ? trim($_REQUEST[$fieldname]) : '';

		$fieldname = "rt$x";
		$reporttype = isset($_REQUEST[$fieldname]) ? $_REQUEST[$fieldname] : '1';
		if (! in_array($reporttype, $reporttypes)) $reporttype = '1';

		if (! empty($wikiname) && ! empty($filename)) $files["$wikiname\t$filename"] = ['wn' => $wikiname, 'fn' => $filename, 'rt' => $reporttype];
	}

	foreach ($files as $file) {
	    ++$filecnt;
	    $params["wn$filecnt"] = $file['wn'];
	    $params["fn$filecnt"] = $file['fn'];
	    $params["rt$filecnt"] = $file['rt'];
	}

	for ($x=$filecnt; $x < 10;) {
		++$x;
		$params["wn$x"] = '';
		$params["fn$x"] = '';
		$params["rt$x"] = '1';
	}

	$params['filecount'] = $filecnt;

	if ($filecnt) $uihelper->saveDumpQuery($params);
}
?>