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
use com_brucemyers\Util\HTMLForm;
use com_brucemyers\PageTools\UIHelper;
use com_brucemyers\Util\Logger;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$uihelper = new UIHelper();
$params = array();

get_params();

// Redirect to get the results so have a bookmarkable url
if (isset($_POST['tpcat1']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'WikiProjectRecentChanges.php?tpcat1=' . urlencode($params['tpcat1']);
	if (! empty($params['tpcat2'])) $extra .= '&tpcat2=' . urlencode($params['tpcat2']);
	if ($params['bots'] == '1') $extra .= '&bots=1';
	if ($params['anon'] == '0') $extra .= '&anon=0';
	if ($params['liu'] == '0') $extra .= '&liu=0';
	if ($params['pagens'] != -100) $extra .= '&pagens=' . $params['pagens'];
	if ($params['talkns'] != -101) $extra .= '&talkns=' . $params['talkns'];
	if ($params['wd'] == '1') $extra .= '&wd=1';
	if ($params['catmemb'] == '1') $extra .= '&catmemb=1';

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
	$pagens = array(
		-100 => 'all',
		-102 => 'none',
		0 => '(Article)',
		108 => 'Book',
		14 => 'Category',
		6 => 'File',
		10 => 'Template',
		4 => 'Wikipedia'
	);
	$talkns = array(
		-101 => 'all',
		-103 => 'none',
		1 => '(Article)',
		109 => 'Book',
		15 => 'Category',
		7 => 'File',
		11 => 'Template',
		5 => 'Wikipedia'
	);

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>WikiProject Recent Changes<?php echo $title ?></title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
		<style>
		  li {padding-bottom: 8px;}
		  a:link {color: #0645ad;}
		  a:visited {color: #0b0080;}
		</style>
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2>WikiProject Recent Changes<?php echo $title ?></h2>
        <form action="WikiProjectRecentChanges.php" method="post">
        <table class="form">
        <tr><td><b>Talk page category 1</b></td><td><input id="tpcat1" name="tpcat1" type="text" size="20" value="<?php echo $params['tpcat1'] ?>" /></td></tr>
        <tr><td><b>Talk page category 2 (optional)</b></td><td><input id="tpcat2" name="tpcat2" type="text" size="20" value="<?php echo $params['tpcat2'] ?>" /></td></tr>
        <tr><td colspan='2' style='padding-left: 0'>
        <table>
        <tr><td style="padding-left: 0"><b>Editor type:</b></td><td>
        <input type="hidden" name="bots" value="0" />
        <input type="hidden" name="anon" value="0" />
        <input type="hidden" name="liu" value="0" />
        <b>Bot</b><input id="bots" name="bots" type="checkbox" value="1"<?php if (! empty($params['bots'])) echo ' CHECKED' ?> />&nbsp;&nbsp;
        <b>Anonymous user</b><input id="anon" name="anon" type="checkbox" value="1"<?php if (! empty($params['anon'])) echo ' CHECKED' ?> />&nbsp;&nbsp;
        <b>Registered user</b><input id="liu" name="liu" type="checkbox" value="1"<?php if (! empty($params['liu'])) echo ' CHECKED' ?> />
        </td></tr>
        <tr><td><b>Edit type:</b></td><td>
        <input type="hidden" name="wd" value="0" />
        <input type="hidden" name="catmemb" value="0" />
        <b>Page</b>&thinsp;<?php echo HTMLForm::generateSelect('pagens', $pagens, $params['pagens']) ?>&nbsp;&nbsp;
        <b>Talk page</b>&thinsp;<?php echo HTMLForm::generateSelect('talkns', $talkns, $params['talkns']) ?>&nbsp;&nbsp;
        <b>Wikidata</b><input id="wd" name="wd" type="checkbox" value="1"<?php if (! empty($params['wd'])) echo ' CHECKED' ?> />&nbsp;&nbsp;
        <b>Categorization</b><input id="catmemb" name="catmemb" type="checkbox" value="1"<?php if (! empty($params['catmemb'])) echo ' CHECKED' ?> />
        </td></tr>
        </table>
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
<?php
	if (empty($changes)) {
		echo '<div>No Category or Editor type or Edit type specified</div>';
	} else {
		if (empty($changes['data'])) {
			if (! empty($params['tpcat1'])) echo '<div>No changes found for category</div>';

		} else {
			echo $changes['replag'];
			echo "<div style='background-color:#FFF; padding:10px; font-family:Arial,Helvetica,sans-serif'>\n";
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
				$linkhist = "<a href='https://en.wikipedia.org/w/index.php?title=" . $urltitle .
					'&curid=' . $row['rc_cur_id'] . "&action=history'>hist</a>";
				$linkart = 'https://en.wikipedia.org/wiki/' . $urltitle;

				if (! preg_match('!^([0-9.]+$|[0-9A-F]{4}:[0-9A-F]{4}:)!', $row['rc_user_text'])) {
					if ($row['rc_type'] == 5 && $row['rc_source'] == 'wb') $url = 'https://www.wikidata.org/wiki/';
					else $url = 'https://en.wikipedia.org/wiki/';
					$linkuser = "{$url}User:" . $urluser;
					$linktalk = "(<a href='{$url}User_talk:" . $urluser .
						"'>talk</a> | <a href='{$url}Special:Contributions/" . $urluser .
						"'>contribs</a>)";
				} else { // anon
					if ($row['rc_type'] == 5 && $row['rc_source'] == 'wb') $url = 'https://www.wikidata.org/wiki/';
					else $url = 'https://en.wikipedia.org/wiki/';
					$linkuser = "{$url}Special:Contributions/" . $urluser;
					$linktalk = "(<a href='{$url}User_talk:" . $urluser .
						"'>talk</a>)";
				}

				$comment = '';
				if (! empty($row['rc_comment'])) {
					$comment = $row['rc_comment'];

					// [[...|...]]
					if (preg_match_all('!\[\[([^|\]]+)\|([^\]]+)\]\]!', $comment, $matches, PREG_SET_ORDER)) {
						foreach ($matches as $match) {
							$wikipage = $match[1];
							if ($wikipage[0] == ':') $wikipage = substr($wikipage, 1);
							$extra = urlencode(str_replace(' ', '_', $wikipage));
							$label = htmlentities($match[2], ENT_COMPAT, 'UTF-8');
							if ($row['rc_type'] == 5 && $row['rc_source'] == 'wb') $url = 'https://www.wikidata.org/wiki/';
							else $url = 'https://en.wikipedia.org/wiki/';
							$comment = str_replace($match[0], "<a href='$url$extra'>$label</a>", $comment);
						}
					}

					// [[...]]
					if (preg_match_all('!\[\[([^\]]+)\]\]!', $comment, $matches, PREG_SET_ORDER)) {
						foreach ($matches as $match) {
							$wikipage = $match[1];
							if ($wikipage[0] == ':') $wikipage = substr($wikipage, 1);
							$extra = urlencode(str_replace(' ', '_', $wikipage));
							$label = htmlentities($wikipage, ENT_COMPAT, 'UTF-8');
							if ($row['rc_type'] == 5 && $row['rc_source'] == 'wb') $url = 'https://www.wikidata.org/wiki/';
							else $url = 'https://en.wikipedia.org/wiki/';
							$comment = str_replace($match[0], "<a href='$url$extra'>$label</a>", $comment);
						}
					}

					$comment = ' <span style="font-style: italic">(' . $comment . ')</span>';
				}

				$sizediff = intval($row['rc_new_len']) - intval($row['rc_old_len']);
				if ($sizediff < -500) $sizediff = "<span style='color:DarkRed ; font-weight: bold'>($sizediff)</span>";
				elseif ($sizediff < 0) $sizediff = "<span style='color:DarkRed'>($sizediff)</span>";
				elseif ($sizediff == 0) $sizediff = "<span style='color:Gray'>($sizediff)</span>";
				elseif ($sizediff > 500) $sizediff = "<span style='color:DarkGreen; font-weight: bold'>(+$sizediff)</span>";
				else $sizediff = "<span style='color:DarkGreen'>(+$sizediff)</span>";

				$flags = '';
				if ($row['rc_bot']) $flags .= '<abbr title="This edit was performed by a bot" style="font-weight: bold">b</abbr>';
				if ($row['rc_type'] == 5 && $row['rc_source'] == 'wb') $flags .= '<abbr title="This edit was made at Wikidata" style="font-weight: bold">D</abbr>';
				if ($row['rc_new']) {
					$flags .= '<abbr title="This edit created a new page" style="font-weight: bold">N</abbr>';
					$linkdiff = 'diff';
				}
				if (! empty($flags)) $flags .= ' ';

				$wikidatalink = '';
				if ($row['rc_type'] == 5 && $row['rc_source'] == 'wb' && ! empty($row['rc_params'])) {
					$rc_params = unserialize($row['rc_params']);
					if (isset($rc_params['wikibase-repo-change']['object_id'])) {
						$qid = strtoupper($rc_params['wikibase-repo-change']['object_id']);
						$wikidatalink = " (<a href='https://www.wikidata.org/wiki/$qid'>$qid</a>)";
					}
				}

				if ($row['rc_type'] == 5 || $row['rc_type'] == 6) {
					$linkdiff = 'diff';
					$linkhist = 'hist';
				}

				echo "<li>($linkdiff | $linkhist) . . $flags<a href='$linkart'>$displaytitle</a>$wikidatalink; " .
					"$time . . $sizediff . . <a href='$linkuser'>$displayuser</a> $linktalk$comment</li>\n";

				$nextid = $row['rc_id'];
			}

			echo '</ul>';

			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'WikiProjectRecentChanges.php?tpcat1=' . urlencode($params['tpcat1']);
			if (! empty($params['tpcat2'])) $extra .= '&tpcat2=' . urlencode($params['tpcat2']);
			if ($params['bots'] == '1') $extra .= '&bots=1';
			if ($params['anon'] == '0') $extra .= '&anon=0';
			if ($params['liu'] == '0') $extra .= '&liu=0';
			if ($params['pagens'] != -100) $extra .= '&pagens=' . $params['pagens'];
			if ($params['talkns'] != -101) $extra .= '&talkns=' . $params['talkns'];
			if ($params['wd'] == '1') $extra .= '&wd=1';
			if ($params['catmemb'] == '1') $extra .= '&catmemb=1';
			$extra .= "&nextid=$nextid";

			$protocol = HttpUtil::getProtocol();
			echo "<div><a href='$protocol://$host$uri/$extra'>next 100</a></div></div>\n";
		}
	}
?>
        <br /><div>Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
}

function get_changes()
{
	global $params, $uihelper;
	$return = array();
	$data = array();
	$property_label = '';

	if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
		return $return;
	}

	if (empty($params['tpcat1'])) return $return;
	if (! $params['bots'] && ! $params['anon'] && ! $params['liu']) return $return;

	$sqltypes = array();
	if ($params['pagens'] != -102) $sqltypes[] = 'page';
	if ($params['talkns'] != -103) $sqltypes[] = 'talk';

	if (empty($sqltypes)) return $return;

	$wikiname = 'enwiki';
	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "$wikiname.web.db.svc.eqiad.wmflabs";

	try {
	   $dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8mb4", $user, $pass);
	} catch (PDOException $e) {
	    Logger::log($e->getMessage());
	    throw new Exception('Connection error, see log for details');
	}
	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sql = '';
	$sqlparams = array();

	foreach ($sqltypes as $sqlnum => $sqltype) {
		if (count($sqltypes) == 2) {
			if ($sqlnum == 1) $sql .= ' UNION ';
			$sql .= '(';
		}

		$sql .= ' SELECT recentchanges.* ';
		$sql .= ' FROM categorylinks ';

		if ($sqltype == 'page') {
			$sql .= ' JOIN `page` ON cl_from = page_id ';
			$sql .= ' JOIN recentchanges ON rc_title = page_title AND rc_namespace = page_namespace - 1 ';
		} else {
			$sql .= ' JOIN recentchanges ON rc_cur_id = cl_from ';
		}

		if (empty($params['tpcat2'])) {
			$sql .= " WHERE cl_to = ? AND cl_type = 'page' ";
			$sqlparams[] = str_replace(' ', '_', $params['tpcat1']);
		} else {
			$sql .= " WHERE cl_to IN (?, ?) AND cl_type = 'page' ";
			$sqlparams[] = str_replace(' ', '_', $params['tpcat1']);
			$sqlparams[] = str_replace(' ', '_', $params['tpcat2']);
		}

		if ($sqltype == 'page') {
			$rctypes = array();
			if ($params['pagens'] != -102) $rctypes = array_merge($rctypes, array(0, 1));
			if ($params['catmemb']) $rctypes[] = 6;
			if ($params['wd']) {
				$rctypes[] = 5;
				$sql .= " AND (rc_type <> 5 OR rc_source = 'wb') ";
			}
			if ($params['pagens'] != -100 && $params['pagens'] != -102) $sql .= ' AND rc_namespace = ' . $params['pagens'];

			$rctypes = implode(',', $rctypes);
			$sql .= " AND rc_type IN ($rctypes) ";
		} else {
			$sql .= ' AND rc_type = 0 ';
			if ($params['talkns'] != -101) $sql .= ' AND rc_namespace = ' . $params['talkns'];
		}

		if (! empty($params['nextid'])) $sql .= ' AND rc_id < ' . $params['nextid'];
		if (! $params['bots']) $sql .= ' AND rc_bot = 0 ';
		if (! $params['anon']) $sql .= " AND rc_user_text NOT REGEXP '^([0-9.]+$|[0-9A-F]{4}:[0-9A-F]{4}:)' "; // rc_user != 0
		if (! $params['liu']) $sql .= " AND (rc_user_text REGEXP '^([0-9.]+$|[0-9A-F]{4}:[0-9A-F]{4}:)' OR rc_bot = 1) "; // rc_user = 0

		if (count($sqltypes) == 2) $sql .= ')';
	}

	$sql .= ' ORDER BY rc_id DESC ';
	$sql .= ' LIMIT 100 ';

	$sth = $dbh_wiki->prepare($sql);
	$sth->execute($sqlparams);
	$sth->setFetchMode(PDO::FETCH_NAMED);

	while ($row = $sth->fetch()) {
		$data[] = $row;
	}

	// replication lag > 1 minute
	$replag = $uihelper->getReplicationLag('enwiki');
	if (strlen($replag['replag']) > 10) $replag = "<div><b>Note</b>: The labs database copy is lagging {$replag['replag']} behind the main database.</div>";
	else $replag = '';

	$return = array('data' => $data, 'replag' => $replag);

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
	$params['anon'] = isset($_REQUEST['anon']) ? $_REQUEST['anon'] : '1';
	$params['liu'] = isset($_REQUEST['liu']) ? $_REQUEST['liu'] : '1';
	$params['pagens'] = isset($_REQUEST['pagens']) ? intval($_REQUEST['pagens']) : -100;
	$params['talkns'] = isset($_REQUEST['talkns']) ? intval($_REQUEST['talkns']) : -101;
	$params['wd'] = isset($_REQUEST['wd']) ? $_REQUEST['wd'] : '0';
	$params['catmemb'] = isset($_REQUEST['catmemb']) ? $_REQUEST['catmemb'] : '0';
	$params['nextid'] = isset($_REQUEST['nextid']) ? intval($_REQUEST['nextid']) : 0;
}

?>