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

 Description
 ===========
 If an item does not have an 'instance of' property, displays a suggestion box.
 Otherwise, adds a toolbox link to show the suggestion box and adds a link to
 each 'instance of' to display the class browser.

 Heuristic
 =========
 1) Get the item's categories from an interwiki page
 2) Choose 3 categories with the least number of pages (minimum: 5)
 3) Get the instance of's for 10 items in each category

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 importScript("User:Bamyers99/ClassSuggester.js");

 */

var Bamyers99 = Bamyers99 || {};

if (typeof Bamyers99_ClassSuggester_testmode === 'undefined') Bamyers99_ClassSuggester_testmode = false;

Bamyers99.ClassSuggester = {
	commonjs: Bamyers99_ClassSuggester_testmode ? 'https://tools.wmflabs.org/bambots/GadgetCommon.js' :
		'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript',
	propInstanceof: 'P31',
	propSubclass: 'P279',
	instanceofIgnores: ['Q13406463'],
	langPriority: ['en','de','es','fr','it','pt'],
	suggestTimeout: 30000, // 30 seconds

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: self.commonjs, dataType: 'script', cache: true } ),
			mw.loader.using( 'jquery.ui.dialog' )
		).done( function() {
			self.gc = Bamyers99.GadgetCommon;

			// See if has instance of or subclass
			var subclassFound = false;
			var instanceOfQidFound = false;

			$( '.wikibase-statementgroupview' ).each( function () {
				var pid = $( this ).attr( 'id' );

				if (pid === self.propSubclass) {
					subclassFound = true;
					return false;
				}

				if (pid === self.propInstanceof) {
					var lang = mw.config.get('wgUserLanguage');

					$( this ).find( '.wikibase-statementview-mainsnak-container' )
					.find( '.wikibase-snakview-value' )
					.each( function () {
						instanceOfQidFound = true;
						var qid = $( this ).find( 'a' ).attr( 'title' );
						if ( qid ) {
							var h = ' <a href="https://tools.wmflabs.org/bambots/WikidataClasses.php?id=' +
								qid + '&lang=' + lang +
								'"><span style="font-size: 16pt" title="view in class browser">&telrec;</span></a>';
							$( this ).append( h );
						}
					} );
				}
			} );

			// Ignore if subclass
			if ( subclassFound ) return;

			var qid = mw.config.get( 'wgPageName' ).toUpperCase();
			self.qid = qid;

			// Add toolbox link if has instanceof
			if ( instanceOfQidFound ) {
				var link = mw.util.addPortletLink(
					'p-tb',
					'#',
					'Class suggester',
					't-Bamyers99.ClassSuggester',
					"Suggest alternate 'instance of' classes"
				);

				$( link ).click( function( e ) {
					e.preventDefault();
					self.showSuggestions( true );
				});

				return;
			}

			self.showSuggestions( false );
		} );
	},

	/**
	 * Show class suggestions
	 *
	 * @param bool toolbar
	 */
	showSuggestions: function( toolbar ) {
		var self = this,
			iws = {},
			firstlang ='';

		// Look for an interwiki in language priority order
		$( 'div[data-wb-sitelinks-group="wikipedia"] .wikibase-sitelinkview-page a' ).each( function ( k, v ) {
			var $v = $( v );
			var lang = $v.attr( 'hreflang' );
			var title = $v.attr( 'title' );
			iws[lang] = title;
			if ( ! firstlang ) firstlang = lang;
		} );

		if ( $.isEmptyObject( iws ) ) {
			self.displayDialog( { type: 'suggestions' } );
			return;
		}

		var lang = '',
			page = '';

		$.each( self.langPriority, function( k, v ) {
			if ( iws[v] ) {
				lang = v;
				page = iws[v];
				return false;
			}
		} );

		if ( ! lang ) {
			lang = firstlang;
			page = iws[firstlang];
		}

		self.showSuggestionsToolLabs( lang, page, toolbar );
	},

	/**
	 * Show class suggestions using the Tool Labs WikidataClass.php API
	 */
	showSuggestionsToolLabs: function( lang, page, toolbar ) {
		var userLang = mw.config.get('wgUserLanguage');
		var self = this,
			opts = {
				action: 'suggest',
				lang: lang,
				page: page,
				userLang: userLang
			};

		$.ajax({
			  dataType: "jsonp",
			  url: 'https://tools.wmflabs.org/bambots/WikidataClasses.php?callback=?',
			  data: opts,
			  timeout: self.suggestTimeout
			} )
			.fail( function() {
				self.showSuggestionsMwApi( lang, page );
			} )
			.done( function( data ) {
				self.displayDialog( { type: 'suggestionsToolLabs', data: data , toolbar: toolbar } );
			} );
	},

	/**
	 * Show class suggestions using the MediaWiki API
	 */
	showSuggestionsMwApi: function( lang, page ) {
		var self = this;

		// Retrieve the pages categories
		var opts = {
			prop: 'categoryinfo',
			generator: 'categories',
			gcllimit: 10,
			gclshow: '!hidden',
			titles: page,
			lang: lang
		};

		self.gc.mwApiQuery( opts, function( data ) {
			var cat = '',
				minpages = Number.MAX_VALUE;
			if ( ! data.query || ! data.query.pages ) return;

			// Choose the smallest category with at least 10 pages and no year in the title
			$.each( data.query.pages, function( id, catdata ) {
				if ( catdata.title.search( /\d{4}/ ) !== -1 ) return;
				if ( catdata.categoryinfo.pages < 10 ) return;
				if ( catdata.categoryinfo.pages < minpages ) {
					cat = catdata.title;
					minpages = catdata.categoryinfo.pages;
				}
			} );

			if ( ! cat ) {
				self.displayDialog( { type: 'suggestions' } );
				return;
			}

			// Retrieve the category member qids
			var qids = [];
			var opts = {
				prop: 'pageprops',
				ppprop: 'wikibase_item',
				generator: 'categorymembers',
				gcmtitle: cat,
				gcmlimit: 10,
				gcmtype: 'page',
				lang: lang
			};

			self.gc.mwApiQuery( opts, function( data ) {
				if ( ! data.query || ! data.query.pages ) return;

				$.each( data.query.pages, function( id, pagedata ) {
					if ( ! pagedata.pageprops || ! pagedata.pageprops.wikibase_item ) return;
					if ( pagedata.pageprops.wikibase_item !== self.qid )
						qids.push( pagedata.pageprops.wikibase_item );
				} );

				if (! qids.length ) {
					self.displayDialog( { type: 'suggestions' } );
					return;
				}

				// Retrieve the item claims and look for instance of
				var instanceofs = {};
				var opts = {
					action: 'wbgetentities',
					props: 'claims',
					ids: qids.join( '|' ),
					lang: 'wikidata'
				};

				self.gc.mwApiQuery( opts, function( data ) {
					if ( ! data.entities ) return;

					$.each( data.entities, function( id, itemdata ) {
						if ( ! itemdata.claims || ! itemdata.claims[self.propInstanceof] ) return;
						$.each( itemdata.claims[self.propInstanceof], function( k, snak ) {
							var qid = 'Q' + snak.mainsnak.datavalue.value['numeric-id'];
							if ( $.inArray( qid, self.instanceofIgnores ) !== -1 ) return;

							if ( instanceofs[qid] ) ++instanceofs[qid].cnt;
								else instanceofs[qid] = { cnt: 1 } ;
						} );
					} );

					if ( $.isEmptyObject( instanceofs ) ) {
						self.displayDialog( { type: 'suggestions' } );
						return;
					}

					// Retrieve suggestion label and description
					$.each( instanceofs, function( k, v) {
						if ( v.cnt === 1 ) delete instanceofs[k];
					} );

					var opts = {
						action: 'wbgetentities',
						props: 'labels|descriptions',
						ids: $.map( instanceofs, function( v, k ) {
								return k;
							} ).join( '|' ),
						lang: 'wikidata'
					};

					self.gc.mwApiQuery( opts, function( data ) {
						if ( data.entities ) {
							var userLang = mw.config.get('wgUserLanguage') ;

							$.each( data.entities, function( id, itemdata ) {
								if ( itemdata.labels ) {
									var label = ( itemdata.labels[userLang] && itemdata.labels[userLang].value ) ||
										( itemdata.labels.en && itemdata.labels.en.value ) || false;
									if ( label ) instanceofs[id].label = label;
								}

								if ( itemdata.descriptions ) {
									var desc = ( itemdata.descriptions[userLang] && itemdata.descriptions[userLang].value ) ||
										( itemdata.descriptions.en && itemdata.descriptions.en.value ) || false;
									if ( desc ) instanceofs[id].desc = desc;
								}
							} );
						}

						// Sort by descending count
						instanceofs = $.map( instanceofs, function( v, k ) {
							return { qid: k, info: v };
						} );

						instanceofs.sort( function( a, b ) {
							if ( a.info.cnt > b.info.cnt ) {
								return -1;
							}
							if ( a.info.cnt < b.info.cnt ) {
								return 1;
							}
							return 0;
						} );

						self.displayDialog( { type: 'suggestions', data: instanceofs } );
					} );
				} );
			} );
		} );
	},

	/**
	 * Display the dialog
	 *
	 * @param object params
	 * 		type: string - 'suggestions', 'suggestionsToolLabs'
	 * 		data: array of data
	 * 		reason: string - fail reason
	 */
	displayDialog: function( params ) {
		var reason, data, star, childs,
			type = params.type,
			self = this;
		var h = '<div id="Bamyers99_ClassSuggester_dialog">';

		h += '<div id="Bamyers99_ClassSuggester_suggestions">';
		h += '<div id="Bamyers99_ClassSuggester_msg"></div>';

		if ( type === 'suggestions' ) {
			data = params.data || [];
			if ( data.length === 0 ) {
				reason = params.reason || '';
				h += '<div>No suggestions</div><div>' + reason + '</div>';
			} else {
				$.each( data, function ( k, v ) {
					var label = v.info.label || v.info.qid;
					var desc = v.info.desc || '';
					h += '<div title="' + desc + '">' + label + '</div>';
				} );
			}

		} else if ( type === 'suggestionsToolLabs' ) {
			data = params.data || {};

			if ( $.isEmptyObject( data ) ) {
				reason = params.reason || '';
				h += '<div>No suggestions</div><div>' + reason + '</div>';

			} else {
				var lang = mw.config.get('wgUserLanguage');

				$.each( data, function ( k, v ) {
					star = v.catcnt ? ' <span style="color: #00f">&starf;</span>' : '';
					var label = '<span title="' + v.desc + '">' + v.label + '</span>';
					h += '<div>' + label + star +
						' <a target="_blank" href="https://tools.wmflabs.org/bambots/WikidataClasses.php?id=' +
						v.qid + '&lang=' + lang + '"><span style="font-size: 16pt" title="view in class browser">&telrec;</span></a>';
					h += ' | <a href="javascript:;" class="Bamyers99_ClassSuggester_createClaim" ' +
						'data-qid="' + v.qid + '">Create claim</a>';
					h += '</div>';

					childs = v.childs || {};
					$.each( childs, function( k, v ) {
						star = v.catcnt ? ' <span style="color: #00f">&starf;</span>' : '';
						var label = '<span title="' + v.desc + '">' + v.label + '</span>';
						h += '<div>&boxur;&thinsp;' + label + star +
							' <a target="_blank" href="https://tools.wmflabs.org/bambots/WikidataClasses.php?id=' +
							v.qid + '&lang=' + lang + '"><span style="font-size: 16pt" title="view in class browser">&telrec;</span></a>';
						h += ' | <a href="javascript:;" class="Bamyers99_ClassSuggester_createClaim" ' +
							'data-qid="' + v.qid + '">Create claim</a>';
						h += '</div>';
					} );
				} );

				h += '<br /><div><span style="color: #00f">&starf;</span> = used by related items</div>';
				h += '<div><span style="font-size: 16pt">&telrec;</span> = view in class browser</div>';
			}
		}

		h += '</div></div>';
		$( '#mw-content-text' ).append( h );

		$( 'a.Bamyers99_ClassSuggester_createClaim' ).click( function() {
			var valueqid = $(this).attr('data-qid');
			self.gc.wdCreateClaimEntityValue(self.qid, self.propInstanceof, valueqid, function( success, msg ) {
				msg = success ? 'Claim created. <a href="/wiki/' + self.qid + '">Reload page</a>' : msg;
				$( '#Bamyers99_ClassSuggester_msg' ).html( msg );

				var h = '<div>' + self.propInstanceof + ' : ' + valueqid + '</div>';
				$( $( 'div.wikibase-listview' ).get( 0 ) ).append( h );
			} );
			return false;
		} );

		var search = $( '#searchInput' );
		$( '#Bamyers99_ClassSuggester_dialog' ).dialog( {
			title : 'Class Suggester',
			width : 'auto',
			position : { my: 'left top', at: 'right top', of: $( '#claims' ) },
			open: function( event, ui ) {
				$('#Bamyers99_ClassSuggester_dialog a').css ( { color: '#0b0080' } );
				$('#Bamyers99_ClassSuggester_dialog').css ( { 'font-size': '12pt', 'font-family': 'Arial,Helvetica,sans-serif' } );
				search.focus();
			},
			close: function( event, ui ) {
				$( '#Bamyers99_ClassSuggester_dialog' ).remove();
			}
		} );
	}

};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.ClassSuggester.init() ;
} );
