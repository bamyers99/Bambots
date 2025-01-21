/**
 Copyright 2025 Myers Enterprises II

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
 Checks commons for a nearby photo if no {{P|18}} statement and has {{P|625}}. Displays above the Statements heading. Defaults to a 500 meter radius. Displays a link to WikiShootMe.

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 Bamyers99_PhotoNearby_radius = 500; // Optional search radius in meters (default: 500, max: 5000)
 importScript("User:Bamyers99/PhotoNearby.js");

 */

var Bamyers99 = Bamyers99 || {};

if (typeof Bamyers99_PhotoNearby_radius === 'undefined') Bamyers99_PhotoNearby_radius = 500;
else {
	Bamyers99_PhotoNearby_radius = parseInt(Bamyers99_PhotoNearby_radius, 10);
	if (Bamyers99_PhotoNearby_radius > 5000) Bamyers99_PhotoNearby_radius = 5000;
}

Bamyers99.PhotoNearby = {

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: 'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript', dataType: 'script', cache: true } )
		).done( function() {
			
			self.gc = Bamyers99.GadgetCommon;
			mw.hook( 'wikibase.entityPage.entityLoaded' ).add( function ( data ) {
				'use strict';
				
				if (! data.claims) return;

				if ('P18' in data.claims) return;
				if (! ('P625' in data.claims)) return;
				
				var claim = data.claims['P625'][0];
				
				if ( ! claim.mainsnak || ! claim.mainsnak.datavalue ) return;
				var dv = claim.mainsnak.datavalue;
				if ( dv.type !== 'globecoordinate' ) {
					return;
				}

				var latitude = dv.value.latitude, longitude = dv.value.longitude;
								
				var opts = {
					action: 'query',
					list: 'geosearch',
					gscoord: latitude + '|' + longitude,
					gsradius: Bamyers99_PhotoNearby_radius,
					gsnamespace: 6,
					gsprimary: 'all',
					lang: 'commons'
				};
		
				self.gc.mwApiQuery( opts, function( data ) {
					var imageCount = 0;
					
					if ( data.query && data.query.geosearch ) {
						imageCount = data.query.geosearch.length;
						if (imageCount == 10) imageCount = '' + imageCount + '+';
					}
				
					var h = '<div>';
					
					h += '<span>Commons images: ' + imageCount +
						' <a href="https://wikishootme.toolforge.org/#lat=' + latitude + '&lng=' + longitude + '&zoom=18">WikiShootMe</a></span>';
												
					h += '</div>';
					
					$( '#toc' ).before( h );
					
				});
	
			} );
		} );
	}
};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.PhotoNearby.init() ;
} );
