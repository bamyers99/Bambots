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
	mwApiQuery: function( opts, callback, userAgent = 'Gadget/1.0 (User:Bamyers99)' ) {
		opts = $.extend( { action: 'query' }, opts);
		var lang = opts.lang || 'en';
		delete opts.lang;
		opts.format = 'json';

        if (! ('continue' in opts)) {
        	if ( opts.action === 'query' ) opts['continue'] = '';
        } else if ( typeof opts['continue'] === 'object' ){
        	var continueval = opts['continue'];
        	delete opts['continue'];
        	opts = $.extend( opts, continueval );
        }

        var protocalDomain = '',
        	origin = '';
		
		if ( lang !== 'wikidata' ) {
			if (lang == 'commons') protocalDomain = 'https://commons.wikimedia.org';
        	else protocalDomain = 'https://' + lang + '.wikipedia.org';
        	origin = 'origin=*';
        }

        $.ajax({
        	  type: 'POST',
			  headers: { 'Api-User-Agent': userAgent },
        	  dataType: "json",
        	  url: protocalDomain + '/w/api.php?' + origin,
        	  data: opts,
        	  success: callback
        	});
	},

	/**
	 * Create a claim with an entity type value
	 *
	 * @param entityId
	 * @param propId
	 * @param propValueEntityId
	 * @param userAgent
	 * @param callback(bool success, string errormsg) (optional)
	 */
	wdCreateClaimEntityValue: function( entityId, propId, propValueEntityId, userAgent, callback) {
		var self = this;

		var opts = {
			lang: 'wikidata',
			action: 'wbgetentities',
			ids: entityId,
			props: 'claims'
		};

		// See if it already has the property
		self.mwApiQuery( opts, function( result ) {
			if ( result.error ) {
				if ( callback ) callback( false, 'Error: "' + result.error.code + '": ' + result.error.info );
				return;
			}

			$.each( result.entities, function( id, itemdata ) {
				var exiting = false;

				if ( itemdata.claims && itemdata.claims[propId] ) {
					$.each( itemdata.claims[propId], function( k, propdata ) {

						if ( propdata.mainsnak && propdata.mainsnak.datavalue && propdata.mainsnak.datavalue.value &&
							propdata.mainsnak.datavalue.value['numeric-id'] &&
							'Q' + propdata.mainsnak.datavalue.value['numeric-id'] === propValueEntityId ) {
							if ( callback ) callback( false, 'Already has claim' );
							exiting = true;
							return false;
						}
					});
				}

				if ( exiting ) return;

				var opts = {
					lang: 'wikidata',
					prop: 'info',
					titles : entityId
				};

				// Get lastrevid
				self.mwApiQuery( opts, function( data ) {
					var lastrevid;

					if ( data.error ) {
						if ( callback ) callback( false, 'Error: "' + data.error.code + '": ' + data.error.info );
						return;
					}

					$.each ( ( data.query.pages || []) , function ( k , v ) {
						lastrevid = v.lastrevid;
					} );

					// Get the csrf token

					var opts = {
						lang: 'wikidata',
						meta: 'tokens'
					};

					self.mwApiQuery( opts, function( data ) {
						var csrftoken;
						if ( data.query && data.query.tokens && data.query.tokens.csrftoken ) {
							csrftoken = data.query.tokens.csrftoken ;
						} else {
							if ( callback ) callback( false, 'Editing not allowed' );
							return;
						}

						var opts = {
							lang: 'wikidata',
							action: 'wbcreateclaim',
							entity : entityId,
							snaktype : 'value',
							property : propId,
							value : '{"entity-type":"item","numeric-id":' + propValueEntityId.substring(1) + '}',
							token : csrftoken,
							baserevid : lastrevid
						};

						// Create the claim
						self.mwApiQuery( opts, function( data ) {
							if ( data.success ) {
								if ( callback ) callback( true, '' );
							} else {
								if ( callback ) callback( false, 'Error: "' + data.error.code + '": ' + data.error.info );
							}
						}, userAgent );
					}, userAgent );
				}, userAgent );
			} );
		}, userAgent );
	},

	/**
	 * Set a claim
	 *
	 * @param entityId
	 * @param claim json formatted claim
	 * @param userAgent
	 * @param callback(bool success, string errormsg) (optional)
	 */
	wdSetClaim: function( entityId, claim, userAgent, callback ) {
		var self = this;

		var opts = {
			lang: 'wikidata',
			prop: 'info',
			titles : entityId
		};

		// Get lastrevid
		self.mwApiQuery( opts, function( data ) {
			var lastrevid;

			if ( data.error ) {
				if ( callback ) callback( false, 'Error: "' + data.error.code + '": ' + data.error.info );
				return;
			}

			$.each ( ( data.query.pages || []) , function ( k , v ) {
				lastrevid = v.lastrevid;
			} );

			// Get the csrf token

			var opts = {
				lang: 'wikidata',
				meta: 'tokens'
			};

			self.mwApiQuery( opts, function( data ) {
				var csrftoken;
				if ( data.query && data.query.tokens && data.query.tokens.csrftoken ) {
					csrftoken = data.query.tokens.csrftoken ;
				} else {
					if ( callback ) callback( false, 'Editing not allowed' );
					return;
				}

				var opts = {
					lang: 'wikidata',
					action: 'wbsetclaim',
					claim : claim,
					token : csrftoken,
					baserevid : lastrevid
				};

				// Set the claim
				self.mwApiQuery( opts, function( data ) {
					if ( data.success ) {
						if ( callback ) callback( true, '' );
					} else {
						if ( callback ) callback( false, 'Error: "' + data.error.code + '": ' + data.error.info );
					}
				}, userAgent );
			}, userAgent );
		}, userAgent );
	},

	/**
	 * Get a claim
	 *
	 * @param claimId
	 * @param userAgent
	 * @param callback(bool success, string errormsg or object json claim)
	 */
	wdGetClaim: function( claimId, userAgent, callback) {
		var self = this;

		var opts = {
			lang: 'wikidata',
			action: 'wbgetclaims',
			claim: claimId
		};

		self.mwApiQuery( opts, function( result ) {
			if ( result.error ) {
				callback( false, 'Error: "' + result.error.code + '": ' + result.error.info );
				return;
			}

			$.each( result.claims, function( propid, claims ) {
				callback( true, claims[0]);
			} );
		}, userAgent );
	},

	/**
	 * html encode a string
	 *
	 * @param str
	 * @returns
	 */
	htmlEncode: function ( str ) {
		return str
		.replace( /&/g, '&amp;' )
		.replace( /"/g, '&quot;' )
		.replace( /'/g, '&#39;' )
		.replace( /`/g, '&#39;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' );
 	},
 	
 	/**
	  * wait for an element to be added to the dom
	  * 
	  * @param selector
	  * @param func
	  */
 	waitForSelector: function ( selector, func ) {
		var observer = new MutationObserver(function(mutations) {
		    if ($(selector).length) {
				func();
		        observer.disconnect(); 
		    }
		});
		
		observer.observe(document.body, {
		    childList: true,
		    subtree: true
		});
	}
 	
};
