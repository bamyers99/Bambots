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

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//ini_set("display_errors", 1);

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
		<div style="display: table; margin: 0 auto; width: 1000px">
		<h2>Page Tools<?php echo $title ?></h2>
        <form action="PageTools.php" method="post">
        <b><acronym title="&lt;language&gt;wiki, wikidata">Wiki</acronym></b> <input name="wiki" type="text" size="6" value="<?php echo $params['wiki'] ?>" />
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

    ?></div><br /><div style="display: table; margin: 0 auto;"><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99" class='novisited'>Bamyers99</a></div></body></html><?php
}

/**
 * Display page data
 */
function display_data()
{
	global $uihelper, $params;

	$results = $uihelper->getResults($params);

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (! empty($results['abstract'])) {
		$protocol = HttpUtil::getProtocol();
		$lang = $results['lang'];
		$domain = $results['domain'];
		$wikiprefix = "$protocol://$domain/wiki/";
		$templates = TemplateParamParser::getTemplates($results['pagetext']);
		$is_person = 'U'; // Unknown

		$pagename = $params['page'];
		if (isset($results['pagename'])) $pagename = $results['pagename'];
		$pagename = str_replace('_', ' ', ucfirst(trim($pagename)));
		// Strip qualifier
		$unqualifiedpage = preg_replace('! \([^\)]+\)!', '', $pagename);

		echo "<br /><div><b>Page:</b> <a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $pagename)) . "\">" .
						htmlentities($pagename, ENT_COMPAT, 'UTF-8') . "</a><div>";

		// display abstract
		echo "<div><b>Abstract:</b> " . str_replace(array('<p>','</p>'), '', $results['abstract']) . "<div>";

		// display birth/death year
		if ($domain == 'en.wikipedia.org') {
			$is_person = 'N';
			$birthyear = '?';
			$deathyear = '?';

			foreach ($results['categories'] as $cat => $hidden) {
				if ($cat == 'Living people') $deathyear = 'living';
				elseif ($cat == 'Possibly living people') $deathyear = 'possibly living';
				elseif ($cat == 'Year of birth missing (living people)') $is_person = 'Y';
				elseif ($cat == 'Year of birth missing') $is_person = 'Y';
				elseif ($cat == 'Year of death missing') $is_person = 'Y';
				elseif (preg_match('!(\d{4}s?) births!', $cat, $matches)) {
					$birthyear = $matches[1];
				} elseif (preg_match('!(\d{4}s?) deaths!', $cat, $matches)) {
					$deathyear = $matches[1];
				}
			}

			if ($birthyear != '?' || $deathyear != '?') $is_person = 'Y';

			echo "<div><b>Wikipedia - Born:</b> $birthyear <b>Died:</b> $deathyear</div>";
		}

		// display official website
		$official_templates = array('Official website','Company Website','Home page','Homepage','Main website','Offficial website',
			'Official','Official Website','Official homepage','Official site','Official web site','OfficialSite','Officialsite','Officialwebsite');
		$official_site = '';

		foreach ($templates as $template) {
			if (preg_match('!infobox!i', $template['name'])) {
				if (isset($template['params']['url'])) $official_site = $template['params']['url'];
				if (isset($template['params']['website'])) $official_site = $template['params']['website'];
			}

			if (in_array($template['name'], $official_templates) && isset($template['params']['1'])) { // can be empty to use wikidata
				$official_site = $template['params']['1'];
				if (! empty($official_site) && $official_site[0] == '/') $official_site = "$protocol:$official_site";
				if (! empty($official_site)) break;
			}
		}

		if ($results['wikidata_exact_match']) {
			$propvalue = $results['wikidata'][0]->getStatementsOfType(WikidataItem::TYPE_OFFICIAL_WEBSITE);
			if (! empty($propvalue)) $official_site = $propvalue[0];
		}

		if (! empty($official_site)) {
			$temp = TemplateParamParser::getTemplates($official_site);
			if (! empty($temp) && strtoupper($temp[0]['name']) == 'URL' && isset($temp[0]['params']['1'])) {
				$official_site = $temp[0]['params']['1'];
			}

			if (strpos($official_site, 'http') !== 0) $official_site = "$protocol://$official_site";

			if (preg_match('!https?://(.*)!', $official_site, $matches)) {
				$temp = $matches[1];
				if (substr($temp, -1) == '/') $temp = substr($temp, 0, -1);
			} else {
				$temp = $official_site;
			}

			$temp = htmlentities($temp, ENT_COMPAT, 'UTF-8');
			echo "<div><b>Official website:</b> <a href='$official_site'>$temp</a><div>";
		}

		// display wikidata
		if ($results['wikidata_exact_match']) {
			$itemid = $results['wikidata'][0]->getId();
			echo "<div><b>Wikidata item:</b> <a href=\"$protocol://www.wikidata.org/wiki/$itemid\">$itemid</a> <b>Reasonator:</b> <a href=\"$protocol://tools.wmflabs.org/reasonator/?q=$itemid&lang=$lang\">$itemid</a><div>";

			$birthdates = $results['wikidata'][0]->getStatementsOfType(WikidataItem::TYPE_BIRTHDATE);
			$deathdates = $results['wikidata'][0]->getStatementsOfType(WikidataItem::TYPE_DEATHDATE);

			if (! empty($birthdates) || ! empty($deathdates)) {
				$birthdates = implode(', ', $birthdates);
				$deathdates = implode(', ', $deathdates);
				echo "<div><b>Wikidata - Born:</b> $birthdates <b>Died:</b> $deathdates</div>";
			}

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

					// See if already has link to our wiki
					$oursite = $wikidata->getSiteLink($params['wiki']);
					if (! empty($oursite) && $oursite['site'] == $params['wiki']) {
						$tempurl = $wikiprefix . urlencode(str_replace(' ', '_', $oursite['title']));
						$label = "<a href='$tempurl'>$label</a> ({$params['wiki']})";
					}

					echo "<tr><td><a href=\"$url\">$itemid</a></td><td>$label</td><td>$description</td><td>$birthdates</td><td>$deathdates</td></tr>";
				}

				echo '</table>';
			}

			$urllabel = urlencode($unqualifiedpage);
			$wikipage = urlencode(str_replace('_', ' ', $pagename));

			echo "<div><a href='https://www.wikidata.org/w/index.php?title=Special:NewItem&label=$urllabel&site={$params['wiki']}&page=$wikipage'>Create new Wikidata item</a></div>";
		}

		// display authority control
		$auth_templates = array('Authority control', 'Authority Control', 'Normdaten');
		$imdb_templates = array('IMDb name','IMDB name','IMDB person','IMDb Name','IMDb person','IMdb name','Imdb name','Imdb-name','Imdbname');
		$musicbrainz_templates = array('MusicBrainz artist','Musicbrainz artist','Musicbrainz.org artist');
		$page_auths = array();

		foreach ($templates as $template) {
			if (in_array($template['name'], $auth_templates)) {
				if (isset($template['params']['VIAF'])) $page_auths['VIAF'] = $template['params']['VIAF'];
				if (isset($template['params']['ISNI'])) $page_auths['ISNI'] = $template['params']['ISNI'];
				if (isset($template['params']['ORCID'])) $page_auths['ORCID'] = $template['params']['ORCID'];
				if (isset($template['params']['LCCN'])) $page_auths['LCCN'] = $template['params']['LCCN'];
				if (isset($template['params']['ULAN'])) $page_auths['ULAN'] = $template['params']['ULAN'];
			}

			if (in_array($template['name'], $imdb_templates)) {
				$imdb_id = '';
				if (isset($template['params']['1'])) $imdb_id = $template['params']['1'];
				if (isset($template['params']['id'])) $imdb_id = $template['params']['id'];

				if (! empty($imdb_id)) {
					if (substr($imdb_id, 0, 2) != 'nm') $imdb_id = 'nm' . $imdb_id;
					$page_auths['IMDb'] = $imdb_id;
				}
			}

			if (in_array($template['name'], $musicbrainz_templates)) {
				if (isset($template['params']['mbid'])) $page_auths['MusicBrainz'] = $template['params']['mbid'];
			}
		}

		$auth_types = array(
			'ISNI' => WikidataItem::TYPE_AUTHCTRL_ISNI,
			'VIAF' => WikidataItem::TYPE_AUTHCTRL_VIAF,
			'LCCN' => WikidataItem::TYPE_AUTHCTRL_LCAuth,
			'ORCID' => WikidataItem::TYPE_AUTHCTRL_ORCID,
			'ULAN' => WikidataItem::TYPE_AUTHCTRL_ULAN,
			'IMDb' => WikidataItem::TYPE_AUTHCTRL_IMDb,
			'MusicBrainz' => WikidataItem::TYPE_AUTHCTRL_MusicBrainz
		);
		$wikidata_auths = array();

		if ($results['wikidata_exact_match']) {
			foreach ($auth_types as $auth_type => $prop) {
				$wikidata_auths[$auth_type] = $results['wikidata'][0]->getStatementsOfType($prop);
			}
		}

		echo '<h3>Authority control</h3>';
		echo '<table class="wikitable"><tr><th>Authority</th>';
		if (! empty($page_auths)) echo '<th>Wikipedia</th>';
		if ($results['wikidata_exact_match']) echo '<th>Wikidata</th>';
		echo '<th>Search</th></tr>';

		foreach ($auth_types as $auth_type => $prop) {
			switch ($auth_type) {
				case 'VIAF':
					$definition = 'Virtual International Authority File';
					$idurl = 'https://viaf.org/viaf/$1/';
					$searchtype = 'names';
					if ($is_person == 'Y') $searchtype = 'personalNames';
					$searchurl= "https://viaf.org/en/viaf/search?field=local.$searchtype&index=VIAF&searchTerms=$1";
					break;

				case 'ISNI':
					$definition = 'International Standard Name Identifier';
					$idurl = 'https://isni.org/isni/$1';
					$searchurl= 'https://isni.oclc.org/cbs/DB=1.2/CMD?ACT=SRCHA&IKT=8006&SRT=&TRM=$1';
					break;

				case 'ORCID':
					if ($is_person == 'N') continue 2;
					$definition = 'Open Researcher and Contributor ID';
					$idurl = 'https://orcid.org/$1';
					$searchurl= 'https://orcid.org/orcid-search/search?searchQuery=$1';
					break;

				case 'LCCN':
					$definition = 'Library of Congress Control Number';
					$idurl = 'https://id.loc.gov/authorities/$1';
					$searchurl= 'https://id.loc.gov/search/?q=$1';
					break;

				case 'ULAN':
					$definition = 'Union List of Artist Names';
					$idurl = 'https://vocab.getty.edu/page/ulan/$1';
					$searchurl= 'https://www.getty.edu/vow/ULANServlet?english=Y&find=$1&role=&page=1&nation=';
					break;

				case 'IMDb':
					$definition = 'Internet Movie Database';
					$idurl = 'https://www.imdb.com/Name?$1';
					$searchurl= 'https://www.imdb.com/find?ref_=nv_sr_fn&q=$1&s=all';
					break;

				case 'MusicBrainz':
					$definition = '';
					$idurl = 'https://musicbrainz.org/artist/$1';
					$searchurl= 'https://musicbrainz.org/search?query=$1&type=artist&method=indexed';
					break;
			}

			$definition = htmlentities($definition, ENT_COMPAT, 'UTF-8');
			echo "<tr><td><acronym title='$definition'>$auth_type</acronym> ($prop)</td>";

			if (! empty($page_auths)) {
				$coldata = '';

				if (isset($page_auths[$auth_type])) {
					$authid = str_replace(' ', '', $page_auths[$auth_type]);
					$displayid = htmlentities($authid, ENT_COMPAT, 'UTF-8');
					$url = str_replace('$1', urlencode($authid), $idurl);
					$coldata = "<a href='$url'>$displayid</a>";
				}

				echo "<td>$coldata</td>";
			}

			if ($results['wikidata_exact_match']) {
				if (! empty($wikidata_auths[$auth_type])) {
					$coldata = array();

					foreach ($wikidata_auths[$auth_type] as $wikidata_auth) {
						$authid = str_replace(' ', '', $wikidata_auth);
						$displayid = htmlentities($authid, ENT_COMPAT, 'UTF-8');
						$url = str_replace('$1', urlencode($authid), $idurl);
						$coldata[] = "<a href='$url'>$displayid</a>";
					}

					$coldata = implode('<br />', $coldata);
				} else {
					$coldata = '';
				}

				echo "<td>$coldata</td>";
			}

			$url = str_replace('$1', urlencode($unqualifiedpage), $searchurl);
			$coldata = "<a href='$url'>Search</a>";
			echo "<td>$coldata</td></tr>";
		}

		echo '</table>';

		// Find sources
		echo '<h3>Find sources</h3>';

		$searchtext = urlencode($unqualifiedpage);
		echo '<ul>';
		echo "<li><a href='https://www.google.com/search?as_eq=wikipedia&q=%22$searchtext%22&num=50'>Google</a></li>";
		echo "<li><a href='https://www.google.com/search?q=%22$searchtext%22&tbm=nws'>news</a></li>";
		echo "<li><a href='https://www.google.com/search?&q=%22$searchtext%22+site:news.google.com/newspapers&source=newspapers'>newspapers</a></li>";
		echo "<li><a href='https://www.google.com/search?tbs=bks:1&q=%22$searchtext%22'>books</a></li>";
		echo "<li><a href='https://scholar.google.com/scholar?q=%22$searchtext%22'>scholar</a></li>";
		echo "<li><a href='http://www.jstor.org/action/doBasicSearch?Query=%22$searchtext%22&acc=on&wc=on'>JSTOR</a></li>";
		echo '</ul>';
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