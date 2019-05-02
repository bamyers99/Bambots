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
use com_brucemyers\Util\CSVString;
use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\MediaWiki\WikidataSPARQL;
use com_brucemyers\Util\Logger;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = array();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

get_params();

switch ($action) {
	case 'getCSV':
		getCSV();
		exit;
}

$navels = get_navels();

display_form($navels);

/**
 * Display replag
 *
 */
function display_form($navels)
{
	global $params;
	$edit_types = array(
		-1 => 'Label additions',
		-2 => 'Description additions',
		-3 => 'Alias additions',
		-4 => 'Site link additions',
		-5 => 'Merges',
	    -6 => 'Lexeme: form additions',
	    -7 => 'Lexeme: representation additions',
	    -8 => 'Lexeme: grammatical feature additions',
	    -9 => 'Lexeme: sense additions',
	    -10 => 'Lexeme: gloss additions',
	    -11 => 'Reference additions',
	    -12 => 'Qualifier additions',
	    -13 => 'Label changes',
	    -14 => 'Description changes',
	    -15 => 'Claim deletions',
	    -16 => 'Claim changes',
	    -17 => 'Undos',
	    -18 => 'Item/Property creations',
	    -19 => 'Item/Property changes',
	    -20 => 'Site link deletions',
	    -21 => 'Description deletions',
	    -22 => 'Alias deletions',
	    -23 => 'Restores',
	    -24 => 'Label deletions',
	    -25 => 'Site link changes',
	    -26 => 'Reference deletions',
	    -27 => 'Alias changes',
	    -28 => 'Reference changes',
	    -29 => 'Qualifier changes',
	    -30 => 'Lexeme: representation / grammatical feature changes',
	    -31 => 'Lexeme: form deletions',
	    -32 => 'Lexeme: representation changes',
	    -33 => 'Lexeme: representation deletions',
	    -34 => 'Lexeme: grammatical feature deletions',
	    -35 => 'Lexeme: sense deletions',
	    -36 => 'Lexeme: gloss changes',
	    -37 => 'Lexeme: gloss deletions',
	    -38 => 'Qualifier deletions',
	    -39 => 'Lexeme: creations',
	    -40 => 'Lexeme: merges'
	);

	asort($edit_types);

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Wikidata Navel Gazer</title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
	    <script type='text/javascript' src='js/jquery-2.1.1.min.js'></script>
		<script type='text/javascript' src='js/jquery.tablesorter.min.js'></script>
	</head>
	<body>
		<script type='text/javascript'>
			$(document).ready(function()
			    {
				<?php if (! empty($params['username'])) { ?>
		        	$('.tablesorter').tablesorter({sortList: [[0,0]]});
			    <?php } elseif (! empty($params['property'])) { ?>
		        	$('.tablesorter').tablesorter({sortList: [[1,1]]});
				<?php } elseif (! empty($params['langadd'])) { ?>
		        	$('.tablesorter').tablesorter({sortList: [[2,1]], headers: {3: {sorter: false}}});
		        <?php } ?>
			    }
			);
		</script>
		<div style="display: table; margin: 0 auto;">
		<h2>Wikidata Navel Gazer<sup>[1]</sup><?php
		if (! empty($params['username'])) echo ' - ' . $params['username'];
		elseif (! empty($params['property'])) {
			if ($params['property'] < 0) {
				if (isset($edit_types[$params['property']])) echo ' - ' . $edit_types[$params['property']];
			} else {
				echo ' - P' . $params['property'];
			}
		}
		?></h2>
		<h3>users edit action counts</h3>
        <form action="NavelGazer.php" method="post">
        <table class="form">
        <tr><td><b>Username</b></td><td><input id="username" name="username" type="text" size="10" value="<?php echo $params['username'] ?>" /></td></tr>
        <tr><td colspan='2'>or</td></tr>
        <tr><td><b>Property</b></td><td><input id="property" name="property" type="text" size="10" value="<?php if (! empty($params['property'])) echo 'P' . $params['property'] ?>" /> example: P31</td></tr>
        <tr><td>Pseudo properties</td><td><select name='pseudoprop' onchange='$("#property").val($(this).val())'><?php
            echo "<option value=''>&nbsp;</option>";
         	foreach ($edit_types as $key => $edit_type) {
        		echo "<option value='P$key'>$edit_type (P$key)</option>";
        	}
        ?></select></td></tr>
        <tr><td colspan='2'>or</td></tr>
        <tr><td><b>Language code</b></td><td><input id="langadd" name="langadd" type="text" size="10" value="<?php echo $params['langadd'] ?>" /> (label, description, alias, sitelink additions)</td></tr>
        <tr><td colspan='2'><hr /></td></tr>
        <tr><td><b>Property label language code</b></td><td><input id="lang" name="lang" type="text" size="4" value="<?php echo $params['lang'] ?>" /></td></tr>
        <tr><td colspan='2'><input type="submit" value="Submit" /></td></tr>
        </table>
        </form>
        <script type="text/javascript">
            if (document.getElementById) {
                var e = document.getElementById('username');
                e.focus();
                e.select();
            }
        </script>
        <br />
<?php
	if (! empty($navels)) {
		if (empty($navels['data'])) {
		    if (! empty($params['username'])) echo 'User does not have any property additions';
		    elseif (! empty($params['property'])) echo 'Property not found';
		    else echo 'Language not found';

		} elseif (! empty($params['username'])) {
			if (! empty($params['property'])) echo "Clear the Username field to do a property search<br />\n";
			echo $navels['dataasof'] . "<sup>[2]</sup><br /><br />\n";

			$misc = [];

			foreach ($navels['data'] as $key => $row) {
			    if ($row[0] < 0) {
			        $misc[] = $row;
			        unset($navels['data'][$key]);
			    }
			}

			echo "<h3>Property additions</h3>";
			echo "<table class='wikitable tablesorter'><thead><tr><th>Property</th><th>Datatype</th><th>Total count</th><th>Last month</th></tr></thead><tbody>\n";

			usort($navels['data'], function($a, $b) {
				return strcmp(strtolower($a[3]), strtolower($b[3]));
			});

			$propaddcnttot = 0;
			$propaddcntmth = 0;

			foreach ($navels['data'] as $row) {
			    $propaddcnttot += $row[1];
			    $propaddcntmth += $row[2];
				$url = "/NavelGazer.php?property=P" . $row[0];
				$term_text = htmlentities($row[3], ENT_COMPAT, 'UTF-8');
				echo "<tr><td><a href='$url'>$term_text (P{$row[0]})</a></td><td>{$row[4]}</td><td style='text-align:right' data-sort-value='$row[1]'>" . intl_num_format($row[1]) .
					"</td><td style='text-align:right' data-sort-value='$row[2]'>" . intl_num_format($row[2]) . "</td></tr>\n";
			}

			echo "</tbody>\n";
			echo "<tfoot><tr><td>Total</td><td>&nbsp;</td><td style='text-align:right'>" . intl_num_format($propaddcnttot) .
			     "</td><td style='text-align:right'>" . intl_num_format($propaddcntmth) . "</td></tr></tfoot>\n";
			echo "</table>\n";

			if (! empty($misc)) {
			    echo "<h3>Other actions</h3>";
			    echo "<table class='wikitable tablesorter'><thead><tr><th>Action</th><th>Total count</th><th>Last month</th></tr></thead><tbody>\n";

			    foreach ($misc as $row) {
			        $url = "/NavelGazer.php?property=P" . $row[0];
			        $term_text = $edit_types[$row[0]];
			        echo "<tr><td><a href='$url'>$term_text (P{$row[0]})</a></td><td style='text-align:right' data-sort-value='$row[1]'>" . intl_num_format($row[1]) .
			        "</td><td style='text-align:right' data-sort-value='$row[2]'>" . intl_num_format($row[2]) . "</td></tr>\n";
			    }

			    echo "</tbody></table>\n";
			}

			if (! empty($navels['langdata'])) {
			    echo '<h3>Label, description, alias, sitelink additions</h3>';
			    echo "<table class='wikitable tablesorter'><thead><tr><th>Language</th><th>Total count</th><th>Last month</th></tr></thead><tbody>\n";

			    foreach ($navels['langdata'] as $row) {
			        echo "<tr><td>{$row[0]}</td><td style='text-align:right' data-sort-value='$row[1]'>" . intl_num_format($row[1]) .
			        "</td><td style='text-align:right' data-sort-value='$row[2]'>" . intl_num_format($row[2]) . "</td></tr>\n";
			    }

			    echo "</tbody></table>\n";
			}

		} elseif (! empty($params['property'])) {
		    echo $navels['dataasof'] . "<sup>[2]</sup><br />\n";
		    if ($params['property'] < 0) {
		        if (isset($edit_types[$params['property']])) echo 'Action: ' . $edit_types[$params['property']] . "<br />\n";
		    } else {
		        $url = "https://www.wikidata.org/wiki/Property:P" . $params['property'];
		        $term_text = htmlentities($navels['property_label'], ENT_COMPAT, 'UTF-8');
		        echo "Property: <a href='$url' class='external'>$term_text (P{$params['property']})</a><br />\n";
		    }
		    echo "Total count: " . intl_num_format($navels['createtotal']) . " Last month: " . intl_num_format($navels['monthtotal']) . "<br />\n";
		    if (count($navels['data']) == 100) echo "Top 100<br />\n";

		    echo "<table class='wikitable tablesorter'><thead><tr><th>Username</th><th>Total count</th><th>Last month</th></tr></thead><tbody>\n";

		    foreach ($navels['data'] as $row) {
		        if (empty($row[0])) {
		            $col1 = 'anonymous';
		        } else {
		            $user_encoded = htmlentities($row[0], ENT_COMPAT, 'UTF-8');
		            $url = "/NavelGazer.php?username=" . urlencode($row[0]);
		            $col1 = "<a href='$url'>$user_encoded</a>";
		        }
		        echo "<tr><td>$col1</td><td style='text-align:right' data-sort-value='$row[1]'>" . intl_num_format($row[1]) .
		        "</td><td style='text-align:right' data-sort-value='$row[2]'>" . intl_num_format($row[2]) . "</td></tr>\n";
		    }

		    echo "</tbody></table>\n";

		    $host  = $_SERVER['HTTP_HOST'];
		    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		    $extra = "NavelGazer.php?action=getCSV&property=P" . urlencode($params['property']);

		    echo "<a href='//$host$uri/$extra'>Download all users in CSV format</a>\n<br />";

		} elseif (! empty($params['langadd'])) {
		    echo $navels['dataasof'] . "<sup>[2]</sup><br />\n";
    	    echo "<b>Label, description, alias, sitelink additions for '{$params['langadd']}'";
    	    if (! empty($navels['property_label'])) echo ' (' . $navels['property_label'] . ')';
            echo "</b><br />\n";
    	    if (count($navels['data']) == 100) echo "Top 100<br />\n";

    	    echo "<table class='wikitable tablesorter'><thead><tr><th>Username</th><th>Total count (rank)</th><th>Last month (rank)</th><th>Property additions</th></tr></thead><tbody>\n";

    	    foreach ($navels['data'] as $row) {
    	        if (empty($row[0])) {
    	            $col1 = 'anonymous';
    	            $col4 = '&nbsp';
    	        } else {
    	            $user_encoded = htmlentities($row[0], ENT_COMPAT, 'UTF-8');
    	            $url = "https://www.wikidata.org/wiki/User:" . str_replace(' ', '_', $row[0]);
    	            $col1 = "<a href='$url' class='external'>$user_encoded</a>";
    	            $userurl = "/NavelGazer.php?username=" . urlencode($row[0]);
    	            $col4 = "<a href='$userurl'>view</a>";
    	        }

    	        echo "<tr><td>$col1</td><td style='text-align:right' data-sort-value='$row[1]'>" . intl_num_format($row[1]) . '<span style="font-family: monospace;">';
    	        if ($row[2] != 0) {
    	            $blankspace = '';
    	            if ($row[2] < 10) $blankspace = '&numsp;';
    	            echo " $blankspace($row[2])";
    	        } else echo ' &numsp;&numsp;&numsp;&numsp;';

    	        echo "</span></td><td style='text-align:right' data-sort-value='$row[3]'>" . intl_num_format($row[3]) . '<span style="font-family: monospace;">';
    	        if ($row[4] != 0) {
    	            $blankspace = '';
    	            if ($row[4] < 10) $blankspace = '&numsp;';
    	            echo " $blankspace($row[4])";
    	        } else echo ' &numsp;&numsp;&numsp;&numsp;';

    	        echo "</span></td><td style='text-align:center'>$col4</td></tr>\n";
    	    }

    	    echo "</tbody></table>\n";
    	    echo "Note: Includes top 50 total count and top 50 last month count<br />\n";
    	}
    }
?>
        <br /><div><sup>1</sup><a href='http://www.merriam-webster.com/dictionary/navel-gazing'>navel–gazing</a> (Merriam-Webster)</div>
        <div><sup>2</sup>Data derived from database dump wikidatawiki-stub-meta-history.xml revision comments</div>
        <div><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
}

