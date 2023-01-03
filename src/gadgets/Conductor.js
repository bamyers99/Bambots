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

 Description
 ===========
 Displays rail station and route information.

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 importScript("User:Bamyers99/Conductor.js");

 */

var Bamyers99 = Bamyers99 || {};

if (typeof Bamyers99_Conductor_testmode === 'undefined') Bamyers99_Conductor_testmode = false;

Bamyers99.Conductor = {
	commonjs: Bamyers99_Conductor_testmode ? 'https://bambots.brucemyers.com/GadgetCommon.js' :
		'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript',
	propAdjStation: 'P197',

	/**
	 * Init
	 */
	init: function() {
		var self = this ;
		var link = mw.util.addPortletLink(
			'p-tb',
			'#',
			'Conductor (rail)',
			't-Bamyers99.Conductor',
			"Railway station/route manager"
		);

		$( link ).click( function( e ) {
			e.preventDefault();
			self.showConductor();
		});
	},
	
	showConductor: function() {
		var self = this;
    	var lang = mw.config.get('wgUserLanguage');
    	var entityid = mw.config.get('wbEntityId');

		$.when(
			$.ajax( { url: self.commonjs, dataType: 'script', cache: true } )
		).done( function() {
			self.gc = Bamyers99.GadgetCommon;
			
			mw.hook( 'wikibase.entityPage.entityLoaded' ).add( function ( data ) {
				'use strict';

				var mainitem = entityid;
				
				// Get the adjacent stations
				var adjacents = [];

				$.each( data.claims[self.propAdjStation] || {}, function ( index, claim ) {
					if ( ! claim.mainsnak || ! claim.mainsnak.datavalue ) return true;
					adjacents.push(claim.mainsnak.datavalue.value.id);
				});
				
		        var api = new mw.Api({
		            ajax: {
		                url: 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?',
		                dataType: 'json',
		                cache: true
		            }
		        });
		        
		        api.get({
				    query: 'SELECT ?s ?sLabel ?coordinates ?color WHERE {\
					  VALUES ?s { ' + adjacents.join(' ') + ' } .\
					  ?s wdt:P625 ?coordinates .\
					  OPTIONAL {\
					    ?s wdt:P465 ?color .\
					    }\
					  SERVICE wikibase:label {bd:serviceParam wikibase:language "' + lang + ',en" .}\
					}'
		        }).done(function(data) {
		            var baseurl = 'https://www.wikidata.org/wiki/';
		            for (var k in data.results.bindings) {
		                var page = data.results.bindings[k];
		                var rank = page.rank.value.replace('http://wikiba.se/ontology#', '').replace('Rank', '').toLowerCase();
				});
			
			    var html = '\
			        <h2 class="wb-section-heading section-heading wikibase-statements" dir="auto"><spanclass="mw-headline">Railway station/route manager</span></h2>\
			        <div class="wikibase-statementgrouplistview" id="bamyers99-conductor"> \
			        </div>';
			        
		        $('.wikibase-entityview-main').append(html);
	        });
        });
	},
 	
 	/**
 	 * Convert degrees to radians
 	 */
 	deg2rad: function ( degrees ) {
  		return degrees * Math.PI / 180;
	},
 
 	/**
 	 * Convert radians to degrees
 	 */
	rad2deg: function ( radians ) {
	  return radians * 180 / Math.PI;
	},
 	
 	/**
     * Calculate a bearing in degrees.
     *
     * @param float lat1 Latitide 1
     * @param float long1 Longitude 1
     * @param float lat2 Latitude 2
     * @param float long2 Longitude 2
     * @return int Bearing in degrees
     */
	bearing: function (lat1, long1, lat2, long2)
	{
	    lat1 = self.deg2rad(lat1);
	    lat2 = self.deg2rad(lat2);
	    var dLon = self.deg2rad(long2 - long1);
	
	    var bearing = Math.atan2(Math.sin(dLon) * Math.cos(lat2), Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * 
	    	Math.cos(lat2) * Math.cos(dLon));
	
	    bearing = (self.rad2deg(bearing) + 360) % 360;
	
	    return bearing;
	}
};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.Conductor.init() ;
} );
