<?php
/**
 Copyright 2022 Myers Enterprises II

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

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'CleanupWorklistBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$params = array();

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

get_params();

switch ($action) {
    case 'getCSV':
        getCSV();
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
    $licenses = [
        'CC0-1.0' => 'Creative Commons Zero',
        'CC-BY-1.0' => 'Creative Commons Attribution 1.0',
        'CC-BY-2.0' => 'Creative Commons Attribution 2.0',
        'CC-BY-2.5' => 'Creative Commons Attribution 2.5',
        'CC-BY-3.0' => 'Creative Commons Attribution 3.0',
        'CC-BY-4.0' => 'Creative Commons Attribution 4.0',
        'CC-BY-4.0+' => 'Creative Commons Attribution 4.0 or later version',
        'CC-BY-SA-1.0' => 'Creative Commons Attribution-Share Alike 1.0',
        'CC-BY-SA-2.0' => 'Creative Commons Attribution-Share Alike 2.0',
        'CC-BY-SA-2.5' => 'Creative Commons Attribution-Share Alike 2.5',
        'CC-BY-SA-3.0' => 'Creative Commons Attribution-Share Alike 3.0',
        'CC-BY-SA-4.0' => 'Creative Commons Attribution-Share Alike 4.0',
        'CC-BY-SA-4.0+' => 'Creative Commons Attribution-Share Alike 4.0 or later version',
        'ODbL-1.0' => 'ODC Open Database License v1.0',
        'dl-de-zero-2.0' => 'Data licence Germany - Zero - Version 2.0',
        'dl-de-by-1.0' => 'Data licence Germany – attribution – Version 1.0',
        'dl-de-by-2.0' => 'Data licence Germany – attribution – version 2.0'
    ];
    
    $templates = [
        'USGS Watercourse' => [
            'license' => 'CC0-1.0',
            'description' => 'en|$MAPNAME course',
            'sources' => 'Map services and data available from U.S. Geological Survey, National Geospatial Program.',
            'zoom' => 'auto',
            'center' => 'auto'
        ]
    ];
    
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Commons Map Loader</title>
    	<link rel='stylesheet' type='text/css' href='css/catwl.css' />
   		<link rel='stylesheet' type='text/css' href='css/leaflet.css' />
	    <script type='text/javascript' src='js/jquery-2.1.1.min.js'></script>
	    <script type='text/javascript' src='js/leaflet.js'></script>
	</head>
	<body>
		<div style="display: table; margin: 0 auto;">
		<h2>Commons Map Loader</h2>
        <form id="mainform">
        <table class="form">
        <tr><td><b>Map name</b> (required)</td><td>Data:<input id="mapname" name="mapname" type="text" size="40" value="<?php echo $params['mapname'] ?>" />.map</td></tr>
        <tr><td><b>Template</b> (optional)</td><td><select name='template' onchange='cml_template_selected($(this).val())'><?php
            echo "<option value=''>&nbsp;</option>";
            foreach (array_keys($templates) as $key) {
                $selected = ($params['template'] == $key) ? " selected='1'" : '';
                echo "<option value='$key'$selected>$key</option>";
        	}
        ?></select></td></tr>
        <tr><td><b>License</b> (required)</td><td><select id='license' name='license'><?php
            echo "<option value=''>&nbsp;</option>";
            foreach ($licenses as $key => $license) {
                $selected = ($params['license'] == $key) ? " selected='1'" : '';
                echo "<option value='$key'$selected>$license ($key)</option>";
        	}
        ?></select></td></tr>
        <tr><td><b>Description</b> (optional)</td><td><input id="description" name="description" type="text" size="40" value="<?php echo $params['description'] ?>" /> lang|description</td></tr>
        <tr><td><b>Sources</b> (optional)</td><td><input id="sources" name="sources" type="text" size="40" value="<?php echo $params['sources'] ?>" /> Can use Wiki Markup</td></tr>
        <tr><td><b>Center point</b> (optional)</td><td>Latitude <input id="latitude" name="latitude" type="text" size="10" value="<?php echo $params['latitude'] ?>" /> Longitude <input id="longitude" name="longitude" type="text" size="10" value="<?php echo $params['longitude'] ?>" /></td></tr>
        <tr><td><b>Zoom level</b> (optional)</td><td><input id="zoom" name="zoom" type="text" size="5" value="<?php echo $params['zoom'] ?>" /> 1 to 18</td></tr>
        <tr><td colspan='2'><textarea rows="20" cols="90" id="geojson" name="geojson" onchange="cml_displayGeoData()"><?php echo htmlspecialchars($params['geojson']) ?></textarea></td></tr>
        <tr><td colspan='2'><input readonly type="text" id="geojson_length" size="3" /> characters</td></tr>
        </table>
        </form>
        <form id="submitform" method="post" target="_blank" onsubmit="cml_submitform(this)">
        <input type="submit" value="Submit" />
        <textarea id="wpTextbox1" name="wpTextbox1" style="display:none;"></textarea>
        </form>
        <div id="map_canvas" style="width:750px; height:500px"></div>
        
        <script type="text/javascript">
            if (document.getElementById) {
                var e = document.getElementById('mapname');
                e.focus();
                e.select();
            }
            
            $('#geojson').on("input paste", function() {
            	$("#geojson_length").val($(this).val().length);
            });
        	 
            var cml_map_tile_url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            var cml_map_attribution = '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

            var cml_templates = <?php echo json_encode($templates); ?>;
                        
    		var cml_agmap = L.map('map_canvas', {
    			center: [0, 0],
    			zoom: 1});
			
    		L.tileLayer(cml_map_tile_url,
    			{
    			maxZoom: 19,
    			attribution: cml_map_attribution
    			}).addTo(cml_agmap);

			var cml_geoJSON_layer = null;
    		
            function cml_displayGeoData() {
                var geodata = $("#geojson").val();

                if (cml_geoJSON_layer) {
                	cml_geoJSON_layer.remove();
                	cml_geoJSON_layer = null;
                }
        
                if (geodata.length) {
                	cml_geoJSON_layer = L.geoJSON(JSON.parse(geodata), {
                        style: function (feature) {
                            return {
                                color: feature.properties.stroke,
                                weight: feature.properties['stroke-width'],
                                opacity: feature.properties['stroke-opacity']
                            };
                        }
                    }).addTo(cml_agmap);
                	cml_agmap.fitBounds(cml_geoJSON_layer.getBounds());

                	var center = cml_agmap.getCenter();
                	$("#latitude").val(Number.parseFloat(center.lat).toFixed(5));
                	$("#longitude").val(Number.parseFloat(center.lng).toFixed(5));
                	$("#zoom").val(cml_agmap.getZoom());
                }
            }

            function cml_template_selected(templatename) {
                if (! cml_templates[templatename]) return;
                
                var template = cml_templates[templatename];

                if (template.license.length) {
                	$("#license").val(template.license);
                }

                if (template.description.length) {
                	$("#description").val(template.description.replace('$MAPNAME', $("#mapname").val()));
                }

                if (template.sources.length) {
                	$("#sources").val(template.sources);
                }
            }

            function cml_submitform(form) {
            	form.action='https://commons.wikimedia.org/w/index.php?action=edit&title=Data:' + $('#mapname').val().replace(' ','_') + '.map';
            	var description = $('#description').val().split('|');
            	var commonsjson = {
                	'license': $('#license').val()
                };

                if (description.length) commonsjson['description'] = {[description[0]]: description[1]};
                if ($('#sources').val().length) commonsjson['sources'] = $('#sources').val();
                if ($('#zoom').val().length) commonsjson['zoom'] = parseInt($('#zoom').val());
                if ($('#latitude').val().length) commonsjson['latitude'] = parseFloat($('#latitude').val());
                if ($('#longitude').val().length) commonsjson['longitude'] = parseFloat($('#longitude').val());
                
                var datamarker = '#DATAMARKER#';
                commonsjson['data'] = datamarker;
                commonsjson = JSON.stringify(commonsjson, null, 2);
                commonsjson = commonsjson.replace('"' + datamarker + '"', $("#geojson").val());
                    
            	$('#wpTextbox1').val(commonsjson);
            }

        </script>
        <br />
        <div><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://www.wikidata.org/wiki/User:Bamyers99">Bamyers99</a></div></div></body></html><?php
}

/**
 * Get the input parameters
 */
