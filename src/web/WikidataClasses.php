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
use com_brucemyers\MediaWiki\MediaWiki;

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

$params = [];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
	case 'suggest':
		$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
		$callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : '';
		$userlang = isset($_REQUEST['userLang']) ? $_REQUEST['userLang'] : '';
		if ($lang && $page && $callback) perform_suggest($lang, $page, $callback, $userlang);
		exit;

	case 'enumqualifiers':
	    $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	    $pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : '';
	    $lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
	    display_enum_qualifiers($id, $pid, $lang);
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
		<script type='text/javascript' src='js/jquery.autocomplete.min.js'></script>
		<style>
            .autocomplete-suggestions { border: 1px solid #999; background: #FFF; }
            .autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; cursor: pointer; }
		</style>
	</head>
	<body>
		<script type='text/javascript'>
    		function escapeHtml(text) {
    			var map = {
    			   '&': '&amp;',
    			    '<': '&lt;',
    			    '>': '&gt;',
    			    '"': '&quot;',
    			    "'": '&#039;'
    			};

    			return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    		}

			$(document).ready(function()
			    {
		        $('.tablesorter').tablesorter();

		        $('#labelalias').autocomplete({
		            serviceUrl: 'https://www.wikidata.org/w/api.php',
		            paramName: 'search',
		            dataType: 'json',
		            params: {action: 'wbsearchentities', format: 'json', origin: '*', type: 'item'},
		            width: 'flex',

		            onSearchStart: function(params) {
			            var val = $("#lang").val();
			            if (val == '') val = 'en';
			            params.language = val;
			            params.uselang = val;
		            },

		            transformResult: function(response) {
		                return {
		                    suggestions: $.map(response.search, function(di) {
			                    var data = {id: di.id, desc: '', alias: ''};
			                    if (di.description !== undefined) data.desc = di.description;
			                    if (di.match.type == 'alias') data.alias = di.match.text;
		                        return { value: di.label, data: data };
		                    })
		                };
		            },

		            formatResult: function(s, cv) {
			            var ret = '<div><b>' + escapeHtml(s.value);
			            if (s.data.alias != '') ret += ' <i>(' + escapeHtml(s.data.alias) + ')</i>';
			            ret += '</b></div>';
				        if (s.data.desc != '') ret += '<div>' + escapeHtml(s.data.desc) + '</div>';
				        return ret;
		            },

		            onSelect: function (suggestion) {
		            	$("#id").val(suggestion.data.id);
		            }
		        });
		        }
			);
		</script>
		<div style="display: table; margin: 0 auto;">
		<h2><a href="WikidataClasses.php">Wikidata Class Browser</a><?php echo $title ?></h2>
        <form action="WikidataClasses.php" method="post"><table class="form">
        <tr><td><b>Class item ID</b></td><td><input id="id" name="id" type="text" size="10" value="Q<?php echo $params['id'] ?>" /> or <b>Label/Alias</b> <input id="labelalias" name="labelalias" type="text" size="15" /></td></tr>
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
				
				$sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel ?isTruthy WHERE {\n" .
				    "  ?s p:P279 ?stmt .\n" .
				    "  ?stmt ps:P279 wd:Q{$params['id']} .\n" .
				    "  BIND( EXISTS { ?stmt a wikibase:BestRank } AS ?isTruthy ) .\n" .
				    "  SERVICE wikibase:label { bd:serviceParam wikibase:language \"{$params['lang']}\" }\n" .
					"}\nORDER BY ?sLabel");

				$sparql = "&nbsp;&nbsp;&nbsp;(<a href='$sparql' class='external'>SPARQL query</a>)";

				echo "<tr><td>Direct subclasses:</td><td>" . intl_num_format($subclasses['class'][2]) . "$sparql</td></tr>\n";
				echo "<tr><td>Indirect subclasses:</td><td>" . intl_num_format($subclasses['class'][3]) . "</td></tr>\n";

				$sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel ?isTruthy WHERE {\n" .
				    "  ?s p:P31 ?stmt .\n" .
				    "  ?stmt ps:P31 wd:Q{$params['id']} .\n" .
				    "  BIND( EXISTS { ?stmt a wikibase:BestRank } AS ?isTruthy ) .\n" .
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

					$sparql2 = <<<EOT
SELECT ?qualifierLabel ?count ?sample ?sampleLabel
{
  {  SELECT (SAMPLE(?item) as ?sample) ?qualifier (COUNT(?item) as ?count)
  {
    hint:Query hint:optimizer "None".
    ?item p:P360 [ ps:P360 wd:Q{$params['id']} ; ?pq ?qv ] .
    ?qualifier wikibase:qualifier ?pq .
    ?item wdt:P31 wd:Q13406463
  }
  GROUP BY ?qualifier
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],{$params['lang']},en". }
  FILTER(?count > 4)
}
ORDER BY DESC(?count)
EOT;

					$sparql2 = 'https://query.wikidata.org/#' . rawurlencode($sparql2);

					$sparql2 = "&nbsp;&nbsp;&nbsp;List type qualifiers:&nbsp;<a href='$sparql2' class='external'>SPARQL query</a>";

					echo "<tr><td>Lists of:</td><td>" . intl_num_format($subclasses['class'][6]) . "$sparql$sparql2</td></tr>\n";
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

				echo "<table class='wikitable tablesorter'><thead><tr><th>Property</th><th>Instance<br />count</th><th>Percentage</th>";
			    echo "<th>Missing property</th><th>Enumerated<br />value<br />count</th><th>Qualifier<br />count</th><th>Enumerated<br />values /<br />qualifiers</th></tr></thead><tbody>\n";

				foreach ($subclasses['pop_props'] as $pid => $row) {
					$wdurl = "https://www.wikidata.org/wiki/" . $pid;
					$pid = substr($pid, 9);
					$term_text = htmlentities($row[0], ENT_COMPAT, 'UTF-8');

					$sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
						"  ?s p:P31 ?stmt .\n" .
                        "  ?stmt ps:P31 wd:Q{$params['id']} .\n" .
						"  OPTIONAL { ?s wdt:$pid ?prop }\n" .
						"  FILTER ( !bound(?prop) )\n" .
						"  SERVICE wikibase:label { bd:serviceParam wikibase:language \"{$params['lang']}\" }\n" .
						"}\nORDER BY ?sLabel");

					$sparql = "<a href='$sparql' class='external'>SPARQL query</a>";

					echo "<tr><td><a class='external' href='$wdurl'>$term_text</a></td>" .
						"<td style='text-align:right' data-sort-value='$row[1]'>" . intl_num_format($row[1]) .
						"</td><td style='text-align:right'>" . $row[2] . "</td><td style='text-align:center'>$sparql</td><td style='text-align:right' ";

					if ($row[3] == 0) echo "data-sort-value='0'>&nbsp;";
					elseif ($row[3] == -1) echo "data-sort-value='50'>&gt; 50";
					else echo "data-sort-value='{$row[3]}'>{$row[3]}";

					echo "</td><td style='text-align:right'>{$row[4]}</td><td style='text-align:center'>";

					if ($row[4] != 0 || $row[3] > 0) echo "<a href='WikidataClasses.php?action=enumqualifiers&lang={$params['lang']}&id=Q{$params['id']}&pid=$pid'>view</a>";
					else echo '&nbsp;';

				    echo "</td></tr>\n";
				}

				echo "</tbody></table>\n";
			}

			echo "<br /><div>Data as of: {$subclasses['dataasof']}</div>";
		}
	}
