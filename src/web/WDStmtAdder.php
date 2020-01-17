<?php
/**
 Copyright 2019 Myers Enterprises II

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
use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\MediaWiki\WikidataItem;
use com_brucemyers\MediaWiki\WikidataSPARQL;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\UUID;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

define('PROP_MAX_LONGEVITY', 'P4214');
define('PROP_MASS', 'P2067');
define('PROP_GESTATION', 'P3063');
define('PROP_LITTER_SIZE', 'P7725');
define('PROP_INCUBATION', 'P7770');
define('PROP_SEX', 'P21');
define('PROP_OF', 'P642');
define('PROP_STATED_IN', 'P248');
define('VALUE_ANIMAL_MALE', 'Q44148');
define('VALUE_ANIMAL_FEMALE', 'Q43445');
define('VALUE_STAGE_BIRTH', 'Q4128476');
define('VALUE_STAGE_ADULT', 'Q78101716');

$params = [];

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

get_params();

switch ($action) {
	case 'display_item':
		display_item();
		exit;

	case 'add_stmt':
	    add_stmt();
	    exit;
}

$list = get_list();

display_form($list);

/**
 * Display form
 *
 */
function display_form($list)
{
	global $params;

	$offset = Config::get('startpos');

	display_header();

	echo '<table class="wikitable"><thead><tr><th>Species</th><th>Gestation</th><th>Litter<br />Size</th><th>Birth<br />Weight</th><th>Adult<br />Weight</th><th>Lonevity</th><th>View</th></tr></thead><tbody>';

    foreach ($list as $row) {
        echo '<tr>';
        echo '<td>' . htmlentities($row['genus_species'], ENT_COMPAT, 'UTF-8') . '</td>';
//        echo '<td>' . htmlentities($row['common_name'], ENT_COMPAT, 'UTF-8') . '</td>';
        echo "<td style='text-align:right'>" . ($row['gestation'] ? $row['gestation'] : '&nbsp;') . "</td>";
        echo "<td style='text-align:right'>" . ($row['litter_size'] ? $row['litter_size'] : '&nbsp;') . "</td>";
        echo "<td style='text-align:right'>" . ($row['birth_weight'] ? $row['birth_weight'] : '&nbsp;') . "</td>";
        echo "<td style='text-align:right'>" . ($row['adult_weight'] ? $row['adult_weight'] : '&nbsp;') . "</td>";
        echo "<td style='text-align:right'>" . ($row['max_longevity'] ? $row['max_longevity'] : '&nbsp;') . "</td>";
        echo '<td><a href="/WDStmtAdder.php?action=display_item&item=' . urlencode($row['genus_species']) . '&offset=' . $offset . '">View</a></td>';
        echo '</tr>';

        ++$offset;
    }

    echo '</tbody></table>';

    echo '<a href="/WDStmtAdder.php">Next</a>';

    display_footer();
}

