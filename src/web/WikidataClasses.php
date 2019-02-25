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
use com_brucemyers\MediaWiki\WikidataItem;
use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;
use com_brucemyers\Util\Logger;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
define('CACHE_PREFIX_WDCLS', 'WDCLS:');
define('MAX_CHILD_CLASSES', 500);
define('MIN_ORPHAN_DIRECT_INST_CNT', 5);
define('PROP_INSTANCEOF', 'P31');

$instanceofIgnores = array('Q13406463','Q11266439'); // Wikimedia list article, Wikimedia template

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = array();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
	case 'suggest':
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
		$callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : '';
		$userlang = isset($_REQUEST['userLang']) ? $_REQUEST['userLang'] : '';
		if ($lang && $page && $callback) perform_suggest($lang, $page, $callback, $userlang);
		exit;
}

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
		$title = ' : Widely used root classes';
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
				echo "Data as of: {$subclasses['dataasof']}<br />\n";
				echo "Class count: " . intl_num_format($subclasses['classcnt']) . "<br />\n";
				echo "Root count<sup>[1]</sup>: " . intl_num_format($subclasses['rootcnt']) . "<br />\n";

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

				if ($subclasses['class'][6] != 0) {
					$sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
						"  ?s wdt:P360 wd:Q{$params['id']} .\n" .
						"  SERVICE wikibase:label { bd:serviceParam wikibase:language \"{$params['lang']}\" }\n" .
						"}\nORDER BY ?sLabel");

					$sparql = "&nbsp;&nbsp;&nbsp;(<a href='$sparql' class='external'>SPARQL query</a>)";

					echo "<tr><td>Lists of:</td><td>" . intl_num_format($subclasses['class'][6]) . "$sparql</td></tr>\n";
				}

				echo "</tbody></table>\n";
			}

			// Display children
			if (! empty($subclasses['children'])) {
				if ($params['id'] == 0) {
					echo "<h2>Widely used root classes</h2>\n";
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

			// Display popular properties
			if (! empty($subclasses['pop_props'])) {
				echo "<h2>Most common properties for this class</h2>\n";

				echo "<table class='wikitable tablesorter'><thead><tr><th>Property</th><th>Count</th><th>Percentage</th><th>Missing property</th></tr></thead><tbody>\n";

				foreach ($subclasses['pop_props'] as $pid => $row) {
					$wdurl = "https://www.wikidata.org/wiki/" . $pid;
					$pid = substr($pid, 9);
					$term_text = htmlentities($row[0], ENT_COMPAT, 'UTF-8');

					$sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
						"  ?s wdt:P31 wd:Q{$params['id']} .\n" .
						"  OPTIONAL { ?s p:$pid ?prop }\n" .
						"  FILTER ( !bound(?prop) )\n" .
						"  SERVICE wikibase:label { bd:serviceParam wikibase:language \"{$params['lang']}\" }\n" .
						"}\nORDER BY ?sLabel");

					$sparql = "<a href='$sparql' class='external'>SPARQL query</a>";

					echo "<tr><td><a class='external' href='$wdurl'>$term_text</a></td>" .
						"<td style='text-align:right' data-sort-value='$row[1]'>" . intl_num_format($row[1]) .
						"</td><td style='text-align:right'>" . $row[2] . "</td><td style='text-align:center'>$sparql</td></tr>\n";
				}

				echo "</tbody></table>\n";
			}

			echo "<br /><div>Data as of: {$subclasses['dataasof']}</div>";
		}
	}