function get_params()
{
    global $params;
    
    $params = [];
    
    $params['mapname'] = isset($_REQUEST['mapname']) ? $_REQUEST['mapname'] : '';
    $params['template'] = isset($_REQUEST['template']) ? $_REQUEST['template'] : '';
    $params['license'] = isset($_REQUEST['license']) ? $_REQUEST['license'] : '';
    $params['description'] = isset($_REQUEST['description']) ? $_REQUEST['description'] : '';
    $params['sources'] = isset($_REQUEST['sources']) ? $_REQUEST['sources'] : '';
    $params['latitude'] = isset($_REQUEST['latitude']) ? $_REQUEST['latitude'] : '';
    $params['longitude'] = isset($_REQUEST['longitude']) ? $_REQUEST['longitude'] : '';
    $params['zoom'] = isset($_REQUEST['zoom']) ? $_REQUEST['zoom'] : '';
    $params['geojson'] = isset($_REQUEST['geojson']) ? $_REQUEST['geojson'] : '';
    
    if (! empty($params['lang']) && preg_match('!([a-zA-Z]+)!', $params['lang'], $matches)) {
        $params['lang'] = strtolower($matches[1]);
    }
    
    if (empty($params['lang']) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('!([a-zA-Z]+)!', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
        $params['lang'] = strtolower($matches[1]);
    }
    if (empty($params['lang'])) $params['lang'] = 'en';
    
}

?>