function display_item()
{
    display_header();

    ?>
<script type="text/javascript">
function add_stmt(btn, qid, fieldname, fieldvalue, unit) {
	$(btn).attr("disabled", true);
	$(btn).val('Adding');

	$.ajax({
		  url: "/WDStmtAdder.php?action=add_stmt&qid=" + qid + "&fieldname=" + fieldname + "&value=" + fieldvalue + "&unit=" + unit,
		  context: btn
		}).done(function( data ) {
			if (data == '') {
				$(this).val('Added');
			} else {
				alert(data);
			}
		}).fail(function( jqXHR, textStatus ) {
			alert(textStatus);
	});
}
</script>
<?php
    $itemname = isset($_REQUEST['item']) ? $_REQUEST['item'] : '';
    $itemoffset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : '0';
    Config::set('startpos', $itemoffset + 1, true);

    //
    // Get source database data
    //

    $db_fields = [
        'GE' => ['fieldname' => 'gestation', 'unit' => 'day'],
        'LS' => ['fieldname' => 'litter_size', 'unit' => '1'],
        'BW' => ['fieldname' => 'birth_weight', 'unit' => 'gram'],
        'AW' => ['fieldname' => 'adult_weight', 'unit' => 'gram'],
        'ML' => ['fieldname' => 'max_longevity', 'unit' => 'year'],
        'MW' => ['fieldname' => 'male_weight', 'unit' => 'gram'],
        'FW' => ['fieldname' => 'female_weight', 'unit' => 'gram']
        ];

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

    $sth = $dbh_wiki->prepare('SELECT * FROM s51454__wikidata.hbw WHERE genus_species = ?');
    $sth->bindValue(1, $itemname);

    $sth->execute();

    $sourceitem = $sth->fetch(PDO::FETCH_NAMED);

    $source_attribs = [];

    foreach ($db_fields as $abbrev => $config) {
        $fieldname = $config['fieldname'];
        if ($sourceitem[$fieldname] != '0') $source_attribs[$abbrev] = $sourceitem[$fieldname];
    }

    //
    // Get Wikidata
    //

    $sparql = <<<EOT
SELECT ?item
{
	?item wdt:P225 "$itemname" .
}
LIMIT 2
EOT;

    $wdsparql = new WikidataSPARQL();
    $result = $wdsparql->query(rawurlencode($sparql));

    if (count($result) == 1) {
        $uri = $result[0]['item']['value'];
        preg_match('!entity/(.+)!', $uri, $matches);
        $qid = $matches[1];
        $wdwiki = new WikidataWiki();
        $items = $wdwiki->getItemsNoCache($qid);

        if (! empty($items)) {
            $wditem = $items[0];
        }
    } else {
        echo "<h3>Wikidata item (" . count($result) . ") not found or ambiguous for $itemname</h3>";
        display_footer();
        return;
    }

    $wdalternatechecked = '';

    loadWikidata($wditem, $wd_attribs, $wdlabel, $wddescription, $wdalias, $wdsitelinkcnt);

    if ($wdsitelinkcnt < 2) { // Check taxon synonym
        $sparql = <<<EOT
SELECT ?item
{
	?item wdt:P1420 wd:$qid .
}
LIMIT 2
EOT;

        $result = $wdsparql->query(rawurlencode($sparql));

        if (count($result) == 1) {
            $uri = $result[0]['item']['value'];
            preg_match('!entity/(.+)!', $uri, $matches);
            $qid = $matches[1];
            $items = $wdwiki->getItemsNoCache($qid);

            if (! empty($items)) {
                $wditem = $items[0];
                loadWikidata($wditem, $wd_attribs, $wdlabel, $wddescription, $wdalias, $wdsitelinkcnt);
                $wdalternatechecked = ' <span style="color:green">taxon synonym</span>';
            }
        }
    }

    if ($wdsitelinkcnt < 2) { // Check original combination
        $sparql = <<<EOT
SELECT ?item
{
	?item wdt:P1403 wd:$qid .
}
LIMIT 2
EOT;

        $result = $wdsparql->query(rawurlencode($sparql));

        if (count($result) == 1) {
            $uri = $result[0]['item']['value'];
            preg_match('!entity/(.+)!', $uri, $matches);
            $qid = $matches[1];
            $items = $wdwiki->getItemsNoCache($qid);

            if (! empty($items)) {
                $wditem = $items[0];
                loadWikidata($wditem, $wd_attribs, $wdlabel, $wddescription, $wdalias, $wdsitelinkcnt);
                $wdalternatechecked = ' <span style="color:green">original combination</span>';
            }
        }
    }

    if ($wdsitelinkcnt < 2) { // Check basionym
        $sparql = <<<EOT
SELECT ?item
{
	?item wdt:P566 wd:$qid .
}
LIMIT 2
EOT;

        $result = $wdsparql->query(rawurlencode($sparql));

        if (count($result) == 1) {
            $uri = $result[0]['item']['value'];
            preg_match('!entity/(.+)!', $uri, $matches);
            $qid = $matches[1];
            $items = $wdwiki->getItemsNoCache($qid);

            if (! empty($items)) {
                $wditem = $items[0];
                loadWikidata($wditem, $wd_attribs, $wdlabel, $wddescription, $wdalias, $wdsitelinkcnt);
                $wdalternatechecked = ' <span style="color:green">basionym</span>';
            }
        }
    }

    if ($wdsitelinkcnt < 2) { // Check subject has role protonym
        $sparql = <<<EOT
SELECT ?item
{
	?item p:P2868 ?subject .
    ?subject ps:P2868 wd:Q14192851 .
    ?subject pq:P642 wd:$qid .
}
LIMIT 2
EOT;

        $result = $wdsparql->query(rawurlencode($sparql));

        if (count($result) == 1) {
            $uri = $result[0]['item']['value'];
            preg_match('!entity/(.+)!', $uri, $matches);
            $qid = $matches[1];
            $items = $wdwiki->getItemsNoCache($qid);

            if (! empty($items)) {
                $wditem = $items[0];
                loadWikidata($wditem, $wd_attribs, $wdlabel, $wddescription, $wdalias, $wdsitelinkcnt);
                $wdalternatechecked = ' <span style="color:green">subject has role protonym</span>';
            }
        }
    }

    //
    // Get Global Species
    //

    $sth = $dbh_wiki->prepare('SELECT * FROM s51454__wikidata.globalspecies, s51454__wikidata.databases1 WHERE name = ? AND dbid = record_id');
    $sth->bindValue(1, $itemname);

    $sth->execute();

    $gs_attribs = [];

    while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
        $gs_attribs[$row['attribute_name']] = $row['attribute_value'];
    }

    $fields = [
        'BW' => 'Birth weight',
        'AW' => 'Adult weight',
        'MW' => 'Male weight',
        'FW' => 'Female weight',
        'GE' => 'Gestation',
        'LS' => 'Litter size',
        'ML' => 'Max longevity'
    ];

    echo "<h3>$itemname ($wdlabel) $wddescription ($wdalias)$wdalternatechecked</h3>";
    if ($wdsitelinkcnt < 5) {
        echo "<h3 style='color:red'>Site link count = $wdsitelinkcnt</h3>";
    }

    echo '<table class="wikitable"><thead><tr><th>Attribute</th><th>Global species</th><th>Wikidata</th><th>Source<br />DB</th><th>Action</th></tr></thead><tbody>';

    foreach ($fields as $fieldname => $description) {
        echo "<tr><td>$description</td><td style='text-align:right'>";

        if (isset($gs_attribs[$fieldname])) echo $gs_attribs[$fieldname]; else echo '&nbsp;';

        echo "</td><td style='text-align:right'>";

        if (isset($wd_attribs[$fieldname])) echo substr($wd_attribs[$fieldname], 1); else echo '&nbsp;';

        echo "</td><td style='text-align:right'>";

        if (isset($source_attribs[$fieldname])) echo $source_attribs[$fieldname]; else echo '&nbsp;';

        echo "</td><td style='text-align:center'>";

        if (isset($source_attribs[$fieldname]) && ! isset($wd_attribs[$fieldname]) && $wdsitelinkcnt > 2 &&
            ($fieldname != 'AW' || (! isset($gs_attribs['MW']) && ! isset($gs_attribs['FW']) && ! isset($wd_attribs['MW']) && ! isset($wd_attribs['FW'])))) {
            echo '<form>';
            echo "<input type='button' value='Add' id='addbtn' onclick='add_stmt(this, \"$qid\", \"$fieldname\", \"" . urlencode($source_attribs[$fieldname]) .
                "\", \"{$db_fields[$fieldname]['unit']}\"); return false;' />";

            if ($fieldname == 'GE') {
                echo "<input type='button' value='Incubation' id='addbtn' onclick='add_stmt(this, \"$qid\", \"IN\", \"" . urlencode($source_attribs[$fieldname]) .
                "\", \"{$db_fields[$fieldname]['unit']}\"); return false;' />";
            }
            echo '</form>';
        } else {
            echo '&nbsp;';
        }

        echo '</td></tr>';
    }

    $encoded_name = urlencode($itemname);

    echo "</tbody><tfoot><tr><td>&nbsp;</td><td style='text-align:center'><a href='http://localhost:92/taxas/search/$encoded_name'>View</a></td><td style='text-align:center'><a href='https://www.wikidata.org/wiki/$qid'>View</a></td><td>&nbsp;</td><td>&nbsp;</td></tr>";

    echo '</table>';

    display_footer();
}

