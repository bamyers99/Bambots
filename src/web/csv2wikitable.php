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

use com_brucemyers\Util\HTMLForm;
use com_brucemyers\Util\CSVString;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Curl;
use com_brucemyers\Util\HttpUtil;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = array();

get_params();

// Redirect to get the results so have a bookmarkable url
if (isset($_POST['file']) && empty($_POST['csv']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

	$extra = 'csv2wikitable.php?file=' . urlencode($params['file']);
	if ($params['sep'] != ',') $extra .= '&sep=' . urlencode($params['sep']);
	if ($params['delim'] != '"') $extra .= '&delim=' . urlencode($params['delim']);
	if ($params['firstrow'] != '0') $extra .= '&firstrow=' . urlencode($params['firstrow']);
	if ($params['link1'] != '') $extra .= '&link1=' . urlencode($params['link1']);
	if ($params['link2'] != '') $extra .= '&link2=' . urlencode($params['link2']);
	if ($params['subdmn'] != 'tools') $extra .= '&subdmn=' . urlencode($params['subdmn']);

	$protocol = HttpUtil::getProtocol();
	header("Location: $protocol://$host$uri/$extra", true, 302);
	exit;
}

$error = processCSV();

display_form($error);

/**
 * Display new pages for a user
 *
 */
function display_form($error)
{
	global $params, $result;
	$columns = array('' => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10');
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>CSV &#8594; Wikitable</title>
    	<link rel='stylesheet' type='text/css' href='css/cwb.css' />
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
		<h2>CSV &#8594; Wikitable</h2>
		<?php if (! empty($error)) echo "<h3 style='color:red'>$error<h3>" ?>
        <form action="csv2wikitable.php" method="post">
        <table class="form">
        <tr><td><b>Field separator</b> <?php echo HTMLForm::generateSelect('sep', array(',' => ',', ';' => ';', '|' => '|', 'space' => '<space>', 'tab' => '<tab>'), $params['sep']) ?>
        </td>
        <td><b>Text delimiter</b> <?php echo HTMLForm::generateSelect('delim', array('"' => '"', "'" => "'", '' => '<none>'), $params['delim']) ?>
        </td>
        <td><b>First row contains headings</b> <input type="checkbox" name="firstrow" <?php if ($params['firstrow'] == '1') echo ' checked="checked"' ?> />
        </td></tr>
        <tr><td><b>Wikilink column</b> <?php echo HTMLForm::generateSelect('link1', $columns, $params['link1']) ?>
        </td>
        <td><b>Wikilink column</b> <?php echo HTMLForm::generateSelect('link2', $columns, $params['link2']) ?>
        </td>
        <td>&nbsp;
        </td></tr>
        <tr><td colspan='3'><b>Input file</b> (optional) http://<input name="subdmn" type="text" size="6" maxlength="32" value="<?php echo htmlspecialchars($params['subdmn']) ?>" />.wmflabs.org/<input name="file" type="text" size="50" maxlength="1024" value="<?php echo htmlspecialchars($params['file']) ?>" /></td></tr>
        <tr><td colspan='3'>
        <table>
        <tr><td><b>CSV rows</b> (optional)</td><td><b>Wikitable</b> <?php if (! empty($result)) echo 'Ctrl-C or Cmd-C to copy to clipboard'; else echo '(output only)'; ?></td></tr>
        <tr><td><textarea rows="20" cols="65" name="csv"><?php echo htmlspecialchars($params['csv']) ?></textarea></td>
        <td><textarea rows="20" cols="65" name="wikitable" id="wikitable"><?php echo htmlspecialchars($result) ?></textarea></td></tr>
        </table>
        </td></tr>
        <tr><td colspan='3'><input type="submit" value="Submit" /></td></tr>
        </table>
        </form>
        <?php if (! empty($result)) {?>
        <script type="text/javascript">
            if (document.getElementById) {
                var e = document.getElementById('wikitable');
                e.focus();
                e.select();
            }
        </script>
        <?php } ?>
        </div><br /><div style="display: table; margin: 0 auto;">
    Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99">Bamyers99</a></div></body></html><?php
}


/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = array();

	$params['sep'] = isset($_REQUEST['sep']) ? $_REQUEST['sep'] : ',';
	$params['delim'] = isset($_REQUEST['delim']) ? $_REQUEST['delim'] : '"';
	$params['firstrow'] = isset($_REQUEST['firstrow']) && $_REQUEST['firstrow'] != '0' ? '1' : '0';
	$params['link1'] = isset($_REQUEST['link1']) ? $_REQUEST['link1'] : '';
	$params['link2'] = isset($_REQUEST['link2']) ? $_REQUEST['link2'] : '';
	$params['file'] = isset($_REQUEST['file']) ? $_REQUEST['file'] : '';
	$params['csv'] = isset($_REQUEST['csv']) ? $_REQUEST['csv'] : '';
	$params['subdmn'] = isset($_REQUEST['subdmn']) ? $_REQUEST['subdmn'] : 'tools';
}

/**
 * Process a CSV file
 *
 * @return string Error message
 */
function processCSV()
{
	global $params, $result;

	$result = '';

	if (empty($params['file']) && empty($params['csv'])) return '';
	if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) return '';

	if (empty($params['csv'])) $data = Curl::getUrlContents("http://{$params['subdmn']}.wmflabs.org/{$params['file']}");
	else $data = $params['csv'];

	if ($data === false) return "Problem reading http://{$params['subdmn']}.wmflabs.org/{$params['file']} (" . Curl::$lastError . ")";

	$result .= "{| class=\"wikitable sortable\"\n";

	$rows = preg_split('!\r?\n!', $data, 0, PREG_SPLIT_NO_EMPTY);

	if (empty($rows)) return 'No CSV data found';

	$rowstart = 0;
	$link1 = 0;
	$link2 = 0;
	if (is_numeric($params['link1'])) $link1 = (int)$params['link1'];
	if (is_numeric($params['link2'])) $link2 = (int)$params['link2'];

	$sep = $params['sep'];
	if ($sep == 'space') $sep = ' ';
	elseif ($sep == 'tab') $sep = "\t";

	$delim = $params['delim'];
	if ($sep == "\t") $delim = '';

	if ($params['firstrow'] == '1') {
		$rowstart = 1;
		$headings = implode('!!', CSVString::parse($rows[0], $sep, $delim));
		$result .= "|-\n!$headings\n";
	}

	$rowcount = count($rows);

	for ($x = $rowstart; $x < $rowcount; ++$x) {
		$csvdata = CSVString::parse($rows[$x], $sep, $params['delim']);
		$columncount = count($csvdata);
		if ($link1 > 0 && $link1 <= $columncount) $csvdata[$link1 - 1] = '[[' . MediaWiki::getLinkSafePagename($csvdata[$link1 - 1]) . ']]';
		if ($link2 > 0 && $link2 <= $columncount && $link1 != $link2) $csvdata[$link2 - 1] = '[[' . MediaWiki::getLinkSafePagename($csvdata[$link2 - 1]) . ']]';

		$csvdata = implode('||', $csvdata);
		$result .= "|-\n|$csvdata\n";
	}

	$result .= "|}\n";
}

?>