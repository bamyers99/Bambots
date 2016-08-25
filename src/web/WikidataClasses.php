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

use com_brucemyers\Util\HttpUtil;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
define('CACHE_PREFIX_WDCLS', 'WDCLS:');
define('MAX_CHILD_CLASSES', 500);

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = array();

get_params();

// Redirect to get the results so have a bookmarkable url
if (isset($_POST['id']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'WikidataClasses.php?id=Q' . $params['id'] . '&lang=' . urlencode($params['lang']);
	$protocol = HttpUtil::getProtocol();
	header("Location: $protocol://$host$uri/$extra", true, 302);
	exit;
}

$subclasses = get_subclasses();

display_form($subclasses);

/**
 * Display form
 *
 */
function display_form($subclasses)
{
	global $params;
	$title = '';
	if (! empty($params['id'])) {
		if (isset($subclasses['class'][0])) $title = $subclasses['class'][0];
		if ($title != "Q{$params['id']}") $title = "$title (Q{$params['id']})";
		$title = " : $title";
	} else {
		$title = ' : Major root classes';
	}

	$title = htmlentities($title, ENT_COMPAT, 'UTF-8');
	$rootclasslink = '&nbsp;';

	if ($params['id'] != 0) {
		$protocol = HttpUtil::getProtocol();
		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

		$extra = "WikidataClasses.php?id=Q0&amp;lang=" . urlencode($params['lang']);
		$rootclasslink = "<a href=\"$protocol://$host$uri/$extra\">view root classes</a>";
	}
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title><?php echo 'Wikidata Class Browser' . $title ?></title>
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
		<div style="display: table; margin: 0 auto;">
		<h2>Wikidata Class Browser<?php echo $title ?></h2>
        <form action="WikidataClasses.php" method="post"><table class="form">
        <tr><td><b>Class item ID</b></td><td><input id="id" name="id" type="text" size="10" value="Q<?php echo $params['id'] ?>" /></td></tr>
        <tr><td><b>Name/description<br />language code</b></td><td><input id="lang" name="lang" type="text" size="4" value="<?php echo $params['lang'] ?>" /></td></tr>
        <tr><td><input type="submit" value="Submit" /></td><td><?php echo $rootclasslink ?></td></tr>
        </table>
        </form>
        <script type="text/javascript">
            if (document.getElementById) {
                var e = document.getElementById('id');
                e.focus();
                e.select();
            }
        </script>
        <br />
<?php
	if (! empty($subclasses)) {
		if ($params['id'] != 0 && empty($subclasses['class'])) {
			echo '<h2>Class not found</h2>';

		} else {
			$protocol = HttpUtil::getProtocol();
			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

			// Display class info
			if ($params['id'] == 0) {
				echo "Data as of: {$subclasses['dataasof']}<sup>[1]</sup><br />\n";
				echo "Class count<sup>[2]</sup>: " . intl_num_format($subclasses['classcnt']) . "<br />\n";

			} else {
				echo "<table><tbody>\n";
				$term_text = htmlentities($subclasses['class'][0], ENT_COMPAT, 'UTF-8');
				if ($term_text != "Q{$params['id']}") $term_text = "$term_text (Q{$params['id']})";
				$url = "https://www.wikidata.org/wiki/Q" . $params['id'];
				echo "<tr><td>Name:</td><td><a class='external' href='$url' title='Wikidata link'>$term_text</a></td></tr>\n";

				if (! empty($subclasses['class'][1])) {
					$term_desc = htmlentities($subclasses['class'][1], ENT_COMPAT, 'UTF-8');
					echo "<tr><td>Description:</td><td>$term_desc</td></tr>\n";
				}

				$sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
					"  ?s wdt:P279 wd:Q{$params['id']} .\n" .
					"  SERVICE wikibase:label { bd:serviceParam wikibase:language \"{$params['lang']}\" }\n" .
					"}\nORDER BY ?sLabel");

				$sparql = "&nbsp;&nbsp;&nbsp;(<a href='$sparql' class='external'>SPARQL query</a>)";

				echo "<tr><td>Direct subclasses:</td><td>" . intl_num_format($subclasses['class'][2]) . "$sparql</td></tr>\n";
				echo "<tr><td>Indirect subclasses:</td><td>" . intl_num_format($subclasses['class'][3]) . "</td></tr>\n";

				$sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
					"  ?s wdt:P31 wd:Q{$params['id']} .\n" .
					"  SERVICE wikibase:label { bd:serviceParam wikibase:language \"{$params['lang']}\" }\n" .
					"}\nORDER BY ?sLabel");

				$sparql = "&nbsp;&nbsp;&nbsp;(<a href='$sparql' class='external'>SPARQL query</a>)";

				echo "<tr><td>Direct instances:</td><td>" . intl_num_format($subclasses['class'][4]) . "$sparql</td></tr>\n";
				echo "<tr><td>Indirect instances:</td><td>" . intl_num_format($subclasses['class'][5]) . "</td></tr>\n";

				// Display parents
				if (! empty($subclasses['parents'])) {
					usort($subclasses['parents'], function($a, $b) {
						return strcmp(strtolower($a[1]), strtolower($b[1]));
					});

					$parents = array();

					foreach ($subclasses['parents'] as $row) {
						$extra = "WikidataClasses.php?id=Q" . $row[0] . "&amp;lang=" . urlencode($params['lang']);
						$term_text = htmlentities($row[1], ENT_COMPAT, 'UTF-8');
						if ($term_text != "Q{$row[0]}") $term_text = "$term_text (Q{$row[0]})";
						$parents[] = "<a href=\"$protocol://$host$uri/$extra\">$term_text</a>";
					}

					$parents = implode(', ', $parents);

					$parent_label = (count($subclasses['parents']) == 1) ? 'Parent class' : 'Parent classes';

					echo "<tr><td>$parent_label:</td><td>$parents</td></tr>\n";
				} else {
					echo "<tr><td>Parent class:</td><td>root class</td></tr>\n";
				}


				echo "<tr><td>Data as of:</td><td>{$subclasses['dataasof']}<sup>[1]</sup></td></tr>\n";
				echo "</tbody></table>\n";
			}

			// Display children
			if (! empty($subclasses['children'])) {
				if ($params['id'] == 0) {
					echo "<h2>Major root classes</h2>\n";
				} else {
					$child_label = (count($subclasses['children']) == 1) ? 'Direct subclass' : 'Direct subclasses';
					echo "<h2>$child_label</h2>\n";
					usort($subclasses['children'], function($a, $b) {
						return strcmp(strtolower($a[1]), strtolower($b[1]));
					});
				}

				echo "<table class='wikitable tablesorter'><thead><tr><th>Name</th><th>Wikidata link</th><th>Direct subclasses</th>" .
					"<th>Indirect subclasses</th><th>Direct instances</th><th>Indirect instances</th></tr></thead><tbody>\n";

				foreach ($subclasses['children'] as $row) {
					$extra = "WikidataClasses.php?id=Q" . $row[0] . "&amp;lang=" . urlencode($params['lang']);
					$classurl = "$protocol://$host$uri/$extra";
					$wdurl = "https://www.wikidata.org/wiki/Q" . $row[0];
					$term_text = htmlentities($row[1], ENT_COMPAT, 'UTF-8');
					echo "<tr><td><a href='$classurl'>$term_text</a></td><td data-sort-value='$row[0]'><a class='external' href='$wdurl'>Q{$row[0]}</a></td>" .
						"<td style='text-align:right' data-sort-value='$row[2]'>" . intl_num_format($row[2]) .
						"</td><td style='text-align:right' data-sort-value='$row[3]'>" . intl_num_format($row[3]) .
						"</td><td style='text-align:right' data-sort-value='$row[4]'>" . intl_num_format($row[4]) .
						"</td><td style='text-align:right' data-sort-value='$row[5]'>" . intl_num_format($row[5]) . "</td></tr>\n";
				}

				echo "</tbody></table>\n";

			} elseif ($subclasses['class'][2] > MAX_CHILD_CLASSES) {
				echo "<h2>&gt; " . MAX_CHILD_CLASSES . " subclasses</h2>\n";
			}
		}
	}