function loadWikidata($wditem, &$wd_attribs, &$wdlabel, &$wddescription, &$wdalias, &$wdsitelinkcnt)
{
    $wd_attribs = [];

    $stmts = $wditem->getStatementsOfType(PROP_MAX_LONGEVITY);
    if (count($stmts)) {
        $wd_attribs['ML'] = $stmts[0];
    }

    $stmts = $wditem->getStatementsOfType(PROP_GESTATION);
    if (count($stmts)) {
        $wd_attribs['GE'] = $stmts[0];
    }

    $stmts = $wditem->getStatementsOfType(PROP_INCUBATION);
    if (count($stmts)) {
        $wd_attribs['GE'] = $stmts[0]; // uses same prop as gestation
    }

    $stmts = $wditem->getStatementsOfType(PROP_LITTER_SIZE);
    if (count($stmts)) {
        $wd_attribs['LS'] = $stmts[0];
    }

    $stmts = $wditem->getStatementsOfType(PROP_MASS);
    if (count($stmts)) {
        $x = 0;

        foreach ($stmts as $stmt) {
            $qualifiers = $wditem->getStatementQualifiers(PROP_MASS, $x);

            $sex = null;
            $stage = null;

            foreach ($qualifiers as $prop => $qualifier) {
                if ($prop == PROP_SEX) {
                    if ($qualifier[0] == VALUE_ANIMAL_MALE) $sex = VALUE_ANIMAL_MALE;
                    elseif ($qualifier[0] == VALUE_ANIMAL_FEMALE) $sex = VALUE_ANIMAL_FEMALE;

                } elseif ($prop == PROP_OF) {
                    if ($qualifier[0] == VALUE_STAGE_BIRTH) $stage = VALUE_STAGE_BIRTH;
                    elseif ($qualifier[0] == VALUE_STAGE_ADULT) $stage = VALUE_STAGE_ADULT;
                }
            }

            if (! empty($stage)) {
                if ($stage == VALUE_STAGE_BIRTH) {
                    $wd_attribs['BW'] = $stmt;

                } else { // VALUE_STAGE_ADULT
                    if ($sex == VALUE_ANIMAL_MALE) $wd_attribs['MW'] = $stmt;
                    elseif ($sex == VALUE_ANIMAL_FEMALE) $wd_attribs['FW'] = $stmt;
                    else $wd_attribs['AW'] = $stmt;
                }
            }

            ++$x;
        }
    }

    $wdlabel = $wditem->getLabelDescription('label', 'en');
    $wddescription = $wditem->getLabelDescription('description', 'en');
    $wdaliases = $wditem->getAliases('en');
    if (count($wdaliases) > 0) $wdalias = $wdaliases[0]['value'];
    else $wdalias = '<span style="color:red">No alias</span>';

    $wdsitelinks = $wditem->getSiteLinks();
    $wdsitelinkcnt = 0;
    foreach ($wdsitelinks as $wdsite) {
        if (! preg_match('!^[a-z]{2,3}wiki$!', $wdsite['site'])) continue;
        ++$wdsitelinkcnt;
    }
}

