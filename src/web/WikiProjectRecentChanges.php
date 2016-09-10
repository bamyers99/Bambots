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

use com_brucemyers\Util\Config;
use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\HttpUtil;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = array();

get_params();

// Redirect to get the results so have a bookmarkable url
if (isset($_POST['tpcat1']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'WikiProjectRecentChanges.php?tpcat1=' . urlencode($params['tpcat1']);
	if (! empty($params['tpcat2'])) $extra .= '&tpcat2=' . urlencode($params['tpcat2']);
	if ($params['bots'] == '1') $extra .= '&bots=1';
	if ($params['anonymous'] == '0') $extra .= '&anonymous=0';
	if ($params['registered'] == '0') $extra .= '&registered=0';

	$protocol = HttpUtil::getProtocol();
	header("Location: $protocol://$host$uri/$extra", true, 302);
	exit;
}

$changes = get_changes();

display_form($changes);

/**
 * Display changes
 *
 */
function display_form($changes)
{
	global $params;
	$title = '';
	if (! empty($params['tpcat1'])) $title .= ' - ' . $params['tpcat1'];
	if (! empty($params['tpcat2'])) $title .= ', ' . $params['tpcat2'];

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>WikiProject Recent Changes<?php echo $title ?></title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2>WikiProject Recent Changes<?php echo $title ?></h2>
        <form action="WikiProjectRecentChanges.php" method="post">
        <table class="form">
        <tr><td><b>Talk page category 1</b></td><td><input id="tpcat1" name="tpcat1" type="text" size="20" value="<?php echo $params['tpcat1'] ?>" /></td></tr>
        <tr><td><b>Talk page category 2 (optional)</b></td><td><input id="tpcat2" name="tpcat2" type="text" size="20" value="<?php echo $params['tpcat2'] ?>" /></td></tr>
        <tr><td colspan='2'>
        <input type="hidden" name="bots" value="0" />
        <input type="hidden" name="anonymous" value="0" />
        <input type="hidden" name="registered" value="0" />
        <b>Show bots</b><input id="bots" name="bots" type="checkbox" value="1"<?php if (! empty($params['bots'])) echo ' CHECKED' ?> />&nbsp;&nbsp;
        <b>Show anonymous users</b><input id="anonymous" name="anonymous" type="checkbox" value="1"<?php if (! empty($params['anonymous'])) echo ' CHECKED' ?> />&nbsp;&nbsp;
        <b>Show registered users</b><input id="registered" name="registered" type="checkbox" value="1"<?php if (! empty($params['registered'])) echo ' CHECKED' ?> />
        </td></tr>
        <tr><td colspan='2'><input type="submit" value="Submit" /></td></tr>
        </table>
        </form>
        <script type="text/javascript">
            if (document.getElementById) {
                var e = document.getElementById('tpcat1');
                e.focus();
            }
        </script>
        <br />
<?php
	if (! empty($changes)) {
		if (empty($changes['data'])) {
			if (! empty($params['tpcat1'])) echo 'No changes found for category';

		} else {
			echo "<div style='background-color:#FFF; padding:10px'>\n";
			$prevdate = '';

			foreach ($changes['data'] as $row) {
				$date = substr($row['rc_timestamp'], 0, 8);
				if ($date != $prevdate) {
					if (! empty($prevdate)) echo "</ul>\n";
					echo "<div style='font-weight: bold'>" . substr($date, 0, 4) . "-" .
						substr($date, 4, 2) . "-" . substr($date, 6) . "</div>\n";
					echo "<ul>\n";
					$prevdate = $date;
				}
				$time = substr($row['rc_timestamp'], 8, 2) . ':' . substr($row['rc_timestamp'], 10, 2);

				$row['rc_title'] =  str_replace(' ', '_', MediaWiki::getNamespacePrefix($row['rc_namespace'])) . $row['rc_title'];
				$row['rc_user_text'] = str_replace(' ', '_', $row['rc_user_text']);
				$urltitle = urlencode($row['rc_title']);
				$urluser = urlencode($row['rc_user_text']);
				$displaytitle = htmlentities(str_replace('_', ' ', $row['rc_title']), ENT_COMPAT, 'UTF-8');
				$displayuser = htmlentities(str_replace('_', ' ', $row['rc_user_text']), ENT_COMPAT, 'UTF-8');

				$linkdiff = "<a href='https://en.wikipedia.org/w/index.php?title=" . $urltitle .
					'&curid=' . $row['rc_cur_id'] . '&diff=' . $row['rc_this_oldid'] .
					'&oldid=' . $row['rc_last_oldid'] . "'>diff</a>";
				$linkhist = 'https://en.wikipedia.org/w/index.php?title=' . $urltitle .
					'&curid=' . $row['rc_cur_id'] . '&action=history';
				$linkart = 'https://en.wikipedia.org/wiki/' . $urltitle;

				if ($row['rc_user']) {
					$linkuser = 'https://en.wikipedia.org/wiki/User:' . $urluser;
					$linktalk = '(<a href="https://en.wikipedia.org/wiki/User_talk:' . $urluser .
						'">talk</a> | <a href="https://en.wikipedia.org/wiki/Special:Contributions/' . $urluser .
						'">contribs<a/>)';
				} else {
					$linkuser = 'https://en.wikipedia.org/wiki/Special:Contributions/' . $urluser;
					$linktalk = '(<a href="https://en.wikipedia.org/wiki/User_talk:' . $urluser .
						'">talk</a>)';
				}

				$comment = '';
				if (! empty($row['rc_comment'])) $comment = ' <span style="font-style: italic">(' .
					htmlentities(str_replace('_', ' ', $row['rc_comment']), ENT_COMPAT, 'UTF-8') . ')</span>';

				$sizediff = intval($row['rc_new_len']) - intval($row['rc_old_len']);
				if ($sizediff < -500) $sizediff = "<span style='color:DarkRed ; font-weight: bold'>($sizediff)</span>";
				elseif ($sizediff < 0) $sizediff = "<span style='color:DarkRed'>($sizediff)</span>";
				elseif ($sizediff == 0) $sizediff = "<span style='color:Gray'>($sizediff)</span>";
				elseif ($sizediff > 500) $sizediff = "<span style='color:DarkGreen; font-weight: bold'>(+$sizediff)</span>";
				else $sizediff = "<span style='color:DarkGreen'>(+$sizediff)</span>";

				$flags = '';
				if ($row['rc_bot']) $flags .= '<abbr title="This edit was performed by a bot" style="font-weight: bold">b</abbr>';
				if ($row['rc_new']) {
					$flags .= '<abbr title="This edit created a new page" style="font-weight: bold">N</abbr>';
					$linkdiff = 'diff';
				}
				if (! empty($flags)) $flags .= ' ';

				echo "<li>($linkdiff | <a href='$linkhist'>hist</a>)..$flags<a href='$linkart'>$displaytitle</a>; " .
					"$time..$sizediff..<a href='$linkuser'>$displayuser</a> $linktalk$comment</li>\n";

				$nextid = $row['rc_id'];
			}

			echo '</ul>';

			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'WikiProjectRecentChanges.php?tpcat1=' . urlencode($params['tpcat1']);
			if (! empty($params['tpcat2'])) $extra .= '&tpcat2=' . urlencode($params['tpcat2']);
			if ($params['bots'] == '1') $extra .= '&bots=1';
			if ($params['anonymous'] == '0') $extra .= '&anonymous=0';
			if ($params['registered'] == '0') $extra .= '&registered=0';
			$extra .= "&nextid=$nextid";

			$protocol = HttpUtil::getProtocol();
			echo "<div><a href='$protocol://$host$uri/$extra'>next 100</a></div></div>\n";
		}
	}
?>
        <br /><div>Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
}

function get_changes()
{
	global $params;
	$return = array();
	$data = array();
	$property_label = '';

	if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
		return $return;
	}

	if (empty($params['tpcat1'])) return $return;

	$wikiname = 'enwiki';
	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";

	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8", $user, $pass);
	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sql = 'SELECT recentchanges.* ';
	$sql .= ' FROM  categorylinks ';
	$sql .= ' JOIN `page` ON cl_from = page_id ';
	$sql .= ' JOIN recentchanges ON rc_title = page_title AND rc_namespace = page_namespace - 1 ';

	if (empty($params['tpcat2'])) {
		$sql .= " WHERE cl_to = ? AND cl_type = 'page' ";
		$sqlparams = array(str_replace(' ', '_', $params['tpcat1']));
	} else {
		$sql .= " WHERE cl_to IN (?, ?) AND cl_type = 'page' ";
		$sqlparams = array(str_replace(' ', '_', $params['tpcat1']), str_replace(' ', '_', $params['tpcat2']));
	}

	$sql .= ' AND rc_type IN (0,1) ';
	if (! empty($params['nextid'])) $sql .= ' AND rc_id < ' . $params['nextid'];
	if (! $params['bots']) $sql .= ' AND rc_bot = 0 ';
	if (! $params['anonymous']) $sql .= ' AND rc_user != 0 ';
	if (! $params['registered']) $sql .= ' AND (rc_user = 0 OR rc_bot = 1) ';
	$sql .= ' ORDER BY rc_id DESC ';
	$sql .= ' LIMIT 100 ';

	$sth = $dbh_wiki->prepare($sql);
	$sth->execute($sqlparams);
	$sth->setFetchMode(PDO::FETCH_NAMED);

	while ($row = $sth->fetch()) {
		$data[] = $row;
	}

	$return = array('data' => $data);

	return $return;
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = array();

	$params['tpcat1'] = isset($_REQUEST['tpcat1']) ? ucfirst(str_replace('_', ' ', $_REQUEST['tpcat1'])) : '';
	$params['tpcat2'] = isset($_REQUEST['tpcat2']) ? ucfirst(str_replace('_', ' ', $_REQUEST['tpcat2'])) : '';
	if (empty($params['tpcat1']) && ! empty($params['tpcat2'])) {
		$params['tpcat1'] = $params['tpcat2'];
		$params['tpcat2'] = '';
	}

	if (strpos($params['tpcat1'], 'Category:') === 0) $params['tpcat1'] = ucfirst(substr($params['tpcat1'], 9));
	if (strpos($params['tpcat2'], 'Category:') === 0) $params['tpcat2'] = ucfirst(substr($params['tpcat2'], 9));

	$params['bots'] = isset($_REQUEST['bots']) ? $_REQUEST['bots'] : '0';
	$params['anonymous'] = isset($_REQUEST['anonymous']) ? $_REQUEST['anonymous'] : '1';
	$params['registered'] = isset($_REQUEST['registered']) ? $_REQUEST['registered'] : '1';
	$params['nextid'] = isset($_REQUEST['nextid']) ? intval($_REQUEST['nextid']) : 0;
}

?>