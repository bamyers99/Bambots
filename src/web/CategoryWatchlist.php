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

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CategoryWatchlistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$uihelper = new UIHelper();
$wikis = $uihelper->getWikis();

get_params();

display_form();

/**
 * Display new pages for a user
 *
 */
function display_form()
{
	global $uihelper, $params, $wikis;
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Category Watchlist</title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
		<script type='text/javascript' src='js/jquery-2.1.1.min.js'></script>
		<script type='text/javascript' src='js/jquery.tablesorter.min.js'></script>
	</head>
	<body>
		<script type='text/javascript'>
			$(document).ready(function()
			    {
		        $('.tablesorter').tablesorter();
			    }
			);
		</script>
		<h2>Category Watchlist</h2>
        <form action="CategoryWatchlist.php" method="post"><table class="form">
        <tr><td colspan='3'><b>Wiki</b> <select name="wiki" id="testfield1"><?php
        foreach ($wikis as $wikiname => $wikititle) {
			$wikititle = htmlentities($wikititle, ENT_COMPAT, 'UTF-8');
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

    if ($params['catcount'] && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
        display_diffs();
    }

    ?></body></html><?php
}

/**
 * Display category differences
 *
 */
function display_diffs()
{
	global $uihelper, $params;

	$results = $uihelper->getResults($params);
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params, $wikis, $uihelper;

	if (isset($_REQUEST['query'])) {
		$params = $uihelper->fetchParams($_REQUEST['query']);
		if (! empty($params)) return;
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

	for ($x=1; $x <= 10; ++$x) {
		$fieldname = "cn$x";
		$params[$fieldname] = isset($_REQUEST[$fieldname]) ? trim($_REQUEST[$fieldname]) : '';
		if (! empty($params[$fieldname])) ++$catcount;

		$fieldname = "sd$x";
		$subdepth = isset($_REQUEST[$fieldname]) ? trim($_REQUEST[$fieldname]) : 0;
		if (! is_numeric($subdepth)) $subdepth = 1;
		$subdepth = (int)$subdepth;
		if ($subdepth > 10) $subdepth = 10;
		elseif ($subdepth < 0) $subdepth = 0;
		$params[$fieldname] = $subdepth;
	}

	$params['catcount'] = $catcount;
}