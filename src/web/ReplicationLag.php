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

use com_brucemyers\Util\Config;
use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = array();

get_params();

$replag = get_replag();

display_form($replag);

/**
 * Display replag
 *
 */
function display_form($replag)
{
	global $params;
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Database Replication Lag</title>
    	<style type="text/css">
table.form {
	background: #fff;
	border:1px solid #ccc;
	color: #333;
	margin-bottom: 10px;
	border-radius: 10px;
	padding: 5px;
}
th.form {
	background: #f2f2f2;
	border:1px solid #bbb;
	border-top: 1px solid #fff;
	border-left: 1px solid #fff;
	text-align: center;
}
td.form {
	padding: 0 10px;
}
    </style>
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2>Database Replication Lag</h2>
        <form action="ReplicationLag.php" method="post">
        <table class="form">
        <tr><td><b>Wiki</b></td><td><input id="wiki" name="wiki" type="text" size="4" value="<?php echo $params['wiki'] ?>" /></td></tr>
        <tr><td colspan='2'><input type="submit" value="Submit" /></td></tr>
        </table>
        </form>
        <br />
        <b>Replication lag</b>: <?php echo $replag['replag'] ?><br /><br />
        <b>Most recent change</b>: <?php echo $replag['lastupdate'] ?> UTC
        <script type="text/javascript">
            if (document.getElementById) {
                var e = document.getElementById('wiki');
                e.focus();
                e.select();
            }
        </script>
        </div><br /><div style="display: table; margin: 0 auto;">
    Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99">Bamyers99</a></div></body></html><?php
}

function get_replag()
{
	global $params;
	$return = array('replag' => 'Error retrieving lag', 'lastupdate' => '');

	$wikiname = $params['wiki'];
	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";

	try {
		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8", $user, $pass);
		$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$sth = $dbh_wiki->query('SELECT MAX(rc_timestamp), UNIX_TIMESTAMP() - UNIX_TIMESTAMP(MAX(rc_timestamp)) FROM recentchanges');

		if ($row = $sth->fetch(PDO::FETCH_NUM)) {
			$lastupdate = date($row[0]);
			$seconds = (int)$row[1];

	        $days = floor($seconds / 86400);
	        $seconds -= $days * 86400;
	        $hours = floor($seconds / 3600);
	        $seconds -= $hours * 3600;
	        $minutes = floor($seconds / 60);
	        $seconds -= $minutes * 60;

	        $replag = '';
	        if ($days > 0) {
				$replag .= " $days ";
				$replag .= ($days == 1) ? 'day' : 'days';
    		}

    		if ($days > 0 || $hours > 0) {
    			$replag .= " $hours ";
    			$replag .= ($hours == 1) ? 'hour' : 'hours';
    		}

    		if ($days > 0 || $hours > 0 || $minutes > 0) {
    			$replag .= " $minutes ";
    			$replag .= ($minutes == 1) ? 'minute' : 'minutes';
    		}

 			$replag .= " $seconds ";
			$replag .= ($seconds == 1) ? 'second' : 'seconds';

			$replag = trim($replag);

			$return = array('replag' => $replag, 'lastupdate' => substr($lastupdate, 0, 4) . '-' . substr($lastupdate, 4 , 2) .
				'-' . substr($lastupdate, 6, 2) . ' ' . substr($lastupdate, 8, 2) . ':' . substr($lastupdate, 10, 2) . ':' .
				substr($lastupdate, 12, 2));
		}
	} catch (PDOException $ex) {
	}

	return $return;
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = array();

	$params['wiki'] = isset($_REQUEST['wiki']) ? $_REQUEST['wiki'] : 'enwiki';
}

?>