function get_list()
{
	global $params;

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

	$startpos = Config::get('startpos');

	$sth = $dbh_wiki->query("SELECT * FROM s51454__wikidata.hbw ORDER BY genus_species LIMIT $startpos,100");

	$data = [];

	while ($row = $sth->fetch(PDO::FETCH_NAMED)) {
		$data[] = $row;
	}

	return $data;
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = [];
}

function display_header()
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Wikidata Statement Adder</title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
	    <script type='text/javascript' src='js/jquery-2.1.1.min.js'></script>
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2>Wikidata Statement Adder</h2>
	<?php
}

function display_footer()
{
    ?>
        <div><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html>
    <?php
}

function add_stmt()
{
    $fields = [
        'BW' => ['valueprop' => PROP_MASS, 'qualifiers' =>[PROP_OF => VALUE_STAGE_BIRTH]],
        'AW' => ['valueprop' => PROP_MASS, 'qualifiers' =>[PROP_OF => VALUE_STAGE_ADULT]],
        'MW' => ['valueprop' => PROP_MASS, 'qualifiers' =>[PROP_OF => VALUE_STAGE_ADULT, PROP_SEX => VALUE_ANIMAL_MALE]],
        'FW' => ['valueprop' => PROP_MASS, 'qualifiers' =>[PROP_OF => VALUE_STAGE_ADULT, PROP_SEX => VALUE_ANIMAL_FEMALE]],
        'GE' => ['valueprop' => PROP_GESTATION],
        'LS' => ['valueprop' => PROP_LITTER_SIZE],
        'ML' => ['valueprop' => PROP_MAX_LONGEVITY],
        'IN' => ['valueprop' => PROP_INCUBATION]
    ];

    $dbqid = Config::get('dbid');

    $unitsids = array_flip(WikidataItem::$quantity_units);

    $itemqid = isset($_REQUEST['qid']) ? $_REQUEST['qid'] : '';
    $fieldname = isset($_REQUEST['fieldname']) ? $_REQUEST['fieldname'] : '';
    $fieldvalue = isset($_REQUEST['value']) ? $_REQUEST['value'] : '';
    $fieldunit = isset($_REQUEST['unit']) ? $_REQUEST['unit'] : '';

    if ($fieldunit == 'gram' && $fieldvalue >= 1000) {
        if (strpos($fieldvalue, '.') !== false) {
            $fieldvalue = '' . round($fieldvalue, 0);
        }

        $precision = 3;
        if ($fieldvalue >= 10000) $precision = 2;
        elseif ($fieldvalue >= 100000) $precision = 1;
        elseif ($fieldvalue >= 1000000) $precision = 0;

        $fieldunit = 'kilogram';
        $fieldvalue = substr($fieldvalue, 0, -3) . '.' . substr($fieldvalue, -3);
        if ($precision != 3) $fieldvalue = substr($fieldvalue, 0, $precision - 3);
        $fieldvalue = rtrim($fieldvalue, '0');
        $fieldvalue = rtrim($fieldvalue, '.'); // Do not combine with above because whould remove 0's before the .
    }

    if ($fieldunit != '1') $fieldunit = "http://www.wikidata.org/entity/{$unitsids[$fieldunit]}";
    $uuid = UUID::getUUID();

    $wdwiki = new WikidataWiki();

    // login
    $mywikiname = Config::get('mywikiname');
    $mypassword = Config::get('mypassword');
    $wdwiki->login($mywikiname, $mypassword);

    // Get lastrevid
    $lastrevid = $wdwiki->getLastRevID($itemqid);

    // Get the csrf token
    $csrftoken = $wdwiki->getCSRFToken();

    // Build the claim
    $claim = '{"type":"statement","mainsnak":{"snaktype":"value","property":"' . $fields[$fieldname]['valueprop'] . '",';
    $claim .= '"datavalue":{"type":"quantity","value":{"amount":"+' . $fieldvalue . '","unit":"' . $fieldunit .'"}}},';
    $claim .= '"id":"' . "$itemqid\$$uuid" . '",';

    if (isset($fields[$fieldname]['qualifiers'])) {
        $claim .= '"qualifiers":{';
        $qualifier_order = [];
        $qualifier_cnt = 0;

        foreach ($fields[$fieldname]['qualifiers'] as $qprop => $qvalue) {
            if ($qualifier_cnt > 0) $claim .= ',';
            $claim .= '"' . $qprop . '":[{"snaktype":"value","property":"' . $qprop . '","datavalue":{"type":"wikibase-entityid","value":{"id":"' . $qvalue . '"}}}]';
            $qualifier_order[] = '"' . $qprop . '"';
            ++$qualifier_cnt;
        }

        $claim .= '},"qualifiers-order":[' . implode(',', $qualifier_order) . '],';
    }

    $claim .= '"references":[{"snaks":{"' . PROP_STATED_IN . '":[{"snaktype":"value","property":"' . PROP_STATED_IN . '","datavalue":{"type":"wikibase-entityid",';
    $claim .= '"value":{"id":"' . $dbqid . '"}}}]},"snaks-order":["' . PROP_STATED_IN . '"]}],"rank":"normal"}';

    // Create the claim
    $ret = $wdwiki->createClaim($lastrevid, $mywikiname, $csrftoken, $claim);

    echo $ret;
}