?>
       <div>Data derived from database dump wikidatawiki-pages-articles.xml and Wikibase table wbs_propertypairs.</div>
       <?php if ($params['id'] == 0) {?><div><sup>1</sup>Root classes with no child classes and less than <?php echo MIN_ORPHAN_DIRECT_INST_CNT; ?> instances are excluded.</div><?php } ?>
       <div>Note: Names/descriptions are cached, so changes may not be seen until the next data load.</div>
       <div>Note: Numbers are formatted with the ISO recommended international thousands separator 'thin space'.</div>
       <div>Note: Some totals may not balance due to a class having the same super-parent class multiple times.</div>
       <div>Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
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
	$pop_props = array();

	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "tools.db.svc.eqiad.wmflabs";

	try {
	   $dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=s51454__wikidata;charset=utf8", $user, $pass);
	} catch (PDOException $e) {
	    Logger::log($e->getMessage());
	    throw new Exception('Connection error, see log for details');
	}
	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sth = $dbh_wiki->query("SELECT * FROM s51454__wikidata.subclasstotals WHERE qid = 0");

	$row = $sth->fetch(PDO::FETCH_NUM);
	$classcnt = $row[2];
	$rootcnt = $row[3];
	$year = $row[4];
	$month_day = $row[5];
	$month = floor($month_day / 100);
	if ($month < 10) $month = "0$month";
	$day = $month_day % 100;
	if ($day < 10) $day = "0$day";
	$dataasof = "$year-$month-$day";

	$wdwiki = new WikidataWiki();

	// Retrieve the class
	if ($params['id'] != 0) {
		$sql = "SELECT directchildcnt, indirectchildcnt, directinstcnt, indirectinstcnt, islistofcnt " .
				" FROM s51454__wikidata.subclasstotals " .
				" WHERE qid = ? LIMIT 1";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['id']);

		$sth->execute();

		if ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$class = array('Q' . $params['id'], '', $row['directchildcnt'], $row['indirectchildcnt'],
					$row['directinstcnt'], $row['indirectinstcnt'], $row['islistofcnt']);

			$items = $wdwiki->getItemsNoCache($class[0]);

			if (! empty($items)) {
				$term_text = $items[0]->getLabelDescription('label', $params['lang']);
				if (! empty($term_text)) $class[0] = $term_text;
				$class[1] = $items[0]->getLabelDescription('description', $params['lang']);
			}
		}
	}

	// Retrieve the parent classes
	if ($params['id'] != 0) {
		$sql = "SELECT parent_qid ";
		$sql .= " FROM s51454__wikidata.subclassclasses ";
		$sql .= " WHERE child_qid = ? ";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['id']);

		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$parents['Q' . $row['parent_qid']] = array($row['parent_qid'], 'Q' . $row['parent_qid']); // removes dup terms
		}

		if (! empty($parents)) {
			$parent_ids = array_keys($parents);
			$items = $wdwiki->getItemsNoCache($parent_ids);

			foreach ($items as $item) {
				$qid = $item->getId();
				$term_text = $item->getLabelDescription('label', $params['lang']);

				if (! empty($term_text)) $parents[$qid][1] = $term_text;
			}
		}

		// Retrieve popular properties
		$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
		if (empty($wiki_host)) $wiki_host = "wikidatawiki.web.db.svc.eqiad.wmflabs";
		$dbh_wikidata = new PDO("mysql:host=$wiki_host;dbname=wikidatawiki_p;charset=utf8", $user, $pass);
		$dbh_wikidata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$sql = "SELECT pid2, count, probability FROM wbs_propertypairs WHERE pid1 = 31 AND qid1 = ? AND context = 'item' ORDER BY probability DESC LIMIT 10";
		$sth = $dbh_wikidata->prepare($sql);
		$sth->bindValue(1, $params['id']);

		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$pop_props['Property:P' . $row['pid2']] = array('P' . $row['pid2'], $row['count'], floor($row['probability'] * 100));
		}

		if (! empty($pop_props)) {
			$prop_ids = array_keys($pop_props);
			$items = $wdwiki->getItemsNoCache($prop_ids);

			foreach ($items as $item) {
				$pid = $item->getId();
				$term_text = $item->getLabelDescription('label', $params['lang']);

				if (! empty($term_text)) $pop_props['Property:' . $pid][0] = $term_text;
			}
		}
	}

	// Retrieve the child classes
	$sql = '';

	if ($params['id'] == 0) {
		$sql = "SELECT qid, directchildcnt, indirectchildcnt, directinstcnt, indirectinstcnt ";
		$sql .= " FROM s51454__wikidata.subclasstotals ";
		$sql .= " WHERE root = 'Y' ";
		$sql .= " ORDER BY directchildcnt + indirectchildcnt + directinstcnt + indirectinstcnt DESC LIMIT 200";

		$sth = $dbh_wiki->prepare($sql);

	} elseif (! empty($class) && $class[2] <= MAX_CHILD_CLASSES) { // directchildcnt
		$sql = "SELECT scc.child_qid AS qid, sct.directchildcnt, sct.indirectchildcnt, sct.directinstcnt, sct.indirectinstcnt ";
		$sql .= " FROM s51454__wikidata.subclassclasses scc ";
		$sql .= " JOIN s51454__wikidata.subclasstotals sct ON sct.qid = scc.child_qid ";
		$sql .= " WHERE scc.parent_qid = ?";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['id']);
	}

	if (! empty($sql)) {
		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$children['Q' . $row['qid']] = array($row['qid'], 'Q' . $row['qid'], $row['directchildcnt'], $row['indirectchildcnt'],
				$row['directinstcnt'], $row['indirectinstcnt']); // removes dup terms
		}
	}

	if (! empty($children)) {
		$child_ids = array_keys($children);
		$items = $wdwiki->getItemsNoCache($child_ids);

		foreach ($items as $item) {
			$qid = $item->getId();
			$term_text = $item->getLabelDescription('label', $params['lang']);

			if (! empty($term_text)) $children[$qid][1] = $term_text;
		}
	}

	$return = array('class' => $class, 'parents' => $parents, 'children' => $children, 'dataasof' => $dataasof,
		'classcnt' => $classcnt, 'rootcnt' => $rootcnt, 'pop_props' => $pop_props);

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

	$params['id'] = '0';
	if (isset($_REQUEST['id'])) $params['id'] = trim($_REQUEST['id']);
	if (empty($params['id'])) $params['id'] = '0';
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

