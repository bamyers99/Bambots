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
 Displays presence/absence of specific properties (configurable) after the Statements heading.

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 importScript("User:Bamyers99/HasProperty.js");

 */

var Bamyers99 = Bamyers99 || {};

Bamyers99.HasProperty = {
	cookieName: 'Bamyers99HasProperty',

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: 'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript', dataType: 'script', cache: true } ),
			mw.loader.using([ 'jquery.ui', 'mediawiki.cookie' ])
		).done( function() {
			
			self.gc = Bamyers99.GadgetCommon;
			mw.hook( 'wikibase.entityPage.entityLoaded' ).add( function ( data ) {
				'use strict';
				
				if (! data.claims) return;
				
				var cookie = mw.cookie.get(self.cookieName);
				if (! cookie || cookie.length == 0) return;
				var aProps = cookie.split(',');
				var oProps = {};
				var labels = {};
				
				for (const name of aProps) {
				   oProps[name] = false;
				   labels[name] = name;
				}

				$.each( data.claims || {}, function ( prop, claims ) {
					if (prop in oProps) oProps[prop] = true;
				} );
				
				var opts = {
					action: 'wbgetentities',
					props: 'labels',
					ids: $.map( labels, function( v, k ) {
							return k;
						} ).join( '|' ),
					lang: 'wikidata'
				};
		
				self.gc.mwApiQuery( opts, function( data ) {
					if ( data.entities ) {
						var userLang = mw.config.get( 'wgUserLanguage' ) ;
		
						$.each( data.entities, function( id, itemdata ) {
							if ( itemdata.labels ) {
								var label = ( itemdata.labels[userLang] && itemdata.labels[userLang].value ) ||
									( itemdata.labels.en && itemdata.labels.en.value ) || false;
								if ( label ) labels[id] = label;
							}
						});
					}
				
					var h = ' <span>';
					var count = 0;
					
					for (const prop in oProps) {
						var label = labels[prop];
						if (count) h += ', ';
						
						if (oProps[prop]) {
							h += '<a href="#' + prop + '">' + label + '</a>';
						} else {
							h += '<span style="text-decoration: line-through #DB4325;">' + label + '</span>';						
						}
						
						count += 1;
					}
					
					h += ' <a id="Bamyers99_HasProperty_editLink">Edit</a>';
												
					h += '</span>';
					
					$( '#claims' ).append( h );
					
					$( '#Bamyers99_HasProperty_editLink' ).click( function() {
						self.displayDialog();
						return false;
					} );
					
				});
	
			} );
		} );
	},
	
	/**
	 * Display the dialog
	 */
	displayDialog: function() {
		var self = this;
		var h = '<div id="Bamyers99_HasProperty_dialog">';

		h += '<div id="Bamyers99_HasProperty_form">';
		h += '<div id="Bamyers99_HasProperty_msg">Enter a comma (,) separated list of properties. ie. P123,P456</div>';

		h += '<input type="text" id="Bamyers99_HasProperty_props" />';
		
		h += '<br /><br />';
		
		h += '<input type="button" id="Bamyers99_HasProperty_save" value="Save" />';
		h += '<input type="button" id="Bamyers99_HasProperty_cancel" value="Cancel" />';

		h += '</div></div>';
		$( '#mw-content-text' ).append( h );

		$( '#Bamyers99_HasProperty_save' ).click( function() {
			var props = $("#Bamyers99_HasProperty_props").val();
			mw.cookie.set(self.cookieName, props, new Date(2037, 12, 31));
			location.reload();

			return false;
		} );
		
		$( '#Bamyers99_HasProperty_cancel' ).click( function() {
			$( '#Bamyers99_HasProperty_dialog' ).dialog( "close" );
			
			return false;
		} );

		$( '#Bamyers99_HasProperty_dialog' ).dialog( {
			title : 'Has Property',
			width : 'auto',
			position : { my: 'left top', at: 'right top', of: $( '#claims' ) },
			open: function( event, ui ) {
				$('#Bamyers99_HasProperty_dialog').css ( { 'font-size': '12pt', 'font-family': 'Arial,Helvetica,sans-serif' } );
			},
			close: function( event, ui ) {
				$( '#Bamyers99_HasProperty_dialog' ).remove();
			}
		} );
	}
};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.HasProperty.init() ;
} );