function add_stmt_test()
{
    $fields = [
        'BW' => ['valueprop' => 'P372', 'qualifiers' =>['P60' => 'Q9647']],
    ];

    $itemqid = 'Q103529';
    $fieldname = 'BW';
    $fieldvalue = '76';
    $fieldunit = "http://test.wikidata.org/entity/Q55407";
    $uuid = UUID::getUUID();

    $wdwiki = new WikidataWiki();

    // login
    $mywikiname = Config::get('mywikiname');
    $mypassword = Config::get('mypassword');
    $wdwiki->login($mywikiname, $mypassword);

    // Get lastrevid
    $lastrevid = $wdwiki->getLastRevID($itemqid);

    // Get the csrf token
    $csrftoken = $wdwiki->getCSRFToken();

    // Build the claim
    $claim = '{"type":"statement","mainsnak":{"snaktype":"value","property":"' . $fields[$fieldname]['valueprop'] . '",';
    $claim .= '"datavalue":{"type":"quantity","value":{"amount":"+' . $fieldvalue . '","unit":"' . $fieldunit .'"}}},';
    $claim .= '"id":"' . "$itemqid\$$uuid" . '",';

    if (isset($fields[$fieldname]['qualifiers'])) {
        $claim .= '"qualifiers":{';
        $qualifier_order = [];

        foreach ($fields[$fieldname]['qualifiers'] as $qprop => $qvalue) {
            $claim .= '"' . $qprop . '":[{"snaktype":"value","property":"' . $qprop . '","datavalue":{"type":"wikibase-entityid","value":{"id":"' . $qvalue . '"}}}]';
            $qualifier_order[] = '"' . $qprop . '"';
        }

        $claim .= '},"qualifiers-order":[' . implode(',', $qualifier_order) . '],';
    }

    $claim .= '"references":[{"snaks":{"P80":[{"snaktype":"value","property":"P80","datavalue":{"type":"wikibase-entityid",';
    $claim .= '"value":{"id":"Q142791"}}}]},"snaks-order":["P80"]}],"rank":"normal"}';

    Logger::log("lastrevid=$lastrevid");
    Logger::log("mywikiname=$mywikiname");
    Logger::log("csrftoken=$csrftoken");
    Logger::log("claim=$claim");

    // Create the claim
    $ret = $wdwiki->createClaim($lastrevid, $mywikiname, $csrftoken, $claim);

    echo $ret;
}
?>