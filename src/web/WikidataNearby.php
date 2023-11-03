<?php
/**
 Copyright 2023 Myers Enterprises II

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
use com_brucemyers\MediaWiki\WikidataSPARQL;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');
define('CACHE_PREFIX_WDNEAR', 'WDNEAR:');

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = [];

get_params();

// Redirect to get the results so have a bookmarkable url
if (isset($_POST['lat']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
	$host  = $_SERVER['HTTP_HOST'];
	$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$extra = 'WikidataNearby.php?lat=' . urlencode($params['lat']) . '&lon=' . urlencode($params['lon']) . '&radius=' . urlencode($params['radius']) .
	   '&class=' . urlencode($params['class']) . '&includesubs=' . urlencode($params['includesubs']) . '&lang=' . urlencode($params['lang']);
	$protocol = HttpUtil::getProtocol();
	header("Location: $protocol://$host$uri/$extra", true, 302);
	exit;
}

$items = get_items();

display_form($items);

/**
 * Display form
 *
 */
function display_form($items)
{
	global $params;
	$title = '';

	$title = htmlentities($title, ENT_COMPAT, 'UTF-8');
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title><?php echo 'Wikidata Nearby' . $title ?></title>
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
		            	$("#class").val(suggestion.data.id);
		            }
		        });
		        }
			);
		</script>
		<div style="display: table; margin: 0 auto;">
		<h2><a href="WikidataNearby.php">Wikidata Nearby</a><?php echo $title ?></h2>
        <form action="WikidataNearby.php" method="post"><table class="form">
        <tr><td><b>Latitide or Lat,Long</b></td><td><input id="lat" name="lat" type="text" size="15" value="<?php echo $params['lat'] ?>" /> <b>Longitude</b> <input id="lon" name="lon" type="text" size="15" value="<?php echo $params['lon'] ?>" /></td></tr>
        <tr><td><b>Radius (kilometers)</b></td><td><input id="radius" name="radius" type="text" size="4" value="<?php echo $params['radius'] ?>" /> max: 25</td></tr>
        <tr><td><b>Class QID (optional)</b></td><td><input id="class" name="class" type="text" size="10" value="<?php echo $params['class'] ?>" /> or <b>Label/Alias</b> <input id="labelalias" name="labelalias" type="text" size="15" /></td></tr>
        <tr><td><b>Include subclasses</b></td><td><input id="includesubs" name="includesubs" type="checkbox" value="1" <?php if ($params['includesubs']) echo 'checked' ?> /></td></tr>
        <tr><td><b>Name/description<br />language code</b></td><td><input id="lang" name="lang" type="text" size="4" value="<?php echo $params['lang'] ?>" /></td></tr>
        <tr><td><input type="submit" value="Submit" /></td><td>&nbsp;</td></tr>
        </table>
        </form>
        <br />
<?php
    if (! empty($items['items'])) {
        if ($items['resulttype'] == 'noclass') {
            echo "<b>Items with class not found, other results below</b><br />\n";
        }
        
        echo "<table class='wikitable tablesorter'><thead><tr><th>Place</th><th>Distance (km)</th></tr></thead><tbody>\n";
        
        foreach ($items['items'] as $item) {
            $name = htmlspecialchars($item['placeLabel']['value']);
            $url = $item['place']['value'];
            $place = '<a href="' . $url . '">' . $name. '</a>';
            $dist = $item['dist']['value'];
            echo "<tr><td>$place</td><td>$dist</td></tr>\n";
        }
        
        echo "</tbody></table>\n";
    } else {
        echo "<b>No results</b><br /><br />\n";
    }
?>
       <div><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
}

/**
 * Get items
 */
function get_items()
{
	global $params;

	$return = [];
	
	$class = '';
	if (! empty($params['class'])) {
	    $operator = 'wdt:P31';
	    
	    if (! empty($params['includesubs'])) {
	        $operator = 'wdt:P31/wdt:P279*';
	    }
	    
	    $class = "?place $operator wd:{$params['class']} .\n";
	}

	$sparql = get_sparql($class, $params['lat'], $params['lon'], $params['radius'], $params['lang']);
  
    $sparql = rawurlencode($sparql);
	
    $wdsparql = new WikidataSPARQL();
    
    $results = $wdsparql->query($sparql);
    $resulttype = 'normal';
    
    if (empty($results) && ! empty($class)) {
        $sparql = get_sparql('', $params['lat'], $params['lon'], $params['radius'], $params['lang']);
        
        $sparql = rawurlencode($sparql);
        
        $results = $wdsparql->query($sparql);
        $resulttype = 'noclass';
    }
    
    $return = ['items' => $results, 'resulttype' => $resulttype];
    
	return $return;
}


function get_sparql($class, $lat, $lon, $radius, $lang)
{
    $sparql = <<<EOT
SELECT DISTINCT ?place ?placeLabel ?location ?dist WHERE {
  $class
  SERVICE wikibase:around {
      ?place wdt:P625 ?location .
      bd:serviceParam wikibase:center "Point($lon $lat)"^^geo:wktLiteral .
      bd:serviceParam wikibase:radius "$radius" .
      bd:serviceParam wikibase:distance ?dist .
  }
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "$lang,en" .
  }
}
ORDER BY ASC(?dist)
LIMIT 100
EOT;
  
  return $sparql;
}

/**
 * Get the input parameters
 */
function get_params()
{
	global $params;

	$params = [];

	$params['lat'] = '';
	if (isset($_REQUEST['lat'])) $params['lat'] = trim($_REQUEST['lat']);
	
	$params['lon'] = '';
	if (isset($_REQUEST['lon'])) $params['lon'] = trim($_REQUEST['lon']);
	
	if (strpos($params['lat'], ',') !== false) {
	    list($lat, $lon) = explode(',', $params['lat'], 2);
	    $params['lat'] = trim($lat);
	    $params['lon'] = trim($lon);
	}
	
	if (! empty($params['lat'])) $params['lat'] = round($params['lat'], 6);
	if (! empty($params['lon'])) $params['lon'] = round($params['lon'], 6);
	
	$params['radius'] = '5';
	if (isset($_REQUEST['radius'])) $params['radius'] = trim($_REQUEST['radius']);
	$params['radius'] = intval($params['radius']);
	if (empty($params['radius'])) $params['radius'] = 5;
	if ($params['radius'] > 25) $params['radius'] = 25;
	
	$params['class'] = '';
	if (isset($_REQUEST['class'])) $params['class'] = trim($_REQUEST['class']);
	
	$params['includesubs'] = '';
	if (isset($_REQUEST['includesubs'])) $params['includesubs'] = trim($_REQUEST['includesubs']);
	
	$params['lang'] = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : '';

	if (! empty($params['lang']) && preg_match('!([a-zA-Z]+)!', $params['lang'], $matches)) {
		$params['lang'] = strtolower($matches[1]);
	}

	if (empty($params['lang']) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('!([a-zA-Z]+)!', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
		$params['lang'] = strtolower($matches[1]);
	}
	
	if (empty($params['lang'])) $params['lang'] = 'en';
}
?>