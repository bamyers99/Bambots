/**
 Copyright 2024 Myers Enterprises II

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
 Allows JSON input of NRHP heritage designation info (P1435).

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 importScript("User:Bamyers99/NRHPhd.js");

 */

var Bamyers99 = Bamyers99 || {};

Bamyers99.NRHPhd = {
	
	NRHPEvents: [],
	until: (predFn) => {
		  const poll = (done) => (predFn() ? done() : setTimeout(() => poll(done), 250));
		  return new Promise(poll);
	},

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: 'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript', dataType: 'script', cache: true } ),
			mw.loader.using([ 'jquery.ui' ])
		).done( function() {
			
			self.gc = Bamyers99.GadgetCommon;
			mw.hook( 'wikibase.entityPage.entityLoaded' ).add( async function ( data ) {
				'use strict';
				
				if (! data.claims) return;
				var stmtID = '';
				var stmtClaim;

				$.each( data.claims['P1435'] || {}, function ( index, claim ) {
					if ( ! claim.mainsnak || ! claim.mainsnak.datavalue || ! claim.mainsnak.datavalue.value || ! claim.mainsnak.datavalue.value.id ) return true;

					if (claim.mainsnak.datavalue.value.id != 'Q19558910') return true;
					
					stmtID = '#' + $.escapeSelector(claim.id);
					stmtClaim = claim;

					return false;
				} );
				
				var missingHeritage = false;
				
				if (stmtID == '') {
					$.each( data.claims['P649'] || {}, function ( index, claim ) {
						stmtID = '#' + $.escapeSelector(claim.id);
						stmtClaim = {};

						return false;
					} );
					
					if (stmtID == '') return;
					
					missingHeritage = true;
				}
				
				await self.until(() => $(stmtID + ' .wikibase-edittoolbar-container').length > 0 );

		        const nrhpedit = $(stmtID + ' .wikibase-edittoolbar-container');
		        
		        if (!nrhpedit) return;
		        
		        const newElement = document.createElement('span');
		        newElement.innerHTML = '&nbsp;&nbsp;&nbsp;<a style="position:relative;top:-5px;"><span class="wb-icon">💻</span></a>';
		        nrhpedit.append(newElement);
		        
		        var qid = mw.config.get( 'wgPageName' ).toUpperCase();
		        
		        newElement.addEventListener('click', () => {
		          	self.displayDialog(stmtID, stmtClaim, qid, missingHeritage);
					return false;
		        } );
					
			} );
		} );
	},
	
	emitNRHP: function(stmtID, data) {
		if (data.section == 'qualifier') this.emitQualifier(stmtID, data);
		else this.emitReference(stmtID, data);
	},
	
	emitQualifier: function(stmtID, data) {
		this.emitAddButtonPress(stmtID, '.wikibase-statementview-qualifiers', '.wikibase-snaklistview');
		this.emitEditSubItem(stmtID, '.wikibase-statementview-qualifiers', '.wikibase-snaklistview', data);
	},
	
	emitReference: function(stmtID, data) {
		this.emitAddButtonPress(stmtID, '.wikibase-statementview-references-container', '.wikibase-referenceview');
		
		for (var offset in data.refs) {
			var row = data.refs[offset];
			
			// Press the reference add button
			if (offset > 0) {
				this.emitAddButtonPress(stmtID, '.wikibase-statementview-references-container .wikibase-referenceview', '.wikibase-snaklistview');
			}
			
			this.emitEditSubItem(stmtID, '.wikibase-statementview-references-container .wikibase-referenceview', '.wikibase-snaklistview', row);
		}		
	},
	
	emitAddButtonPress: function(stmtID, container, listitem) {
		var event = {'type': 'addButtonPress', 'stmtID': stmtID, 'container': container, 'listitem': listitem};
		
		this.NRHPEvents.push(event);
	},
	
	emitEditSubItem: function(stmtID, container, listitem, data) {
		// Set property
		this.emitWaitForFunction(() => {
			$(`${stmtID} ${container} ${listitem}:last-of-type .wikibase-snakview-property:last-of-type :first-child`).focus();
			return $(`${stmtID} ${container} ${listitem}:last-of-type .wikibase-snakview-property:last-of-type :first-child:focus`).length > 0;
		});
		this.emitSetValue(stmtID, container, listitem, 'entity', data.prop);
		this.emitSearch(stmtID, container, listitem);
		this.emitActivate(stmtID, container, listitem, 0);
		this.emitSelect(stmtID, container, listitem);
		this.emitWaitForFunction(() => {
			return $(`${stmtID} ${container} ${listitem}:last-of-type .valueview-value :first-child:focus`).length > 0;
		});
		this.emitCheckPropLabel(stmtID, container, listitem, data.propLabel);
		
		// Set value
		switch (data.type) {
			case 'date':
			case 'string':
				this.emitSetValue(stmtID, container, listitem, data.type, data.value);
				break;
				
			case 'entity':
				this.emitSetValue(stmtID, container, listitem, data.type, data.value);
				this.emitSearch(stmtID, container, listitem);
				//this.emitWaitForSelector(() => $('.ui-ooMenu:not(.wikibase-entitysearch-list)').length > 1);
				this.emitActivate(stmtID, container, listitem, 1);
				this.emitSelect(stmtID, container, listitem);
				break;
		}
	},
	
	emitSetValue(stmtID, container, listitem, type, value) {
		var event = {'type': 'setValue', 'stmtID': stmtID, 'container': container, 'listitem': listitem, 'valuetype': type, 'value': value};
		
		this.NRHPEvents.push(event);
	},
	
	emitCheckPropLabel(stmtID, container, listitem, label) {
		var event = {'type': 'checkPropLabel', 'stmtID': stmtID, 'container': container, 'listitem': listitem, 'label': label};
		
		this.NRHPEvents.push(event);
	},
	
	emitSearch(stmtID, container, listitem) {
		var event = {'type': 'search', 'stmtID': stmtID, 'container': container, 'listitem': listitem};
		
		this.NRHPEvents.push(event);
	},
	
	emitActivate(stmtID, container, listitem, occurence) {
		var event = {'type': 'activate', 'stmtID': stmtID, 'container': container, 'listitem': listitem, 'occurence': occurence};
		
		this.NRHPEvents.push(event);
	},
	
	emitSelect(stmtID, container, listitem) {
		var event = {'type': 'select', 'stmtID': stmtID, 'container': container, 'listitem': listitem};
		
		this.NRHPEvents.push(event);
	},
	
	emitWaitForSelector(selector) {
		var event = {'type': 'waitForSelector', 'selector': selector};
		
		this.NRHPEvents.push(event);
	},
	
	emitWaitForFunction(func) {
		var event = {'type': 'waitForFunction', 'function': func};
		
		this.NRHPEvents.push(event);
	},
	
	processEvent: async function(event) {
		var self = this;
				
		switch (event.type) {
			case 'addButtonPress':
				var countitem = event.listitem;
				var container = `${event.stmtID} ${event.container}`;
				
				container = $(container);
				var button = container.find('.wikibase-toolbar-button-add a').last();
				
				// Count how many list items we already have.
				var listcnt = container.find(countitem).length;
				++listcnt;
				
				button.click();
				
				await self.until( () => {
					console.log(container.find(countitem).length + ' =? ' + listcnt);
					return container.find(countitem).length == listcnt;
					 });
				break;
				
			case 'setValue':
				var container = $(`${event.stmtID} ${event.container}`);
				var listitem = container.find(event.listitem).last();
				var inputfield = (event.valuetype == 'entity') ? '.ui-suggester-input': '.valueview-input';
	            var fieldInput = listitem.find(inputfield).last();
	
	            fieldInput.val(event.value).trigger( "input" );
				break;
				
			case 'checkPropLabel':
				var container = $(`${event.stmtID} ${event.container}`);
				var listitem = container.find(event.listitem).last();
				var inputfield = '.wikibase-snakview-property .ui-suggester-input';
	            var fieldInput = listitem.find(inputfield).last();
	
	            if (fieldInput.val() != event.label) alert('Bad property name ' + event.label);
				break;
				
			case 'search':
				var container = $(`${event.stmtID} ${event.container}`);
				var listitem = container.find(event.listitem).last();
	            var fieldInput = listitem.find('.ui-suggester-input').last();
	            
	            await fieldInput.entityselector('search');
				break;
				
			case 'activate':
				var container = $(`${event.stmtID} ${event.container}`);
				var listitem = container.find(event.listitem).last();
	
                var propertySearchList = $('.ui-ooMenu:not(.wikibase-entitysearch-list):not([style*="display: none"])').last();
                var propertyTopResult = propertySearchList.children('.ui-ooMenu-item').eq(0);

                propertySearchList.ooMenu('activate', propertyTopResult);
				break;
				
			case 'select':
				var container = $(`${event.stmtID} ${event.container}`);
				var listitem = container.find(event.listitem).last();
                var propertySearchList = $('.ui-ooMenu:not(.wikibase-entitysearch-list):not([style*="display: none"])').last();

                propertySearchList.ooMenu('select');
				break;
				
			case 'waitForSelector':
				await self.until(() => $(event.selector).length > 0 );
				break;
				
			case 'waitForFunction':
				await self.until( event.function );
				break;
		}
	},
	
	/**
	 * Display the dialog
	 */
	displayDialog: function(stmtID, stmtClaim, qid, missingHeritage) {
		var self = this;
		var h = '<div id="Bamyers99_NRHPhd_dialog">';

		h += '<div id="Bamyers99_NRHPhd_form">';
		h += '<div id="Bamyers99_NRHPhd_msg">Enter JSON data</div>';

		h += '<textarea rows="10" cols="50" id="Bamyers99_NRHPhd_props"></textarea>';
		
		h += '<br /><br />';
		
		h += '<input type="button" id="Bamyers99_NRHPhd_save" value="Save" />';
		h += '<input type="button" id="Bamyers99_NRHPhd_cancel" value="Cancel" />';

		h += '</div></div>';
		$( '#mw-content-text' ).append( h );
		
		$( '#Bamyers99_NRHPhd_props' ).val('');

		$( '#Bamyers99_NRHPhd_save' ).click( async function() {
			var data = JSON.parse($("#Bamyers99_NRHPhd_props").val());
			$( '#Bamyers99_NRHPhd_dialog' ).dialog( "close" );
			
			// Verify qids match
			
//			if (qid != data.qid) {
//				alert(`qid mismatch ${qid} != ${data.qid}`);
//				return;
//			}
			
			// Click edit link
			var button;
			
			if (missingHeritage) {
				button = true;
				stmtID = '#new';
			} else {
				button = $(stmtID + ' .wikibase-toolbar-button-edit a').last();
			}
			
			if (button) {
				if (! missingHeritage) button.click();
				
				await self.until(() => $(stmtID + ' .wikibase-statementview-mainsnak-container :focus').length > 0);
				 {		
					// Calc which qualifiers need to be added
					var props = {'P580': 'date_published', 'P1810': 'title', 'P2868': 'resource_type', 'P1013': 'criteria', 'P361': 'multiple_listing'};
					var propsTypes = {'P580': 'date', 'P1810': 'string', 'P2868': 'entity', 'P1013': 'entity', 'P361': 'entity'};
					var propsLabels = {'P580': 'start time', 'P1810': 'subject named as', 'P2868': 'subject has role',
						'P1013': 'criterion used', 'P361': 'part of', 'P248': 'stated in', 'P649': 'NRHP reference number',
						'P1225': 'U.S. National Archives Identifier'};
					var queue = [];

					for (const prop in props) {
						var label = props[prop];
						
						if ( ! (prop in (stmtClaim.qualifiers || {})) && data[label] != '') {
							if (label == 'criteria') {
								var criterias = data[label].split(',');
								
								for (criteria of criterias) {
									var record = {'section': 'qualifier', 'prop': prop, 'value': criteria, 'type': propsTypes[prop],
										'propLabel': propsLabels[prop]};
									queue.push(record);				
								}
							} else {
								var record = {'section': 'qualifier', 'prop': prop, 'value': data[label], 'type': propsTypes[prop],
									'propLabel': propsLabels[prop]};
								queue.push(record);
							}
						}
					}
					
					// Add references
					var record = {'section': 'reference', 'refs': [{'prop': 'P248', 'value': 'Q3719', 'type': 'entity',
								'propLabel': propsLabels['P248']},
							{'prop': 'P649', 'value': data.nrhpid, 'type': 'string', 'propLabel': propsLabels['P649']}]};
					queue.push(record);
					
					if (data.archiveid != '') {
						record = {'section': 'reference', 'refs': [{'prop': 'P248', 'value': 'Q113508718', 'type': 'entity',
								'propLabel': propsLabels['P248']},
							{'prop': 'P1225', 'value': data.archiveid, 'type': 'string', 'propLabel': propsLabels['P1225']}]};
						queue.push(record);
					}
					
					// Generate the events
					self.NRHPEvents = [];
					
					while (queue.length > 0) {
						self.emitNRHP(stmtID, queue.shift());
					}					
					
					// Dispatch the events
					var p = new Promise((resolve) => {resolve();});
					
					while (self.NRHPEvents.length > 0) {
					   var boundfunc = self.processEvent.bind(self, self.NRHPEvents.shift())
					   p = p.then(boundfunc);
					}
				} ;
			}

			return false;
		} );
		
		$( '#Bamyers99_NRHPhd_cancel' ).click( function() {
			$( '#Bamyers99_NRHPhd_dialog' ).dialog( "close" );
			
			return false;
		} );

		$( '#Bamyers99_NRHPhd_dialog' ).dialog( {
			title : 'NRHP Heritage Designation',
			width : 'auto',
			//position : { my: 'center top', at: 'center top' },
			open: function( event, ui ) {
				$('#Bamyers99_NRHPhd_dialog').css ( { 'font-size': '12pt', 'font-family': 'Arial,Helvetica,sans-serif' } );
			},
			close: function( event, ui ) {
				$( '#Bamyers99_NRHPhd_dialog' ).remove();
			}
		} );
	}
	
};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.NRHPhd.init() ;
} );
