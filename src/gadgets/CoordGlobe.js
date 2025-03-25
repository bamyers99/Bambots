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

 Description
 ===========
 Displays the globe for a coordinate (P625). Allows to change the globe.

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 importScript("User:Bamyers99/CoordGlobe.js");

 */

var Bamyers99 = Bamyers99 || {};

if (typeof Bamyers99_CoordGlobe_testmode === 'undefined') Bamyers99_CoordGlobe_testmode = false;

Bamyers99.CoordGlobe = {
	commonjs: Bamyers99_CoordGlobe_testmode ? 'https://bambots.brucemyers.com/GadgetCommon.js' :
		'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript',
	propCoordinate: 'P625',
	globeLabel: {
		en: 'globe',
		de: 'Globus',
		es: 'globo',
		fr: 'globe terrestre',
		it: 'globo',
		pt: 'globo terrestre'
	},
	globes: { // source: https://github.com/wikimedia/Wikibase/blob/master/repo/config/Wikibase.default.php globeUris
		'Q3257': 'amalthea',
		'Q3343': 'ariel',
		'Q11558': 'bennu',
		'Q3134': 'callisto',
		'Q596': 'ceres',
		'Q6604': 'charon',
		'Q844672': 'churyumov',
		'Q7548': 'deimos',
		'Q2265762': 'didymos',
		'Q25387442': 'dimorphos',
		'Q15040': 'dione',
		'Q2': 'earth',
		'Q3303': 'enceladus',
		'Q17751': 'epimetheus',
		'Q16711': 'eros',
		'Q3143': 'europa',
		'Q3169': 'ganymede',
		'Q158244': 'gaspra',
		'Q15037': 'hyperion',
		'Q17958': 'iapetus',
		'Q149012': 'ida',
		'Q510728': 'ida i dactyl',
		'Q3123': 'io',
		'Q149374': 'itokawa',
		'Q17754': 'janus',
		'Q319': 'jupiter',
		'Q107556': 'lutetia',
		'Q111': 'mars',
		'Q149417': 'mathilde',
		'Q308': 'mercury',
		'Q15034': 'mimas',
		'Q3352': 'miranda',
		'Q405': 'moon',
		'Q3332': 'oberon',
		'Q7547': 'phobos',
		'Q17975': 'phoebe',
		'Q339': 'pluto',
		'Q16081': 'proteus',
		'Q15662': 'puck',
		'Q15050': 'rhea',
		'Q1385178': 'ryugu',
		'Q193': 'saturn',
		'Q150249': 'steins',
		'Q15047': 'tethys',
		'Q16765': 'thebe',
		'Q2565': 'titan',
		'Q3322': 'titania',
		'Q3359': 'triton',
		'Q3338': 'umbriel',
		'Q324': 'uranus',
		'Q313': 'venus',
		'Q3030': 'vesta'
	},
	userAgent: 'CoordGlobe/1.0 (User:Bamyers99)',

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: self.commonjs, dataType: 'script', cache: true } ),
			mw.loader.using( 'jquery.ui' )
		).done( function() {
			self.gc = Bamyers99.GadgetCommon;
			
			mw.hook( 'wikibase.entityPage.entityLoaded' ).add( function ( data ) {
				'use strict';
				
				if (! data.claims) return;

				$.each( data.claims[self.propCoordinate] || {}, function ( index, claim ) {
					if ( ! claim.mainsnak || ! claim.mainsnak.datavalue ) return true;

					var dv = claim.mainsnak.datavalue;

					if ( dv.type !== 'globecoordinate' ) {
						return true;
					}
					
					var lang = mw.config.get( 'wgUserLanguage' );
					var qid = mw.config.get( 'wgPageName' ).toUpperCase();
					self.qid = qid;

					var globeQid = dv.value.globe.match(/http:\/\/www.wikidata.org\/entity\/(.*)/);
					if (globeQid) globeQid = globeQid[1];
					var globe = self.globes[globeQid] || 'unknown';
					
					var globelabel = self.globeLabel[lang] ? self.globeLabel[lang] : 'globe';
					
					var h = ' ' + globelabel + ': <a class="Bamyers99_CoordGlobe_editLink" ' +
						'data-qid="' + globeQid + '" data-claimid="' + self.gc.htmlEncode( claim.id ) + '"' +
						'><span id= "Bamyers99_CoordGlobe_globe" title="change ' + '">' + globe + '</span></a>';
						
					var $claimview = $( '.wikibase-statementview' )
						.filter( function () {
							return $( this ).hasClass( 'wikibase-statement-' + claim.id );
						});
					
					$claimview.find( ' .wikibase-statementview-mainsnak .wikibase-snakview-value' ).append( h );
					
					$( 'a.Bamyers99_CoordGlobe_editLink' ).click( function() {
						var globeQid = $( this ).attr( 'data-qid' );
						var claimId = $( this ).attr( 'data-claimid' );
			
						self.displayDialog( globeQid, claimId);
						return false;
					} );
				} );
	
			} );
		} );
	},
	
	/**
	 * Display the dialog
	 */
	displayDialog: function( globeQid, claimId ) {
		var self = this;
		var h = '<div id="Bamyers99_CoordGlobe_dialog">';

		h += '<div id="Bamyers99_CoordGlobe_form">';
		h += '<div id="Bamyers99_CoordGlobe_msg"></div>';

		h += '<select id="Bamyers99_CoordGlobe_select">';
		
		for (var key in self.globes) {
			h += '<option value="' + key + '"';
			if (key == globeQid) h += ' selected="1"';
			h += '>' + self.globes[key] + '</option>';
		}
		
		h += '</select><br /><br />';
		
		h += '<input type="button" id="Bamyers99_CoordGlobe_setGlobe" value="Update globe" />';

		h += '</div></div>';
		$( '#mw-content-text' ).append( h );

		$( '#Bamyers99_CoordGlobe_setGlobe' ).click( function() {
			var selectedQid = $("#Bamyers99_CoordGlobe_select").val();
			
			// Get the current claim
			self.gc.wdGetClaim(claimId, self.userAgent, function( success, data ) {
				if (! success) {
					$( '#Bamyers99_CoordGlobe_msg' ).html( data );
					return;
				}
				
				if (! data.mainsnak || ! data.mainsnak.datavalue || ! data.mainsnak.datavalue.value ||
					! data.mainsnak.datavalue.value.globe) {
					$( '#Bamyers99_CoordGlobe_msg' ).html( 'current value is missing globe' );
					return;						
				}

				data.mainsnak.datavalue.value.globe = 'http://www.wikidata.org/entity/' + selectedQid;
	
				self.gc.wdSetClaim(self.qid, JSON.stringify(data), self.userAgent, function( success, msg ) {
					msg = success ? 'Globe set. <a href="/wiki/' + self.qid + '">Reload page</a>' : msg;
					$( '#Bamyers99_CoordGlobe_msg' ).html( msg );
	
					if ( success ) {
						$( '#Bamyers99_CoordGlobe_globe' ).html( self.globes[selectedQid] );
					}
				} );
			} );

			return false;
		} );

		$( '#Bamyers99_CoordGlobe_dialog' ).dialog( {
			title : 'Globe Changer',
			width : 'auto',
			position : { my: 'left top', at: 'right top', of: $( '#claims' ) },
			open: function( event, ui ) {
				$('#Bamyers99_CoordGlobe_dialog').css ( { 'font-size': '12pt', 'font-family': 'Arial,Helvetica,sans-serif' } );
			},
			close: function( event, ui ) {
				$( '#Bamyers99_CoordGlobe_dialog' ).remove();
			}
		} );
	}
};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.CoordGlobe.init() ;
} );