/**
 * Suggest a class for an items instanceOf property
 *
 * @param string $lang
 * @param string $page
 * @param string $callback
 * @param string $userlang
 * @return JSONP
 */
function perform_suggest($lang, $page, $callback, $userlang)
{
	global $instanceofIgnores;
	header('content-type: application/json; charset=utf-8');
	header('access-control-allow-origin: *');

	$lang = preg_replace('!\W!', '', $lang);
	if (! $userlang) $userlang = 'en';
	$userlang = preg_replace('!\W!', '', $userlang);

	$wikiname = "{$lang}wiki";
	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "$wikiname.web.db.svc.eqiad.wmflabs";

	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8", $user, $pass);
	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$page = str_replace(' ', '_', $page);

	// Retrieve the pages 3 smallest categories with min 5 pages and no year in cat name
	$sql = 'SELECT cat_title ' .
		' FROM page ' .
		' JOIN categorylinks cl ON page.page_id = cl_from ' .
		' JOIN category cat ON cl_to = cat_title ' .
		' LEFT JOIN page catpage ON cat_title = catpage.page_title ' .
		" LEFT JOIN page_props ON pp_page = catpage.page_id AND pp_propname = 'hiddencat' " .
		' WHERE page.page_namespace = 0 AND page.page_title = ? ' .
		' AND catpage.page_namespace = 14 AND pp_value IS NULL ' .
		' AND cat_pages - (cat_subcats + cat_files) >= 5 ' .
		" AND cat_title NOT REGEXP '[[:digit:]]{4}' " .
		' ORDER BY cat_pages - (cat_subcats + cat_files) ' .
		' LIMIT 3 ';

	$sth = $dbh_wiki->prepare($sql);
	$sth->bindValue(1, $page);

	$sth->execute();
	$sth->setFetchMode(PDO::FETCH_NAMED);
	$cats = array();

	while ($row = $sth->fetch()) {
		$cats[$row['cat_title']] = array('qids' => array(), 'instanceofs' => array());
	}

	$sth->closeCursor();

	if (! $cats) {
		echo "/**/$callback({});";
		return;
	}

	// Retrieve the category member qids
	$qids = array();

	foreach ($cats as $cat => $dummy) {
		$sql = 'SELECT pp_value ' .
			' FROM categorylinks ' .
			' JOIN page_props ON cl_from = pp_page ' .
	       	" WHERE cl_to = ? AND pp_propname = 'wikibase_item' AND cl_type = 'page' " .
	       	' ORDER BY cl_sortkey_prefix ' . // weed out * sort key etc
			' LIMIT 10 ';

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $cat);

		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_NUM);

		while ($row = $sth->fetch()) {
			$qid = $row[0];
			$qids[$qid] = true; // removes dups
			$cats[$cat]['qids'][] = $qid;
		}

		$sth->closeCursor();
	}

	if (! $qids) {
		echo "/**/$callback({});";
		return;
	}

	// Retrieve the item claims and look for instance of
	$wdwiki = new WikidataWiki();

	$items = $wdwiki->getItemsNoCache(array_keys($qids));

	foreach ($items as $item) {
		$propvalues = $item->getStatementsOfType(WikidataItem::TYPE_INSTANCE_OF);
		$itemqid = $item->getId();

		foreach ($propvalues as $qid) {
			if (in_array($qid, $instanceofIgnores)) continue;

			foreach ($cats as $cat => $data) {
				if (in_array($itemqid, $data['qids'])) {
					if (! isset($cats[$cat]['instanceofs'][$qid])) {
						$cats[$cat]['instanceofs'][$qid] = array('qid' => $qid, 'catcnt' => 0, 'label' => 'Q' . $qid, 'desc' => '');
					}
					++$cats[$cat]['instanceofs'][$qid]['catcnt'];
				}
			}
		}
	}

	foreach ($cats as $cat => $data) {
		foreach ($data['instanceofs'] as $qid => $info) {
			if ($info['catcnt'] == 1) unset($cats[$cat]['instanceofs'][$qid]);
		}
		if (! $cats[$cat]['instanceofs']) unset($cats[$cat]);
	}

	if (! $cats) {
		echo "/**/$callback({});";
		return;
	}

	// Reverse sort on catcnt
	foreach ($cats as &$data) {
		uasort($data['instanceofs'], function($a, $b) {
			$acatcnt = $a['catcnt'];
			$bcatcnt = $b['catcnt'];
			if ($acatcnt > $bcatcnt) return -1;
			if ($acatcnt < $bcatcnt) return 1;
			return 0;
		});
	}
	unset($data);

	// Take the top 2 instanceofs per category

	$instanceofs = array();

	foreach ($cats as $data) {
		$x = 0;

		foreach ($data['instanceofs'] as $qid => $instanceof) {
			$instanceofs[$qid] = $instanceof;
			if (++$x == 2) break;
		}
	}

	// Retrieve the name and description
	$found_qids = array();
	$qids = array_keys($instanceofs);
	$items = $wdwiki->getItemsWithCache($qids);

	foreach ($items as $item) {
		$qid = $item->getId();
		$found_qids[] = $qid;
		$term_text = $item->getLabelDescription('label', $userlang);
		if (empty($term_text)) $term_text = $qid;
		$term_desc = $item->getLabelDescription('description', $userlang);

		$instanceofs[$qid]['label'] = $term_text;
		$instanceofs[$qid]['desc'] = $term_desc;
	}

	foreach ($instanceofs as $qid => $dummy) {
		if (! in_array($qid, $found_qids)) {
			unset($instanceofs[$qid]); // not a class
			$qids = array_keys($instanceofs);
		}
	}

	$sth->closeCursor();

	if (! $qids) {
		echo "/**/$callback({});";
		return;
	}

	// Retrieve the child classes
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "tools.db.svc.eqiad.wmflabs";

	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=s51454__wikidata;charset=utf8", $user, $pass);
	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$num_qids = array();
	foreach ($qids as $qid) {
		$num_qids[] = substr($qid, 1);
	}

	$sql = "SELECT DISTINCT scc.child_qid AS child_qid, scc.parent_qid AS parent_qid ";
	$sql .= " FROM s51454__wikidata.subclassclasses scc ";
	$sql .= " JOIN s51454__wikidata.subclasstotals sct ON sct.qid = scc.child_qid ";
	$sql .= " WHERE scc.parent_qid IN (" . implode(',', $num_qids) . ") AND sct.directinstcnt + sct.indirectinstcnt > 0 ";
	$sql .= " ORDER BY sct.directinstcnt + sct.indirectinstcnt DESC ";
	$sql .= " LIMIT 10 ";

	$sth = $dbh_wiki->prepare($sql);
	$sth->execute();
	$sth->setFetchMode(PDO::FETCH_NAMED);

	while ($row = $sth->fetch()) {
		$child_qid = "Q{$row['child_qid']}";
		$parent_qid = "Q{$row['parent_qid']}";

		$item = $wdwiki->getItemWithCache($child_qid);
		if ($item->getId() == '') continue;

		$term_text = $item->getLabelDescription('label', $userlang);
		if (empty($term_text)) continue;

		$term_desc = $item->getLabelDescription('description', $userlang);

		if (! isset($instanceofs[$parent_qid]['childs'])) $instanceofs[$parent_qid]['childs'] = array();
		$instanceofs[$parent_qid]['childs'][] = array('qid' => $child_qid, 'label' => $term_text, 'desc' => $term_desc);
	}

	$sth->closeCursor();

	// Reverse sort on catcnt
	usort($instanceofs, function($a, $b) {
		$acatcnt = $a['catcnt'];
		$bcatcnt = $b['catcnt'];
		if ($acatcnt > $bcatcnt) return -1;
		if ($acatcnt < $bcatcnt) return 1;
		return 0;
	});

	echo "/**/$callback(" . json_encode($instanceofs) . ");";
}
?>