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
 */

var Bamyers99 = Bamyers99 || {};

Bamyers99.GadgetCommon = Bamyers99.GadgetCommon || {

	/**
	 * Execute a MediaWiki API query
	 *
	 * if 'continue' has been returned, pass 'continue' back in as an opt to get more results
	 *
	 * @param object opts MediaWiki API parameters
	 * 		lang : optional, default = 'en', use 'wikidata' for wikidata
	 * 		action : optional, default = 'query'
	 * @param function callback
	 */
	mwApiQuery: function( opts, callback ) {
		opts = $.extend( { action: 'query' }, opts);
		var lang = opts.lang || 'en';
		delete opts.lang;
		opts.format = 'json';

        if (! ('continue' in opts)) {
        	opts['continue'] = '';
        } else if ( typeof opts['continue'] === 'object' ){
        	var continueval = opts['continue'];
        	delete opts['continue'];
        	opts = $.extend( opts, continueval );
        }

        var protocalDomain = '',
        	jsonp = '';
        if ( lang !== 'wikidata' ) {
        	protocalDomain = 'https://' + lang + '.wikipedia.org';
        	jsonp = 'callback=?';
        }

		$.getJSON( protocalDomain + '/w/api.php?' + jsonp, opts, callback );
	},

	/**
	 * Create a claim with an entity type value
	 *
	 * @param entityId
	 * @param propId
	 * @param propValueEntityId
	 * @param callback(bool success, string errormsg) (optional)
	 */
	wdCreateClaimEntityValue: function( entityId, propId, propValueEntityId, callback ) {
		var self = this;

		var opts = {
			lang: 'wikidata',
			action: 'wbgetentities',
			ids: entityId,
			props: 'claims'
		};

		// See if it already has the property
		self.mwApiQuery( opts, function( result ) {
			$.each( result.entities, function( id, itemdata ) {
				if ( itemdata.claims &&  itemdata.claims[propId] ) {
					if ( callback ) callback( false, 'Already has property' );
					return;
				}

				var opts = {
					lang: 'wikidata',
					prop: 'info',
					intoken : 'edit',
					titles : entityId
				};

				// Get an edit token and lastrevid
				self.mwApiQuery( opts, function( data ) {
					var token , lastrevid ;
					$.each ( (data.query.pages||[]) , function ( k , v ) {
						token = v.edittoken ;
						lastrevid = v.lastrevid ;
					} ) ;

					if ( token === undefined ) {
						if ( callback ) callback( false, 'Editing not allowed' );
						return ;
					}

					var opts = {
						lang: 'wikidata',
						action: 'wbcreateclaim',
						entity : entityId,
						snaktype : 'value',
						property : propId,
						value : '{"entity-type":"item","numeric-id":' + propValueEntityId.substring(1) + '}',
						token : token,
						baserevid : lastrevid
					};

					// Create the claim
					self.mwApiQuery( opts, function( data ) {
						if ( callback ) callback( true, '' );
					} );
				} );
			} );
		} );
	}
};