?>
       <div>Data derived from database dump wikidatawiki-pages-articles.xml.</div>
       <?php if ($params['id'] == 0) {?><div><sup>1</sup>Root classes with no child classes and less than <?php echo MIN_ORPHAN_DIRECT_INST_CNT; ?> instances are excluded.</div><?php } ?>
       <div>Note: Names/descriptions are cached, so changes may not be seen until the next data load.</div>
       <div>Note: Numbers are formatted with the ISO recommended international thousands separator 'thin space'.</div>
       <div>Note: Some totals may not balance due to a class having the same super-parent class multiple times.</div>
       <div><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
}

/**
 * Display enumerations and qualifiers
 *
 */
function display_enum_qualifiers($id, $pid, $lang)
{
    $wdwiki = new WikidataWiki();
    $title = '';

    // Get the class label
    $item = $wdwiki->getItemWithCache($id);

    if (! empty($item)) {
        $class_label = $item->getLabelDescription('label', $lang);
        if (empty($class_label)) $class_label = $id;
        else $class_label = "$class_label ($id)";
        $title = " : $class_label";
    }

    // Get the property label
    $item = $wdwiki->getItemWithCache("Property:$pid");

    if (! empty($item)) {
        $prop_label = $item->getLabelDescription('label', $lang);
        if (empty($prop_label)) $prop_label = $pid;
        else $prop_label = "$prop_label ($pid)";
        $title .= " : $prop_label";
    }

    $title = htmlentities($title, ENT_COMPAT, 'UTF-8');

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    ?>
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
		<h2><a href="WikidataClasses.php">Wikidata Class Browser</a><?php echo $title ?></h2>
        <br />
		<?php
        echo "<table><tbody>\n";

        $term_text = htmlentities($class_label, ENT_COMPAT, 'UTF-8');
        $url = "https://www.wikidata.org/wiki/" . $id;
        echo "<tr><td>Class:</td><td><a class='external' href='$url' title='Wikidata link'>$term_text</a></td></tr>\n";

        $term_text = htmlentities($prop_label, ENT_COMPAT, 'UTF-8');
        $url = "https://www.wikidata.org/wiki/Property:" . $pid;
        echo "<tr><td>Property:</td><td><a class='external' href='$url' title='Wikidata link'>$term_text</a></td></tr>\n";

        echo "</tbody></table>\n";

        // Get the enum values
        $user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
        $pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
        $wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
        if (empty($wiki_host)) $wiki_host = "tools.db.svc.eqiad.wmflabs";

        try {
            $dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=s51454__wikidata;charset=utf8mb4", $user, $pass);
        } catch (PDOException $e) {
            Logger::log($e->getMessage());
            throw new Exception('Connection error, see log for details');
        }
        $dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT value, valcount ";
        $sql .= " FROM s51454__wikidata.subclassvalues ";
        $sql .= " WHERE qid = ? AND pid = ? AND qualpid = 0 AND value NOT IN ('C','0') ORDER BY valcount DESC ";

        $sth = $dbh_wiki->prepare($sql);
        $numeric_id = substr($id, 1);
        $numeric_pid = substr($pid, 1);
        $sth->bindValue(1, $numeric_id);
        $sth->bindValue(2, $numeric_pid);

        $sth->execute();
        $enums = [];

        while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
            $valid = "Q{$row['value']}";
            $valcount = $row['valcount'];
            $enums[$valid] = [$valid, $valcount];
        }

        if (! empty($enums)) {
            // Get the labels
            $enum_ids = array_keys($enums);
            $items = $wdwiki->getItemsWithCache($enum_ids);

            foreach ($items as $item) {
                $qid = $item->getId();
                $term_text = $item->getLabelDescription('label', $lang);

                if (! empty($term_text)) $enums[$qid][0] = $term_text;
            }

            echo "<h2>Enumerated values</h2>\n";

            echo "<table class='wikitable tablesorter'><thead><tr><th>Value</th><th>Instance<br />count</th><th>Instances<br />with value</th></tr></thead><tbody>\n";

            foreach ($enums as $valid => $enum) {
                $wdurl = "https://www.wikidata.org/wiki/" . $valid;
                $term_text = htmlentities($enum[0], ENT_COMPAT, 'UTF-8');

                $sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
                    "  ?s p:P31 ?stmt .\n" .
                    "  ?stmt ps:P31 wd:$id .\n" .
                    "  ?s wdt:$pid wd:$valid .\n" .
                    "  SERVICE wikibase:label { bd:serviceParam wikibase:language \"$lang\" }\n" .
                    "}\nORDER BY ?sLabel");

                $sparql = "<a href='$sparql' class='external'>SPARQL query</a>";

                echo "<tr><td><a class='external' href='$wdurl'>$term_text</a></td><td style='text-align:right' data-sort-value='{$enum[1]}'>" .
                intl_num_format($enum[1]) ."</td><td style='text-align:center'>$sparql</td></tr>";
            }

            echo "</tbody></table>\n";
        }

        // Get qualifiers
        $sql = "SELECT qualpid, valcount ";
        $sql .= " FROM s51454__wikidata.subclassvalues ";
        $sql .= " WHERE qid = ? AND pid = ? AND qualpid <> 0 AND value = 'C' ORDER BY valcount DESC LIMIT 100";

        $sth = $dbh_wiki->prepare($sql);
        $sth->bindValue(1, $numeric_id);
        $sth->bindValue(2, $numeric_pid);

        $sth->execute();
        $qualids = [];
        $quals = [];

        while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
            $qualid = $row['qualpid'];
            $qualids[] = $qualid;
            $quals['Property:P' . $qualid] = ["P$qualid", $row['valcount'], 0, []];
        }

        if (! empty($quals)) {
            $qualids = implode(',', $qualids);

            // Get the labels
            $prop_ids = array_keys($quals);
            $items = $wdwiki->getItemsWithCache($prop_ids);

            foreach ($items as $item) {
                $qualpid = $item->getId();
                $term_text = $item->getLabelDescription('label', $lang);

                if (! empty($term_text)) $quals['Property:' . $qualpid][0] = $term_text;
            }

            // Find > 50 unique values
            $sql = "SELECT qualpid ";
            $sql .= " FROM s51454__wikidata.subclassvalues ";
            $sql .= " WHERE qid = ? AND pid = ? AND value = '0' AND qualpid IN ($qualids)";

            $sth = $dbh_wiki->prepare($sql);
            $sth->bindValue(1, $numeric_id);
            $sth->bindValue(2, $numeric_pid);

            $sth->execute();

            while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
                $qualpid = $row['qualpid'];
                $quals['Property:P' . $qualpid][2] = -1;
            }

            // Get enum values
            $sql = "SELECT qualpid, value, valcount ";
            $sql .= " FROM s51454__wikidata.subclassvalues ";
            $sql .= " WHERE qid = ? AND pid = ? AND value NOT IN ('0','C') AND qualpid IN ($qualids)";

            $sth = $dbh_wiki->prepare($sql);
            $sth->bindValue(1, $numeric_id);
            $sth->bindValue(2, $numeric_pid);

            $sth->execute();
            $valids = [];

            while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
                $qualpid = $row['qualpid'];
                $value = $row['value'];
                $valcount = $row['valcount'];
                $quals['Property:P' . $qualpid][2] = 1;
                $quals['Property:P' . $qualpid][3]["Q$value"] = ["Q$value", $valcount];
                $valids["Q$value"] = true; // gets rid of duplicates
            }

            // Get enum value labels
            // Chunk it to keep from running out of memory
            $pageChunks = array_chunk($valids, 50, true);

            foreach ($pageChunks as $pageChunk) {
                $items = $wdwiki->getItemsWithCache(array_keys($pageChunk));

                foreach ($items as $item) {
                    $itemid = $item->getId();
                    $term_text = $item->getLabelDescription('label', $lang);

                    if (! empty($term_text)) {
                        foreach ($quals as &$qual) {
                            foreach ($qual[3] as $qualvalid => &$qualvalue) {
                                if ($qualvalid == $itemid) $qualvalue[0] = $term_text;
                            }
                            unset($qualvalue);
                        }
                        unset($qual);
                    }
                }
            }

            echo "<h2>Qualifiers</h2>\n";
            echo "Top 100 with 10 or more uses.\n";

            echo "<table class='wikitable tablesorter'><thead><tr><th>Qualifier</th><th>Usage<br />count</th><th>Instances<br />with qualifier</th>";
            echo "<th>Enumerated values (count) (SPARQL query)</th></tr></thead><tbody>\n";

            foreach ($quals as $propid => $qual) {
                $qualpid = substr($propid, 9);
                $wdurl = "https://www.wikidata.org/wiki/" . $propid;
                $term_text = htmlentities($qual[0], ENT_COMPAT, 'UTF-8');

                $sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
                    "  ?s p:P31 ?stmt .\n" .
                    "  ?stmt ps:P31 wd:$id .\n" .
                    "  ?s p:$pid ?qualstmt .\n" .
                    "  ?qualstmt pq:$qualpid ?qualval .\n" .
                    "  SERVICE wikibase:label { bd:serviceParam wikibase:language \"$lang\" }\n" .
                    "}\nORDER BY ?sLabel");

                $sparql = "<a href='$sparql' class='external'>SPARQL query</a>";

                echo "<tr><td><a class='external' href='$wdurl'>$term_text</a></td><td style='text-align:right' data-sort-value='{$qual[1]}'>" .
                intl_num_format($qual[1]) ."</td><td style='text-align:center'>$sparql</td><td>";

                if ($qual[2] == 0) echo "&nbsp;";
                elseif ($qual[2] == -1) echo "&gt; 50";
                else {
                    foreach ($qual[3] as $qualid => $qualval) {
                        $sparql = 'https://query.wikidata.org/#' . rawurlencode("SELECT DISTINCT ?s ?sLabel WHERE {\n" .
                            "  ?s p:P31 ?stmt .\n" .
                            "  ?stmt ps:P31 wd:$id .\n" .
                            "  ?s p:$pid ?qualstmt .\n" .
                            "  ?qualstmt pq:$qualpid wd:$qualid .\n" .
                            "  SERVICE wikibase:label { bd:serviceParam wikibase:language \"$lang\" }\n" .
                            "}\nORDER BY ?sLabel");

                        $sparql = "<a href='$sparql' class='external'>view</a>";

                        echo "{$qualval[0]} ({$qualval[1]}) ($sparql)<br />";
                    }
                }

                echo "</td></tr>";
            }

            echo "</tbody></table>\n";
        }

        ?>
       <br />
       <div>Data derived from database dump wikidatawiki-pages-articles.xml.</div>
       <div>Note: Names/descriptions are cached, so changes may not be seen until the next data load.</div>
       <div>Note: Numbers are formatted with the ISO recommended international thousands separator 'thin space'.</div>
       <div><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
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

	$return = [];
	$parents = [];
	$children = [];
	$class = [];
	$pop_props = [];

	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "tools.db.svc.eqiad.wmflabs";

	try {
	   $dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=s51454__wikidata;charset=utf8mb4", $user, $pass);
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
	$directinstcnt = 0;

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
		    $directinstcnt = $row['directinstcnt'];

			$class = ['Q' . $params['id'], '', $row['directchildcnt'], $row['indirectchildcnt'],
					$row['directinstcnt'], $row['indirectinstcnt'], $row['islistofcnt']];

			$items = $wdwiki->getItemsNoCache($class[0]);

			if (! empty($items)) {
				$term_text = $items[0]->getLabelDescription('label', $params['lang']);
				if (! empty($term_text)) $class[0] = $term_text;
				elseif ($items[0]->getRedirect()) $class[0] .= ' (redirect)';
				$class[1] = $items[0]->getLabelDescription('description', $params['lang']);
			}
		}		

	    // Retrieve the parent classes
		$sql = "SELECT parent_qid ";
		$sql .= " FROM s51454__wikidata.subclassclasses ";
		$sql .= " WHERE child_qid = ? ";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['id']);

		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$parents['Q' . $row['parent_qid']] = [$row['parent_qid'], 'Q' . $row['parent_qid']]; // removes dup terms
		}

		if (! empty($parents)) {
			$parent_ids = array_keys($parents);
			$items = $wdwiki->getItemsNoCache($parent_ids);

			foreach ($items as $item) {
				$qid = $item->getId();
				$term_text = $item->getLabelDescription('label', $params['lang']);

				if (! empty($term_text)) $parents[$qid][1] = $term_text;
				elseif ($item->getRedirect()) $parents[$qid][1] .= ' (redirect)';
			}
		}

		// Retrieve popular properties

		$sql = "SELECT pid, valcount ";
		$sql .= " FROM s51454__wikidata.subclassvalues ";
		$sql .= " WHERE qid = ? AND qualpid = 0 AND value = 'C' ORDER BY valcount DESC LIMIT 50";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['id']);

		$sth->execute();
		$pids = [];

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		    $pid = $row['pid'];
		    $pids[] = $pid;
		    $pop_props['Property:P' . $pid] = ['P' . $pid, $row['valcount'], floor($row['valcount'] / $directinstcnt * 100), 0, 0];
		}

		if (! empty($pop_props)) {
		    $pids = implode(',', $pids);

		    // Get the labels
		    $prop_ids = array_keys($pop_props);
		    $items = $wdwiki->getItemsWithCache($prop_ids);

		    foreach ($items as $item) {
		        $pid = $item->getId();
		        $term_text = $item->getLabelDescription('label', $params['lang']);

		        if (! empty($term_text)) $pop_props['Property:' . $pid][0] = $term_text;
		    }

		    // Find > 50 unique values
		    $sql = "SELECT pid ";
		    $sql .= " FROM s51454__wikidata.subclassvalues ";
		    $sql .= " WHERE qid = ? AND qualpid = 0 AND value = '0' AND pid IN ($pids)";

		    $sth = $dbh_wiki->prepare($sql);
		    $sth->bindValue(1, $params['id']);

		    $sth->execute();

		    while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		        $pid = $row['pid'];
		        $pop_props['Property:P' . $pid][3] = -1;
		    }

		    // Get the enumeration counts
		    $sql = "SELECT pid, count(*) AS enumcount ";
		    $sql .= " FROM s51454__wikidata.subclassvalues ";
		    $sql .= " WHERE qid = ? AND qualpid = 0 AND value NOT IN ('C','0')  AND pid IN ($pids) ";
		    $sql .= " GROUP BY pid";

		    $sth = $dbh_wiki->prepare($sql);
		    $sth->bindValue(1, $params['id']);

		    $sth->execute();

		    while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		        $pid = $row['pid'];
		        $pop_props['Property:P' . $pid][3] = $row['enumcount'];
		    }

		    // Get the qualifier counts
		    $sql = "SELECT pid, count(*) AS enumcount ";
		    $sql .= " FROM s51454__wikidata.subclassvalues ";
		    $sql .= " WHERE qid = ? AND qualpid <> 0 AND value = 'C'  AND pid IN ($pids) ";
		    $sql .= " GROUP BY pid";

		    $sth = $dbh_wiki->prepare($sql);
		    $sth->bindValue(1, $params['id']);

		    $sth->execute();

		    while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		        $pid = $row['pid'];
		        $pop_props['Property:P' . $pid][4] = $row['enumcount'];
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
			elseif ($item->getRedirect()) $children[$qid][1] .= ' (redirect)';
		}
	}
	
	$return = array('class' => $class, 'parents' => $parents, 'children' => $children, 'dataasof' => $dataasof,
	    'classcnt' => $classcnt, 'rootcnt' => $rootcnt, 'pop_props' => $pop_props
	);

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

	$params = [];

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
 * outputs JSONP
 *
 * @param string $lang
 * @param string $page
 * @param string $callback
 * @param string $userlang
 */