function get_navels()
{
	global $params;
	$return = [];
	$data = [];
	$langdata = [];
	$property_label = '';
	$wdwiki = new WikidataWiki();
	$createtotal = 0;
	$monthtotal = 0;

	if (empty($params['username']) && empty($params['property']) && empty($params['langadd'])) return $return;

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

	$sth = $dbh_wiki->query("SELECT user_name FROM s51454__wikidata.navelgazer WHERE user_name LIKE 'Data as of:%'");

	$row = $sth->fetch(PDO::FETCH_NUM);
	$dataasof = $row[0];

	if (! empty($params['username'])) {
		if (stripos($params['username'], 'User:') === 0) $params['username'] = substr($params['username'], 5);
		if (empty($params['username'])) return $return;
		$params['username'] = ucfirst($params['username']);
		$params['username'] = str_replace('_', ' ', $params['username']);

		$sql = "SELECT property_id, create_count, month_count " .
				" FROM s51454__wikidata.navelgazer " .
				" WHERE user_name = ? ";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['username']);

		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
			$data[$row['property_id']] = [$row['property_id'], $row['create_count'], $row['month_count'], '', '']; // removes dups
		}

		if (! empty($data)) {
			$prop_ids = array();
			foreach (array_keys($data) as $key) {
				if ($key > 0) $prop_ids[] = 'Property:P' . $key;
			}

			$items = $wdwiki->getItemsNoCache($prop_ids);

			foreach ($items as $item) {
				$pid = $item->getId();
				$pid = substr($pid, 1);
				$property_label = $item->getLabelDescription('label', $params['lang']);

				if (! empty($property_label)) $data[$pid][3] = $property_label;
				$data[$pid][4] = $item->getDatatype();
			}
		}

		// load language stats
		$sql = "SELECT `language`, create_count, month_count " .
		  		" FROM s51454__wikidata.navelgazerlang " .
		  		" WHERE user_name = ? ORDER BY `language`";

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, $params['username']);

		$sth->execute();

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		    $langdata[$row['language']] = [$row['language'], $row['create_count'], $row['month_count']]; // removes dups
		}

	} elseif (! empty($params['property'])) {
		$items = $wdwiki->getItemsNoCache('Property:P' . $params['property']);

		if (! empty($items)) {
			$property_label = $items[0]->getLabelDescription('label', $params['lang']);
		}

		$sql = 'SELECT user_name, create_count, month_count ' .
				' FROM s51454__wikidata.navelgazer ' .
				' WHERE property_id = ? ORDER by create_count DESC';

		$sth = $dbh_wiki->prepare($sql);
		$sth->bindValue(1, (int)$params['property']);

		$sth->execute();
		$count = 0;

		while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		    if ($count < 100) {
			    $data[] = [$row['user_name'], $row['create_count'], $row['month_count']];
		    }
		    $createtotal += $row['create_count'];
		    $monthtotal += $row['month_count'];
		    ++$count;
		}

	} elseif (! empty($params['langadd'])) {
	    $sparql = <<<EOT
SELECT ?item ?c
{
    ?item wdt:P424 "{$params['langadd']}" .
    ?item wdt:P31/wdt:P279* wd:Q34770 .
}
EOT;

	    $wdsparql = new WikidataSPARQL();
	    $result = $wdsparql->query(rawurlencode($sparql));

	    $property_label = '';

	    if (! empty($result)) {
	        $uri = $result[0]['item']['value'];
	        preg_match('!entity/(.+)!', $uri, $matches);
	        $qid = $matches[1];
	        $wdwiki = new WikidataWiki();
	        $items = $wdwiki->getItemsNoCache($qid);

	        if (! empty($items)) {
	            $property_label = $items[0]->getLabelDescription('label', $params['lang']);
	        }

	    }

	    $sql = '(SELECT user_name, create_count, month_count ' .
	   	    ' FROM s51454__wikidata.navelgazerlang ' .
	   	    ' WHERE `language` = ? ORDER BY create_count DESC LIMIT 50) ' .
	   	    ' UNION ' .
	   	    ' (SELECT user_name, create_count, month_count ' .
	   	   	' FROM s51454__wikidata.navelgazerlang ' .
	   	   	' WHERE `language` = ? ORDER BY month_count DESC LIMIT 50) ' .
	   	   	' ORDER BY create_count DESC';

	    $sth = $dbh_wiki->prepare($sql);
	    $sth->bindValue(1, $params['langadd']);
	    $sth->bindValue(2, $params['langadd']);

	    $sth->execute();

	    while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
	        $data[$row['user_name']] = [$row['user_name'], $row['create_count'], 0, $row['month_count'], 0]; // removes dups
	    }

	    $rank = 1;
	    $prevcnt = -1;
	    $prevrank = 0;

	    foreach ($data as &$row) {
	        if ($row[1] == $prevcnt) {
	            $row[2] = $prevrank;
	        } else {
	            $row[2] = $rank;
	            $prevrank = $rank;
	        }

	        if ($rank == 50) break;
	        ++$rank;
	        $prevcnt = $row[1];
	    }
	    unset($row);

	    usort($data, function ($a, $b) { //desc
	        if ($a[3] < $b[3]) return 1;
	        if ($a[3] > $b[3]) return -1;
	        return 0;
	    });

        $rank = 1;
        $prevcnt = -1;
        $prevrank = 0;

        foreach ($data as &$row) {
            if ($row[3] == $prevcnt) {
                $row[4] = $prevrank;
            } else {
                $row[4] = $rank;
                $prevrank = $rank;
            }

            if ($rank == 50) break;
            ++$rank;
            $prevcnt = $row[3];
        }
        unset($row);
	}

	$return = ['data' => $data, 'dataasof' => $dataasof, 'property_label' => $property_label, 'langdata' => $langdata,
	    'createtotal' => $createtotal, 'monthtotal' => $monthtotal
	];

	return $return;
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = array();

	$params['username'] = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
	$params['property'] = isset($_REQUEST['property']) ? $_REQUEST['property'] : '';
	if (! empty($params['property']) && $params['property'][0] == 'P') $params['property'] = substr($params['property'], 1);
	$params['langadd'] = isset($_REQUEST['langadd']) ? $_REQUEST['langadd'] : '';
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
 * Return CSV of property user counts
 */
function getCSV()
{
	global $params;
	$wikiname = 'tools';
	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);
	$wiki_host = Config::get('CleanupWorklistBot.wiki_host'); // Used for testing
	if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";

	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=s51454__wikidata;charset=utf8", $user, $pass);
	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sth = $dbh_wiki->query("SELECT user_name FROM s51454__wikidata.navelgazer WHERE user_name LIKE 'Data as of:%'");

	$row = $sth->fetch(PDO::FETCH_NUM);
	$dataasof = substr($row[0], 12);

	$filename = "NavelGazer_P{$params['property']}_$dataasof.csv";

	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="'.$filename.'";');

	echo CSVString::format(array('Username', 'Total count', 'Last month'));
	echo "\n";

	$sql = "SELECT user_name, create_count, month_count " .
			" FROM s51454__wikidata.navelgazer " .
			" WHERE property_id = ? ORDER by create_count DESC";

	$sth = $dbh_wiki->prepare($sql);
	$sth->bindValue(1, (int)$params['property']);

	$sth->execute();

	while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		echo CSVString::format(array($row['user_name'], $row['create_count'], $row['month_count']));
		echo "\n";
	}
}

?>