?>
       <br /><div><sup>1</sup>Data derived from database dump wikidatawiki-pages-articles.xml</div>
       <?php if ($params['id'] == 0) {?><div><sup>2</sup>Class count only includes classes that are a parent or child class. (no parentless classes other than root classes which have a child class)</div><?php } ?>
       <div>Note: Names/descriptions are cached, so changes may not be seen until the next data load.</div>
       <div>Note: Numbers are formatted with the ISO recommended international thousands separator 'thin space'.</div>
       <div>Note: Some totals may not balance due to a class having the same super-parent class multiple times.</div>
       <div>Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php

}

/**
 * Get subclasses
 */
function get_subclasses()
{
	global $params;

	$cachekey = CACHE_PREFIX_WDCLS . $params['id'] . '_' . $params['lang'];

	// Check the cache
	$results = FileCache::getData($cachekey);
	if (! empty($results)) {
		$results = unserialize($results);
		return $results;
	}

	$return = array();
	$parents = array();
	$children = array();
	$class = array();

	$wikiname = 'enwiki';
	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";

	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8", $user, $pass);
	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sth = $dbh_wiki->query("SELECT * FROM s51454__wikidata.subclasstotals WHERE qid = 0");

	$row = $sth->fetch(PDO::FETCH_NUM);
	$year = $row[2];
	$month = $row[3]; if ($month < 10) $month = "0$month";
	$day = $row[4]; if ($day < 10) $day = "0$day";
	$classcnt = $row[5];
	$dataasof = "$year-$month-$day";

	// Retrieve the class
	if ($params['id'] != 0) {
		$sql = "SELECT wbt.term_text AS lang_text, wbten.term_text AS en_text, wbd.term_text AS lang_desc, wbden.term_text AS en_desc, " .
				" sct.directchildcnt, sct.indirectchildcnt, sct.directinstcnt, sct.indirectinstcnt " .
				" FROM s51454__wikidata.subclasstotals sct " .
				" LEFT JOIN wikidatawiki_p.wb_terms wbt ON sct.qid = wbt.term_entity_id AND wbt.term_entity_type = 'item' " .
				" AND wbt.term_type = 'label' AND wbt.term_language = ? " .
				" LEFT JOIN wikidatawiki_p.wb_terms wbten ON sct.qid = wbten.term_entity_id AND wbten.term_entity_type = 'item' " .
				" AND wbten.term_type = 'label' AND wbten.term_language = 'en' " .
				" LEFT JOIN wikidatawiki_p.wb_terms wbd ON sct.qid = wbd.term_entity_id AND wbd.term_entity_type = 'item' " .
				" AND wbd.term_type = 'description' AND wbd.term_language = ? " .
				" LEFT JOIN wikidatawiki_p.wb_terms wbden ON sct.qid = wbden.term_entity_id AND wbden.term_entity_type = 'item' " .
				" AND wbden.term_type = 'description' AND wbden.term_language = 'en' " .
				" WHERE sct.qid = ?";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['lang']);
		$sth->bindValue(2, $params['lang']);
		$sth->bindValue(3, $params['id']);

		$sth->execute();

		if ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$term_text = $row['lang_text'];
			if (is_null($term_text)) $term_text = $row['en_text'];
			if (is_null($term_text)) $term_text = 'Q' . $params['id'];

			$term_desc = $row['lang_desc'];
			if (is_null($term_desc)) $term_desc = $row['en_desc'];
			if (is_null($term_desc)) $term_desc = '';

			$class = array($term_text, $term_desc, $row['directchildcnt'], $row['indirectchildcnt'],
					$row['directinstcnt'], $row['indirectinstcnt']);
		}
	}

	// Retrieve the parent classes
	if ($params['id'] != 0) {
		$sql = "SELECT scc.parent_qid, wbt.term_text AS lang_text, wbten.term_text AS en_text " .
			" FROM s51454__wikidata.subclassclasses scc " .
			" LEFT JOIN wikidatawiki_p.wb_terms wbt ON scc.parent_qid = wbt.term_entity_id AND wbt.term_entity_type = 'item' " .
			" AND wbt.term_type = 'label' AND wbt.term_language = ? " .
			" LEFT JOIN wikidatawiki_p.wb_terms wbten ON scc.parent_qid = wbten.term_entity_id AND wbten.term_entity_type = 'item' " .
			" AND wbten.term_type = 'label' AND wbten.term_language = 'en' " .
			" WHERE scc.child_qid = ? ";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['lang']);
		$sth->bindValue(2, $params['id']);

		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$term_text = $row['lang_text'];
			if (is_null($term_text)) $term_text = $row['en_text'];
			if (is_null($term_text)) $term_text = 'Q' . $row['parent_qid'];

			$parents[] = array($row['parent_qid'], $term_text);
		}
	}

	// Retrieve the child classes
	if ($params['id'] == 0) {
		$en_text = '';
		if ($params['lang'] != 'en') $en_text = 'wbten.term_text AS en_text,';

		$sql = "SELECT sct.qid, wbt.term_text AS lang_text, $en_text ";
		$sql .= " sct.directchildcnt, sct.indirectchildcnt, sct.directinstcnt, sct.indirectinstcnt ";
		$sql .= " FROM s51454__wikidata.subclasstotals sct ";
		$sql .= " LEFT JOIN wikidatawiki_p.wb_terms wbt ON sct.qid = wbt.term_entity_id AND wbt.term_entity_type = 'item' ";
		$sql .= " AND wbt.term_type = 'label' AND wbt.term_language = ? ";
		if ($params['lang'] != 'en') $sql .= " LEFT JOIN wikidatawiki_p.wb_terms wbten ON sct.qid = wbten.term_entity_id AND wbten.term_entity_type = 'item' ";
		if ($params['lang'] != 'en') $sql .= " AND wbten.term_type = 'label' AND wbten.term_language = 'en' ";
		$sql .= " WHERE sct.root = 'Y' and sct.directchildcnt + sct.indirectchildcnt + sct.directinstcnt + sct.indirectinstcnt > 100 ";
		$sql .= " ORDER BY sct.directchildcnt + sct.indirectchildcnt + sct.directinstcnt + sct.indirectinstcnt DESC";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['lang']);

	} elseif (! empty($class) && $class[2] <= MAX_CHILD_CLASSES) { // directchildcnt
		$en_text = '';
		if ($params['lang'] != 'en') $en_text = 'wbten.term_text AS en_text,';

		$sql = "SELECT scc.child_qid AS qid, wbt.term_text AS lang_text, $en_text ";
		$sql .= " sct.directchildcnt, sct.indirectchildcnt, sct.directinstcnt, sct.indirectinstcnt ";
		$sql .= " FROM s51454__wikidata.subclassclasses scc ";
		$sql .= " JOIN s51454__wikidata.subclasstotals sct ON sct.qid = scc.child_qid ";
		$sql .= " LEFT JOIN wikidatawiki_p.wb_terms wbt ON scc.child_qid = wbt.term_entity_id AND wbt.term_entity_type = 'item' ";
		$sql .= " AND wbt.term_type = 'label' AND wbt.term_language = ? ";
		if ($params['lang'] != 'en') $sql .= " LEFT JOIN wikidatawiki_p.wb_terms wbten ON scc.child_qid = wbten.term_entity_id AND wbten.term_entity_type = 'item' ";
		if ($params['lang'] != 'en') $sql .= " AND wbten.term_type = 'label' AND wbten.term_language = 'en' ";
		$sql .= " WHERE scc.parent_qid = ?";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['lang']);
		$sth->bindValue(2, $params['id']);
	}

	$sth->execute();

	while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		$term_text = $row['lang_text'];
		if (is_null($term_text) && $params['lang'] != 'en') $term_text = $row['en_text'];
		if (is_null($term_text)) $term_text = 'Q' . $row['qid'];

		$children[] = array($row['qid'], $term_text, $row['directchildcnt'], $row['indirectchildcnt'],
			$row['directinstcnt'], $row['indirectinstcnt']);
	}

	$return = array('class' => $class, 'parents' => $parents, 'children' => $children, 'dataasof' => $dataasof, 'classcnt' => $classcnt);

	$serialized = serialize($return);

	FileCache::putData($cachekey, $serialized);

	return $return;
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = array();

	$params['id'] = (isset($_REQUEST['id']) && ! empty($_REQUEST['id'])) ? $_REQUEST['id'] : '0';
	if (! is_numeric($params['id'][0])) $params['id'] = substr($params['id'], 1);
	if (empty($params['id'])) $params['id'] = '0';
	$params['id'] = intval($params['id']);
	$params['lang'] = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : '';

	if (! empty($params['lang']) && preg_match('!([a-zA-Z]+)!', $params['lang'], $matches)) {
		$params['lang'] = strtolower($matches[1]);
	}

	if (empty($params['lang']) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('!([a-zA-Z]+)!', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
		$params['lang'] = strtolower($matches[1]);
	}
	if (empty($params['lang'])) $params['lang'] = 'en';
}

/**
 * Format an integer
 *
 * @param int $number
 */
function intl_num_format($number)
{
	return number_format($number, 0, '', '&thinsp;');
}

?>