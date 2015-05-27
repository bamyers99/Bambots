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

use com_brucemyers\PageTools\UIHelper;
use com_brucemyers\Util\HttpUtil;
use com_brucemyers\MediaWiki\WikidataItem;
use com_brucemyers\Util\TemplateParamParser;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'PageTools';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$uihelper = new UIHelper();
$params = array();

//$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

//switch ($action) {
//}

get_params();

// Redirect to get the results so have a bookmarkable url
if (! empty($_POST['page']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'PageTools.php?wiki=' . urlencode($params['wiki']) . '&page=' . urlencode($params['page']);
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
	global $params;
	$title = '';
	if (! empty($params['page'])) $title = ' : ' . $params['page'];
	$title = htmlentities($title, ENT_COMPAT, 'UTF-8');
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Page Tools<?php echo $title ?></title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2>Page Tools<?php echo $title ?></h2>
        <form action="PageTools.php" method="post">
        <b>Wiki</b> <input name="wiki" type="text" size="6" value="<?php echo $params['wiki'] ?>" />
        <b>Page</b> <input name="page" id="testfield1" type="text" size="25" value="<?php echo $params['page'] ?>" />
        <input type="submit" value="Submit" />
        </form>

        <script type="text/javascript">
            if (document.getElementById) {
                document.getElementById('testfield1').focus();
            }
        </script>

    <?php
    if (! empty($params['page']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
        display_data();
    }

    ?></div><br /><div style="display: table; margin: 0 auto;">Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99" class='novisited'>Bamyers99</a></div></body></html><?php
}

/**
 * Display page data
 */
function display_data()
{
	global $uihelper, $params;

	$results = $uihelper->getResults($params);
	if (empty($results['abstract'])) $results['errors'][] = 'Page not found';

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (! empty($results['abstract'])) {
		$protocol = HttpUtil::getProtocol();
		$lang = substr($params['wiki'], 0, 2);
		$domain = $lang . '.wikipedia.org';
		$wikiprefix = "$protocol://$domain/wiki/";

		$pagename = str_replace('_', ' ', ucfirst(trim($params['page'])));
		// Strip qualifier
		$unqualifiedpage = preg_replace('! \([^\)]+\)!', '', $pagename);

		echo "<br /><div><b>Page:</b> <a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $pagename)) . "\" target=\"_new\">" .
						htmlentities($pagename, ENT_COMPAT, 'UTF-8') . "</a><div>";

		// display abstract
		echo "<div><b>Abstract:</b> " . str_replace(array('<p>','</p>'), '', $results['abstract']) . "<div>";

		// display birth/death year
		if ($params['wiki'] == 'enwiki') {
			$birthyear = '?';
			$deathyear = '?';

			foreach ($results['categories'] as $cat => $hidden) {
				if ($cat == 'Living people') $deathyear = 'Living';
				elseif ($cat == 'Possibly living people') $deathyear = 'Possibly living';
				elseif (preg_match('!(\d{4}s?) births!', $cat, $matches)) {
					$birthyear = $matches[1];
				} elseif (preg_match('!(\d{4}s?) deaths!', $cat, $matches)) {
					$deathyear = $matches[1];
				}
			}

			echo "<div><b>Born:</b> $birthyear <b>Died:</b> $deathyear</div>";
		}

		// display wikidata
		if ($results['wikidata_exact_match']) {
			$itemid = $results['wikidata'][0]->getId();
			echo "<div><b>Wikidata item:</b> <a href=\"$protocol://www.wikidata.org/wiki/$itemid\" target=\"_new\">$itemid</a><div>";
		} else {
			echo '<h3>Possible Wikidata matches</h3>';

			if (empty($results['wikidata'])) echo '<div>None</div>';
			else {
				echo '<table class="wikitable"><tr><th>Item</th><th>Label</th><th>Description</th><th>Birth date</th><th>Death date</th></tr>';

				foreach ($results['wikidata'] as $wikidata) {
					$itemid = $wikidata->getId();
					$label = htmlentities($wikidata->getLabelDescription('label', $lang), ENT_COMPAT, 'UTF-8');
					$description = htmlentities($wikidata->getLabelDescription('description', $lang), ENT_COMPAT, 'UTF-8');
					$birthdates = implode('<br />', $wikidata->getStatementsOfType(WikidataItem::TYPE_BIRTHDATE));
					$deathdates = implode('<br />', $wikidata->getStatementsOfType(WikidataItem::TYPE_DEATHDATE));

					$url = "$protocol://www.wikidata.org/wiki/" . $itemid;

					echo "<tr><td><a href=\"$url\"  target=\"_new\">$itemid</a></td><td>$label</td><td>$description</td><td>$birthdates</td><td>$deathdates</td></tr>";
				}

				echo '</table>';
			}

			$urllabel = urlencode($unqualifiedpage);

			echo "<div><a href='https://www.wikidata.org/w/index.php?title=Special:NewItem&label=$urllabel' target='_new'>Create new Wikidata item</a></div>";
		}

		// display authority control
		$auth_templates = array('Authority control', 'Authority Control', 'Normdaten');
		$page_auths = array();

		$templates = TemplateParamParser::getTemplates($results['pagetext']);

		foreach ($templates as $template) {
			if (in_array($template['name'], $auth_templates)) {
				if (isset($template['params']['VIAF'])) $page_auths['VIAF'] = $template['params']['VIAF'];
				if (isset($template['params']['ISNI'])) $page_auths['ISNI'] = $template['params']['ISNI'];
				if (isset($template['params']['ORCID'])) $page_auths['ORCID'] = $template['params']['ORCID'];
				break;
			}
		}

		$auth_types = array('VIAF' => WikidataItem::TYPE_AUTHCTRL_VIAF,
			'ISNI' => WikidataItem::TYPE_AUTHCTRL_ISNI,
			'ORCID' => WikidataItem::TYPE_AUTHCTRL_ORCID);
		$wikidata_auths = array();

		if ($results['wikidata_exact_match']) {
			foreach ($auth_types as $auth_type => $prop) {
				$wikidata_auths[$auth_type] = $results['wikidata'][0]->getStatementsOfType($prop);
			}
		}

		echo '<h3>Authority control</h3>';
		echo '<table class="wikitable"><tr><th>Authority</th><th>On page</th>';
		if ($results['wikidata_exact_match']) echo '<th>Wikidata</th>';
		echo '</tr>';

		foreach ($auth_types as $auth_type => $prop) {
			switch ($auth_type) {
				case 'VIAF':
					$idurl = 'https://viaf.org/viaf/$1/';
					$searchurl= 'https://viaf.org/viaf/search?query=local.names+all+%22$1%22&sortKeys=holdingscount&recordSchema=BriefVIAF';
					break;

				case 'ISNI':
					$idurl = 'http://isni.org/isni/$1';
					$searchurl= 'http://isni.oclc.nl/DB=1.2/CMD?ACT=SRCHA&IKT=8006&SRT=&TRM=$1';
					break;

				case 'ORCID':
					$idurl = 'http://orcid.org/$1';
					$searchurl= 'https://orcid.org/orcid-search/quick-search/?searchQuery=$1';
					break;
			}

			if (isset($page_auths[$auth_type])) {
				$authid = str_replace(' ', '', $page_auths[$auth_type]);
				$displayid = htmlentities($authid, ENT_COMPAT, 'UTF-8');
				$url = str_replace('$1', urlencode($authid), $idurl);
				$coldata = "<a href='$url' target='_new'>$displayid</a>";
			} else {
				$url = str_replace('$1', urlencode($unqualifiedpage), $searchurl);
				if (empty($wikidata_auths[$auth_type])) $coldata = "<a href='$url' target='_new'>Search</a>";
				else $coldata = '';
			}

			echo "<tr><td>$auth_type ($prop)</td><td>$coldata</td>";

			if ($results['wikidata_exact_match']) {
				if (! empty($wikidata_auths[$auth_type])) {
					$coldata = array();

					foreach ($wikidata_auths[$auth_type] as $wikidata_auth) {
						$authid = str_replace(' ', '', $wikidata_auth);
						$displayid = htmlentities($authid, ENT_COMPAT, 'UTF-8');
						$url = str_replace('$1', urlencode($authid), $idurl);
						$coldata[] = "<a href='$url' target='_new'>$displayid</a>";
					}

					$coldata = implode('<br />', $coldata);
				} else {
					$coldata = '';
				}

				echo "<td>$coldata</td>";
			}

			echo '</tr>';
		}

		echo '</table>';
	}
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = array();

	$params['wiki'] = isset($_REQUEST['wiki']) ? $_REQUEST['wiki'] : 'enwiki';
	if (strlen($params['wiki']) < 6) $params['wiki'] = 'enwiki';

	$params['page'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
}
?>