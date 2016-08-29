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

        if (! 'continue' in opts) {
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
	}
};