function perform_suggest($lang, $page, $callback, $userlang)
{
	global $instanceofIgnores;
	header('content-type: application/json; charset=utf-8');
	header('access-control-allow-origin: *');

	$lang = preg_replace('!\W!', '', $lang);
	if (! $userlang) $userlang = 'en';
	$userlang = preg_replace('!\W!', '', $userlang);

	// Retrieve the pages 3 smallest categories with min 5 pages and no year in cat name
	$mediawiki = new MediaWiki("https://$lang.wikipedia.org/w/api.php");

	$ret = $mediawiki->getProp('categoryinfo', [
	    'titles' => $page,
	    'generator' => 'categories',
	    'gclshow' => '!hidden',
	    'gcllimit' => '15'
	]);

	if (empty($ret['query']['pages'])) {
	    echo "/**/$callback({});";
	    return;
	}

	$cats = [];

	foreach ($ret['query']['pages'] as $qp) {
	    $colonpos = strpos($qp['title'], ':');
	    $title = substr($qp['title'], $colonpos + 1);
	    $page_cnt = $qp['categoryinfo']['pages'];
	    if ($page_cnt < 5) continue;
	    if (preg_match('!\d{4}!', $title)) continue;
	    $cats[$title] = ['qids' => [], 'instanceofs' => [], 'page_cnt' => $page_cnt];
	}

	uasort($cats, function ($a, $b) {
	    if ($a['page_cnt'] > $b['page_cnt']) return 1;
	    if ($a['page_cnt'] < $b['page_cnt']) return -1;
	    return 0;
	});

	$cats = array_slice($cats, 0, 3);

	// Retrieve the category member qids
	$qids = [];

	foreach ($cats as $cat => $dummy) {
	    $ret = $mediawiki->getProp('pageprops', [
	        'ppprop' => 'wikibase_item',
	        'generator' => 'categorymembers',
	        'gcmtitle' => "Category:$cat",
	        'gcmtype' => 'page',
	        'gcmsort' => 'sortkey',
	        'gcmlimit' => '10'
	    ]);

	    if (! empty($ret['query']['pages'])) {
	        foreach ($ret['query']['pages'] as $qp) {
	            $qid = $qp['pageprops']['wikibase_item'];
	            $qids[$qid] = true; // removes dups
	            $cats[$cat]['qids'][] = $qid;
	        }
	    }
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

	$instanceofs = [];

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

	if (! $qids) {
		echo "/**/$callback({});";
		return;
	}

	// Retrieve the child classes
	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "tools.db.svc.eqiad.wmflabs";

	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=s51454__wikidata;charset=utf8mb4", $user, $pass);
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