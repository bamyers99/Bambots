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
 Edit the raw data value for a statement, qualifier, reference.

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 importScript("User:Bamyers99/Fiddle.js");

 */

var Bamyers99 = Bamyers99 || {};

if (typeof Bamyers99_Fiddle_testmode === 'undefined') Bamyers99_Fiddle_testmode = false;

Bamyers99.Fiddle = {
	commonjs: Bamyers99_Fiddle_testmode ? 'https://bambots.brucemyers.com/GadgetCommon.js' :
		'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript',

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: self.commonjs, dataType: 'script', cache: true } ),
			mw.loader.using( ['jquery.ui', 'mw.Uri'] )
		).done( function() {
			self.gc = Bamyers99.GadgetCommon;
			
			mw.hook( 'wikibase.entityPage.entityLoaded' ).add( function ( data ) {
				'use strict';
								
				var link = mw.util.addPortletLink(
					'p-tb',
					'#',
					'Value editor',
					't-Bamyers99.Fiddle',
					"Edit a statement, qualifier, reference value"
				);

				$( link ).click( function( e ) {
					e.preventDefault();
					self.linkValues();
				});
	
			} );
		} );
	},
	
	linkValues: function() {
		var entityId = mw.config.get( 'wbEntityId' ),
		url,
		specialEntityDataPath;
		
		// Remove previous links
		
		specialEntityDataPath = mwConfig.get( 'wgArticlePath' ).replace(
			/\$1/g, 'Special:EntityData/' + entityId + '.json'
		);
		url = new mw.Uri( specialEntityDataPath );
		url.extend( { revision: mwConfig.get( 'wgRevisionId' ) } );
		
		$.getJSON( url.toString(), function ( data ) {
			$.each( data.claims[self.propCoordinate] || {}, function ( index, claim ) {
				if ( ! claim.mainsnak || ! claim.mainsnak.datavalue ) return true;
	
				var dv = claim.mainsnak.datavalue;
	
				if ( claim['qualifiers-order'] && claim.qualifiers ) {
					$.each( claim['qualifiers-order'], function ( i, prop ){
						claimQualifiers[prop] = claim.qualifiers[prop];
					});
				} else if ( claim.qualifiers ) {
					claimQualifiers = claim.qualifiers;
				}
	
				$.each( claimQualifiers, function ( prop, qualifiers) {
	
					$.each( qualifiers, function ( index, qualifier ) {
					} )
				} );
				
				var qid = mw.config.get( 'wgPageName' ).toUpperCase();
				self.qid = qid;
				
				var h = ' <a class="Bamyers99_Fiddle_editLink" ' +
					'data-claimid="' + self.gc.htmlEncode( claim.id ) + '"' +
					'>&#127931;</a>';
					
				var $claimview = $( '.wikibase-statementview' )
					.filter( function () {
						return $( this ).hasClass( 'wikibase-statement-' + claim.id );
					});
				
				$claimview.find( ' .wikibase-statementview-mainsnak .wikibase-snakview-value' ).append( h );
				
				$( 'a.Bamyers99_Fiddle_editLink' ).click( function() {
					var globeQid = $( this ).attr( 'data-qid' );
					var claimId = $( this ).attr( 'data-claimid' );
		
					self.displayDialog( globeQid, claimId);
					return false;
				} );
			} );
		} );
	},
	
	/**
	 * Display the dialog
	 */
	displayDialog: function( globeQid, claimId ) {
		var self = this;
		var h = '<div id="Bamyers99_Fiddle_dialog">';

		h += '<div id="Bamyers99_Fiddle_form">';
		h += '<div id="Bamyers99_Fiddle_msg"></div>';

		h += '<select id="Bamyers99_Fiddle_select">';
		
		for (var key in self.globes) {
			h += '<option value="' + key + '"';
			if (key == globeQid) h += ' selected="1"';
			h += '>' + self.globes[key] + '</option>';
		}
		
		h += '</select><br /><br />';
		
		h += '<input type="button" id="Bamyers99_Fiddle_setValue" value="Update value" />';

		h += '</div></div>';
		$( '#mw-content-text' ).append( h );

		$( '#Bamyers99_Fiddle_setGlobe' ).click( function() {
			var selectedQid = $("#Bamyers99_Fiddle_select").val();
			
			// Get the current claim
			self.gc.wdGetClaim(claimId, function( success, data ) {
				if (! success) {
					$( '#Bamyers99_Fiddle_msg' ).html( data );
					return;
				}
				
				if (! data.mainsnak || ! data.mainsnak.datavalue || ! data.mainsnak.datavalue.value ||
					! data.mainsnak.datavalue.value.globe) {
					$( '#Bamyers99_Fiddle_msg' ).html( 'current value is missing globe' );
					return;						
				}

				data.mainsnak.datavalue.value.globe = 'http://www.wikidata.org/entity/' + selectedQid;
	
				self.gc.wdSetClaim(self.qid, JSON.stringify(data), function( success, msg ) {
					msg = success ? 'Globe set. <a href="/wiki/' + self.qid + '">Reload page</a>' : msg;
					$( '#Bamyers99_Fiddle_msg' ).html( msg );
	
					if ( success ) {
						$( '#Bamyers99_Fiddle_globe' ).html( self.globes[selectedQid] );
					}
				} );
			} );

			return false;
		} );

		$( '#Bamyers99_Fiddle_dialog' ).dialog( {
			title : 'Value Editor',
			width : 'auto',
			position : { my: 'left top', at: 'right top', of: $( '#claims' ) },
			open: function( event, ui ) {
				$('#Bamyers99_Fiddle_dialog').css ( { 'font-size': '12pt', 'font-family': 'Arial,Helvetica,sans-serif' } );
			},
			close: function( event, ui ) {
				$( '#Bamyers99_Fiddle_dialog' ).remove();
			}
		} );
	}
};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.Fiddle.init() ;
} );
