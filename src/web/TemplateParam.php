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

use com_brucemyers\TemplateParamBot\UIHelper;
use com_brucemyers\Util\HttpUtil;
use com_brucemyers\Util\L10N;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'TemplateParamBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$uihelper = new UIHelper();
$wikis = $uihelper->getWikis();
$params = array();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

get_params();

switch ($action) {
	case 'getLoadStatus':
		getLoadStatus();
		exit;
}

// Redirect to get the results so have a bookmarkable url
if (isset($_POST['template']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'TemplateParam.php?wiki=' . urlencode($params['wiki']) . '&template=' . urlencode($params['template']);
	$protocol = HttpUtil::getProtocol();
	header("Location: $protocol://$host$uri/$extra", true, 302);
	exit;
}

$l10n = new L10N($wikis[$params['wiki']]['lang']);

display_form();

/**
 * Display form
 *
 */
function display_form()
{
	global $uihelper, $params, $wikis, $l10n, $action;
	$title = '';
	if (! empty($params['template'])) $title .= ' : ' . $params['template'];
	if (! empty($params['param'])) $title .= ' : ' . $params['param'];
	if ($action == 'missing') $title .= ' (' . $l10n->get('missing') . ')';
	if (! empty($params['value'])) $title .= ' : ' . $params['value'];
	$title = htmlentities($title, ENT_COMPAT, 'UTF-8');
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title><?php echo htmlentities($l10n->get('pagetitle'), ENT_COMPAT, 'UTF-8') . $title ?></title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
    	<script type='text/javascript' src='js/jquery-2.1.1.min.js'></script>
		<script type='text/javascript' src='js/jquery.tablesorter.min.js'></script>
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2><a href="TemplateParam.php<?php if (! empty($params['template'])) echo "?template=" . urlencode($params['template']) ?>" class="novisited"><?php echo htmlentities($l10n->get('pagetitle'), ENT_COMPAT, 'UTF-8') . $title ?></a></h2>
        <form action="TemplateParam.php" method="post"><table class="form">
        <tr><td><b><?php echo htmlentities($l10n->get('wiki', true), ENT_COMPAT, 'UTF-8') ?></b> <select name="wiki" onchange="this.form.submit()"><?php
        foreach ($wikis as $wikiname => $wikidata) {
			$wikititle = htmlentities($wikidata['title'], ENT_COMPAT, 'UTF-8');
			$selected = '';
			if ($wikiname == $params['wiki']) $selected = ' selected="1"';
			$wikiname = htmlentities($wikiname, ENT_COMPAT, 'UTF-8');
			echo "<option value='$wikiname'$selected>$wikititle</option>";
		}
        ?></select></td></tr>
        <tr><td><b><?php echo htmlentities($l10n->get('template', true), ENT_COMPAT, 'UTF-8') ?></b> <input name='template' id='testfield1' type='text' size='30' value='<?php echo htmlentities($params['template'], ENT_COMPAT, 'UTF-8') ?>' /></td></tr>
        <tr><td><input type="submit" value="<?php echo htmlentities($l10n->get('submit', true), ENT_COMPAT, 'UTF-8') ?>" /></td></tr>
        </table></form>

        <script type="text/javascript">
            if (document.getElementById) {
                document.getElementById('testfield1').focus();
            }
        </script>
    <?php
	echo '<div>' . htmlentities($l10n->get('bottomnote1'), ENT_COMPAT, 'UTF-8') . '<br />' .
		htmlentities($l10n->get('bottomnote2'), ENT_COMPAT, 'UTF-8') . '</div>';
	$asof = $wikis[$params['wiki']]['lastdumpdate'];
	echo '<div>' . htmlentities($l10n->get('asofdate', true), ENT_COMPAT, 'UTF-8') . ' ' . substr($asof, 0, 4) . "-" . substr($asof, 4, 2) .
		"-" . substr($asof, 6) . '</div>';

    if (isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
    	switch ($action) {
    		case 'template':
        		display_template();
        		break;

    		case 'paramlinks':
        		display_paramlinks();
        		break;

        	case 'valuelinks':
        		display_valuelinks();
        		break;

        	case 'missing':
        		display_missing();
        		break;

        	default:
    			display_all_templates();
    			break;
    	}
    }

    ?></div><br /><div style="display: table; margin: 0 auto;">
    <?php echo htmlentities($l10n->get('author', true), ENT_COMPAT, 'UTF-8') ?>: <a href="https://en.wikipedia.org/wiki/User:Bamyers99" class='novisited'>Bamyers99</a></div></body></html><?php
}

/**
 * Display all templates
 */
function display_all_templates()
{
	global $uihelper, $params, $wikis, $l10n;

	$results = $uihelper->getAllTemplates($params, 100);
	if (empty($results['results'])) $results['errors'][] = 'No more results';

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (empty($results['results'])) return;

	echo <<< EOT
	<script type='text/javascript'>
		$(document).ready(function()
		    {
	        $('.tablesorter').tablesorter({ headers: { 0: {sorter:"text"}, 1: { sorter: false} } });
		    }
		);
	</script>
EOT;

	$protocol = HttpUtil::getProtocol();
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

	echo "<table class='wikitable tablesorter'><thead><tr><th>" .
			htmlentities($l10n->get('template', true), ENT_COMPAT, 'UTF-8') . "</th><th>" .
			htmlentities($l10n->get('pagecount', true), ENT_COMPAT, 'UTF-8') . "</th><th>" .
			htmlentities($l10n->get('transclusioncount', true), ENT_COMPAT, 'UTF-8') . "</th></tr></thead><tbody>\n";

	foreach ($results['results'] as $result) {
		$tmplname = $result['name'];

		$extra = "TemplateParam.php?wiki=" . urlencode($params['wiki']) . "&template=" . urlencode($tmplname);
		echo "<td><a href=\"$protocol://$host$uri/$extra\">" .
			htmlentities($tmplname, ENT_COMPAT, 'UTF-8') . "</a></td>";

		echo "<td style='text-align:right'>{$result['page_count']}&nbsp;</td><td style='text-align:right'>{$result['instance_count']}&nbsp;</td></tr>";
	}

	echo "</tbody></table>\n";

	if (count($results['results']) == 100) {
		$extra = "TemplateParam.php?wiki=" . urlencode($params['wiki']) . "&page=" . ($params['page'] + 1);
		echo "<div style='padding-bottom: 10px;' class='novisited'><a href='$protocol://$host$uri/$extra'>" .
			htmlentities($l10n->get('nextpage', true), ENT_COMPAT, 'UTF-8') . "</a></div>";
	}
}

/**
 * Display template
 */
function display_template()
{
	global $uihelper, $params, $wikis, $l10n;

	$results = $uihelper->getTemplate($params);
	$pagelinks = true;

	if (! empty($results['info']) && $results['info']['file_offset'] == -1) {
		echo '<div>' . htmlentities($l10n->get('pagelistsunavailable', true), ENT_COMPAT, 'UTF-8') . '</div>';
		$pagelinks = false;
	}

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (empty($results['info'])) return;

	echo <<< EOT
	<script type='text/javascript'>
		$(document).ready(function()
		    {
	        $('.tablesorter').tablesorter({ headers: { 0: {sorter:"text"}, 3: { sorter: false} } });
		    }
		);
	</script>
EOT;

	$protocol = HttpUtil::getProtocol();
	$domain = $wikis[$params['wiki']]['domain'];
	$wikiprefix = "$protocol://$domain/wiki/Template:";
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$tmplname = $params['template'];
	$wikitext = '';

	if (isset($results['info']['TemplateData'])) $templatedata = $results['info']['TemplateData'];
	else $templatedata = false;

	if ($templatedata) $paramdef = $templatedata->getParams();
	else $paramdef = false;

	echo '<div><b>' . htmlentities($l10n->get('template', true), ENT_COMPAT, 'UTF-8') .
			"</b>: <a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $tmplname)) . "\">" .
			htmlentities($tmplname, ENT_COMPAT, 'UTF-8') . "</a>";
	echo '<div><b>' . htmlentities($l10n->get('pagecount', true), ENT_COMPAT, 'UTF-8') . '</b>: ' . $results['info']['page_count'] . '</div>';
	echo '<div><b>' . htmlentities($l10n->get('transclusioncount', true), ENT_COMPAT, 'UTF-8') . '</b>: ' . $results['info']['instance_count'] . '</div>';

	$wikitext .= $l10n->get('template', true) . ": [[Template:$tmplname|$tmplname]]<br />\n";
	$wikitext .= $l10n->get('pagecount', true) . ": {$results['info']['page_count']}<br />\n";
	$wikitext .= $l10n->get('transclusioncount', true) . ": {$results['info']['instance_count']}<br />\n";
	$asof = $wikis[$params['wiki']]['lastdumpdate'];
	$wikitext .= $l10n->get('asofdate', true) . ": " . substr($asof, 0, 4) . "-" . substr($asof, 4, 2) .
			"-" . substr($asof, 6) . "<br />\n";

	echo "<table class='wikitable tablesorter'><thead><tr><th>" .
			htmlentities($l10n->get('paramname', true), ENT_COMPAT, 'UTF-8') . "</th><th>" .
			htmlentities($l10n->get('validparamname', true), ENT_COMPAT, 'UTF-8') . "</th><th>" .
			htmlentities($l10n->get('valuecount', true), ENT_COMPAT, 'UTF-8') . "</th><th>" .
			htmlentities($l10n->get('uniquevalues', true), ENT_COMPAT, 'UTF-8') . ' (' .
			htmlentities($l10n->get('count'), ENT_COMPAT, 'UTF-8') . ")</th></tr></thead><tbody>\n";

	$headings = implode('!!', array($l10n->get('paramname', true), $l10n->get('validparamname', true),
			$l10n->get('valuecount', true), $l10n->get('uniquevalues', true) . ' (' . $l10n->get('count') . ')'
	));

	$wikitext .= "{| class=\"wikitable sortable\"\n|-\n!$headings\n";

	foreach ($results['info']['params'] as $param) {
		$paramname = htmlentities($param['param_name'], ENT_COMPAT, 'UTF-8');
		$validparamname = '&nbsp;';
		$missinglink = false;

		if ($paramdef) {
			if (isset($paramdef[$paramname])) {
				if (isset($paramdef[$paramname]['deprecated'])) $validparamname = 'D';
				else {
					$validparamname = 'Y';
					if (isset($paramdef[$paramname]['required'])) {
						$missinglink = true;
						$validparamname .= ' (R)';
					} elseif (isset($paramdef[$paramname]['suggested'])) {
						$missinglink = true;
						$validparamname .= ' (S)';
					}
				}
			} else {
				$validparamname = 'N';
			}
		}

		$parmpageslink = '';
		if ($pagelinks) {
			$extra = "TemplateParam.php?action=paramlinks&wiki=" . urlencode($params['wiki']) .
				"&template=" . urlencode($tmplname) . "&param=" . urlencode($param['param_name']);
			$parmpageslink = " <a href='$protocol://$host$uri/$extra'>(" .
				str_replace(' ', '&nbsp;', htmlentities($l10n->get('pagelinks'), ENT_COMPAT, 'UTF-8')) . ")</a>";

			if ($missinglink) {
				$extra = "TemplateParam.php?action=missing&wiki=" . urlencode($params['wiki']) .
					"&template=" . urlencode($tmplname) . "&param=" . urlencode($param['param_name']);
				$parmpageslink .= " <a href='$protocol://$host$uri/$extra'>(" .
					str_replace(' ', '&nbsp;', htmlentities($l10n->get('missing'), ENT_COMPAT, 'UTF-8')) . ")</a>";
			}
		}

		$uniques = explode("\t", $param['unique_values']);
		$cnt = count($uniques);
		if ($cnt > 1) {
			$uniquedata = '';
			$wuniquedata = '';
			for ($x = 0; $x < $cnt; $x += 2) {
				$val = htmlentities($uniques[$x], ENT_COMPAT, 'UTF-8');
				$count = $uniques[$x + 1];

				$valuepageslink = '';
				if ($pagelinks) {
					$extra = 'TemplateParam.php?action=valuelinks&wiki=' . urlencode($params['wiki']) .
						'&template=' . urlencode($tmplname) . '&param=' . urlencode($param['param_name']).
						'&value=' . urlencode($uniques[$x]);
					$valuepageslink = " <a href='$protocol://$host$uri/$extra'>(page&nbsp;links)</a>";
				}

				$uniquedata .= "$val&nbsp;&nbsp;($count)$valuepageslink<br />";
				$wuniquedata .= "<nowiki>$val</nowiki>&nbsp;&nbsp;($count)<br />";
			}

		} else {
			$uniquedata = htmlentities('> 50 ' . $l10n->get('uniquevalues'), ENT_COMPAT, 'UTF-8');
			$wuniquedata = htmlentities('> 50 ' . $l10n->get('uniquevalues'), ENT_COMPAT, 'UTF-8');
		}

		echo "<td>$paramname$parmpageslink</td><td style='text-align:center'>$validparamname</td><td style='text-align:right'>{$param['value_count']}&nbsp;</td><td>$uniquedata</td></tr>";
		$csvdata = implode('||', array($paramname, $validparamname, $param['value_count'], $wuniquedata));
		$wikitext .= "|-\n|$csvdata\n";
	}

	echo '</tbody></table>';
	echo '<div>' . htmlentities($l10n->get('validnamekey', true), ENT_COMPAT, 'UTF-8') . '</div>';

	$wikitext .= "|}\n" . $l10n->get('validnamekey', true);
	echo '<br /><b>' . htmlentities($l10n->get('wikitext', true), ENT_COMPAT, 'UTF-8') .
		'</b><form><textarea rows="20" cols="65" name="wikitable" id="wikitable">' . htmlspecialchars($wikitext) .
		'</textarea></form>';

}

/**
 * Display a parameters links
 */
function display_paramlinks()
{
	global $uihelper, $params, $wikis, $l10n;

	$results = $uihelper->getTemplate($params);
	$pagelinks = true;

	if (! empty($results['info'])) {
		if ($results['info']['file_offset'] == -1) {
			$results['errors'][] = htmlentities($l10n->get('pagelistsunavailable', true), ENT_COMPAT, 'UTF-8');
			$pagelinks = false;
		}

		if (! isset($results['info']['params'][$params['param']])) {
			$results['errors'][] = htmlentities($l10n->get('invalidparameter', true) . " = {$params['param']}", ENT_COMPAT, 'UTF-8');
			$pagelinks = false;
		}
	}

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (empty($results['info']) || ! $pagelinks) return;

	$loaded = checkLoadStatus($results['info']);
	if ($loaded['status'] != 'C') return;

	$results = $uihelper->getPages('paramlinks', $params, 100);

	if (empty($results['results'])) {
		echo '<div><b>No more results</b></div>';
		return;
	}

	$protocol = HttpUtil::getProtocol();
	$domain = $wikis[$params['wiki']]['domain'];
	$wikiprefix = "$protocol://$domain/wiki/";
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

	echo '<table class="wikitable"><thead><tr><th>' . htmlentities($l10n->get('page', true), ENT_COMPAT, 'UTF-8') .
	'</th></tr></thead><tbody>';

	foreach ($results['results'] as $page) {
		$page = $page['page_title'];
		echo "<tr><td><a href=\"$wikiprefix$page\">" .
		htmlentities(str_replace('_', ' ', $page), ENT_COMPAT, 'UTF-8') . "</a></td></tr>";
	}

	echo '<tbody></table>';

	if (count($results['results']) == 100) {
		$extra = "TemplateParam.php?action=paramlinks&wiki=" . urlencode($params['wiki']) .
		"&template=" . urlencode($params['template']) . "&param=" . urlencode($params['param']) . "&page=" . ($params['page'] + 1);
		echo "<div style='padding-bottom: 10px;' class='novisited'><a href='$protocol://$host$uri/$extra'>" .
		htmlentities($l10n->get('nextpage', true), ENT_COMPAT, 'UTF-8') . "</a></div>";
	}
}

/**
 * Display missing a parameter
 */
function display_missing()
{
	global $uihelper, $params, $wikis, $l10n;

	$results = $uihelper->getTemplate($params);
	$pagelinks = true;

	if (! empty($results['info'])) {
		if ($results['info']['file_offset'] == -1) {
			$results['errors'][] = htmlentities($l10n->get('pagelistsunavailable', true), ENT_COMPAT, 'UTF-8');
			$pagelinks = false;
		}

		if (! isset($results['info']['params'][$params['param']])) {
			$results['errors'][] = htmlentities($l10n->get('invalidparameter', true) . " = {$params['param']}", ENT_COMPAT, 'UTF-8');
			$pagelinks = false;
		}
	}

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (empty($results['info']) || ! $pagelinks) return;

	$loaded = checkLoadStatus($results['info']);
	if ($loaded['status'] != 'C') return;

	$results = $uihelper->getMissing($params, 100);

	if (empty($results['results'])) {
		echo '<div><b>No more results</b></div>';
		return;
	}

	$protocol = HttpUtil::getProtocol();
	$domain = $wikis[$params['wiki']]['domain'];
	$wikiprefix = "$protocol://$domain/wiki/";
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

	echo '<table class="wikitable"><thead><tr><th>' . htmlentities($l10n->get('page', true), ENT_COMPAT, 'UTF-8') .
	'</th></tr></thead><tbody>';

	foreach ($results['results'] as $page) {
		$page = $page['page_title'];
		echo "<tr><td><a href=\"$wikiprefix$page\">" .
		htmlentities(str_replace('_', ' ', $page), ENT_COMPAT, 'UTF-8') . "</a></td></tr>";
	}

	echo '<tbody></table>';

	if (count($results['results']) == 100) {
		$extra = "TemplateParam.php?action=missing&wiki=" . urlencode($params['wiki']) .
		"&template=" . urlencode($params['template']) . "&param=" . urlencode($params['param']) . "&page=" . ($params['page'] + 1);
		echo "<div style='padding-bottom: 10px;' class='novisited'><a href='$protocol://$host$uri/$extra'>" .
		htmlentities($l10n->get('nextpage', true), ENT_COMPAT, 'UTF-8') . "</a></div>";
	}
}

/**
 * Display a values links
 */
function display_valuelinks()
{
	global $uihelper, $params, $wikis, $l10n;

	$results = $uihelper->getTemplate($params);
	$pagelinks = true;

	if (! empty($results['info'])) {
		if ($results['info']['file_offset'] == -1) {
			$results['errors'][] = htmlentities($l10n->get('pagelistsunavailable', true), ENT_COMPAT, 'UTF-8');
			$pagelinks = false;
		}

		if (! isset($results['info']['params'][$params['param']])) {
			$results['errors'][] = htmlentities($l10n->get('invalidparameter', true) . " = {$params['param']}", ENT_COMPAT, 'UTF-8');
			$pagelinks = false;
		}
	}

	if (! empty($results['errors'])) {
		echo '<h3>Messages</h3><ul>';
		foreach ($results['errors'] as $msg) {
			echo "<li>$msg</li>";
		}
		echo '</ul>';
	}

	if (empty($results['info']) || ! $pagelinks) return;

	$loaded = checkLoadStatus($results['info']);
	if ($loaded['status'] != 'C') return;

	$results = $uihelper->getPages('valuelinks', $params, 100);

	if (empty($results['results'])) {
		echo '<div><b>No more results</b></div>';
		return;
	}

	$protocol = HttpUtil::getProtocol();
	$domain = $wikis[$params['wiki']]['domain'];
	$wikiprefix = "$protocol://$domain/wiki/";
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

	echo '<table class="wikitable"><thead><tr><th>' . htmlentities($l10n->get('page', true), ENT_COMPAT, 'UTF-8') .
		'</th></tr></thead><tbody>';

	foreach ($results['results'] as $page) {
		$page = $page['page_title'];
		echo "<tr><td><a href=\"$wikiprefix$page\">" .
			htmlentities(str_replace('_', ' ', $page), ENT_COMPAT, 'UTF-8') . "</a></td></tr>";
	}

	echo '<tbody></table>';

	if (count($results['results']) == 100) {
		$extra = "TemplateParam.php?action=valuelinks&wiki=" . urlencode($params['wiki']) .
			"&template=" . urlencode($params['template']) . "&param=" . urlencode($params['param']) .
			"&value=" . urlencode($params['value']) . "&page=" . ($params['page'] + 1);
		echo "<div style='padding-bottom: 10px;' class='novisited'><a href='$protocol://$host$uri/$extra'>" .
			htmlentities($l10n->get('nextpage', true), ENT_COMPAT, 'UTF-8') . "</a></div>";
	}
}

/**
 * Check the parameter value load status
 *
 * @param array $info Template info
 * @return array status => S,R,E,C, progress => progress message
 */
function checkLoadStatus($info)
{
	global $uihelper, $params, $wikis, $l10n;

	$loaded = $uihelper->checkLoadStatus($params, $info);

	if ($loaded['status'] == 'C' || $loaded['status'] == 'E') return $loaded;

	// Display load status
	$protocol = HttpUtil::getProtocol();
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = "TemplateParam.php?action=getLoadStatus&wiki=" . urlencode($params['wiki']) .
		"&template=" . urlencode($params['template']) . "&param=" . urlencode($params['param']);
	$statuslink = "$protocol://$host$uri/$extra";

	$label = $l10n->get('loadstatus', true);

	echo "<div><b>$label: <span id='loadStatus'>&nbsp;</span></b></div>";
	echo "<div><b>The template data is being loaded into the database.</b></div>";
	echo "<div><b>This is a one-time event that only happens the first time that someone accesses the template.</b></div>";
	echo "<div><b>The page will automatically refresh when done.</b></div>";

	$script = <<< EOT
<script>
	var getLoadStatus = function () {
		$.get( "$statuslink", function( data ) {
			switch (data.status) {
				case 'S':
				case 'R':
					$( "#loadStatus" ).html( data.progress );
					setTimeout(getLoadStatus, 15000);
					break;
				case 'E':
					$( "#loadStatus" ).html( "Load error" );
					break;
				case 'C':
					location.reload(true);
					break;
			}
		}, "json" );
	}

	setTimeout(getLoadStatus, 0);
</script>
<noscript><div><b>Refresh the page to see if loading is complete.</b></div></noscript>
EOT;

	echo $script;

	return $loaded;
}

/**
 * Ajax get load status
 */
function getLoadStatus()
{
	global $uihelper, $params, $wikis, $l10n, $action;

	$results = $uihelper->getTemplate($params);
	$loaded = $uihelper->checkLoadStatus($params, $results['info']);

	echo json_encode(array('status' => $loaded['status'], 'progress' => $loaded['progress']));
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params, $wikis, $uihelper, $action;

	$params = array();

	$params['page'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : '1';

	$params['wiki'] = isset($_REQUEST['wiki']) ? $_REQUEST['wiki'] : '';
	if (! isset($wikis[$params['wiki']])) $params['wiki'] = 'enwiki';

	$params['template'] = isset($_REQUEST['template']) ? $_REQUEST['template'] : '';
	if (! empty($params['template'])) {
		if (empty($action)) $action = 'template';
		$params['template'] = ucfirst(trim(str_replace('_', ' ', $params['template'])));
	}

	$params['param'] = isset($_REQUEST['param']) ? $_REQUEST['param'] : '';
	$params['value'] = isset($_REQUEST['value']) ? $_REQUEST['value'] : '';

}

?>