/**
 Copyright 2017 Myers Enterprises II

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
 Convert a unit to another unit.
 Supports currency, metric, United States units

 Usage
 =====
 Add the following line(s) to your [[Special:Mypage/common.js]] page

 Bamyers99_UnitsConverter_currency = 'EUR'; // AUD,BRL,CAD,CHF,CNY,DKK,EUR,GBP,INR,ISK,JPY,MXN,NOK,NZD,PLN,RUB,SEK,TRY,USD,ZAR
 importScript("User:Bamyers99/UnitsConverter.js");

 */

var Bamyers99 = Bamyers99 || {};

if (typeof Bamyers99_UnitsConverter_testmode === 'undefined') Bamyers99_UnitsConverter_testmode = false;
if (typeof Bamyers99_UnitsConverter_currency === 'undefined') Bamyers99_UnitsConverter_currency = 'EUR';

Bamyers99.UnitsConverter = {
	commonjs: Bamyers99_UnitsConverter_testmode ? 'https://tools.wmflabs.org/bambots/GadgetCommon.js' :
		'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript',
	// ?s wdt:P2876 wd:Q8142,Q3647172; ?s wdt:P31 wd:Q21077852
	propQuantities: {'P2121':'cur', 'P2769':'cur', 'P2130':'cur', 'P2630':'cur', 'P2555':'cur', 'P3087':'cur', 'P2836':'cur',
		'P2220':'cur', 'P2835':'cur', 'P2295':'cur', 'P2218':'cur', 'P2284': 'cur', 'P2133':'cur',
		'P2137':'cur', 'P2139':'cur', 'P2067':'mass', 'P2050':'dim', 'P2046':'area', 'P2102':'temp', 'P2793':'dim',
		'P2217':'speed', 'P2386':'dim', 'P2148':'dim', 'P2262':'dim', 'P2044':'dim', 'P3157':'dim', 'P2128':'temp',
		'P2923':'dim', 'P2151':'dim', 'P2565':'mass', 'P2048':'dim', 'P2043':'dim', 'P2254':'dim', 'P3252':'temp',
		'P2101':'temp', 'P3251':'temp', 'P3253':'temp', 'P2547':'dim', 'P2120':'dim', 'P2052':'speed', 'P2113':'temp',
		'P2430':'dim', 'P2076':'temp', 'P2073':'dim', 'P2053':'area', 'P3039':'dim', 'P2049':'dim', 'P2112':'area'},
	unitConverts: {'mass': {
			'Q41803': {'mult':0.0353, 'unit': 'Q48013'}, // gram -> ounce
			'Q48013': {'mult':28.35, 'unit': 'Q41803'}, // ounce -> gram
			'Q11570': {'mult':2.205, 'unit': 'Q100995'}, // kilogram -> pound
			'Q100995': {'mult':0.454, 'unit': 'Q11570'} // pound -> kilogram
		}, 'dim': {
			'Q174789': {'mult':0.0394, 'unit': 'Q218593'}, // millimeter -> inch
			'Q174728': {'mult':0.394, 'unit': 'Q218593'}, // centimetre -> inch
			'Q218593': {'mult':2.54, 'unit': 'Q174728'}, // inch -> centimetre
			'Q11573': {'mult':3.28, 'unit': 'Q3710'}, // metre -> foot
			'Q3710': {'mult':0.305, 'unit': 'Q11573'}, // foot -> metre
			'Q482798': {'mult':0.914, 'unit': 'Q11573'}, // yard -> metre
			'Q828224': {'mult':0.621, 'unit': 'Q253276'}, // kilometre -> mile
			'Q253276': {'mult':1.609, 'unit': 'Q828224'} // mile -> kilometre
		}, 'area': {
			'Q2489298': {'mult':0.155, 'unit': 'Q1063786'}, // sq centimetre -> sq inch
			'Q1063786': {'mult':6.452, 'unit': 'Q2489298'}, // sq inch -> sq centimetre
			'Q25343': {'mult':10.764, 'unit': 'Q857027'}, // sq metre -> sq foot
			'Q857027': {'mult':0.0929, 'unit': 'Q25343'}, // sq foot -> sq metre
			'Q1550511': {'mult':0.836, 'unit': 'Q11573'}, // sq yard -> sq metre
			'Q35852': {'mult':2.471, 'unit': 'Q81292'}, // hectare -> acre
			'Q81292': {'mult':0.405, 'unit': 'Q35852'}, // acre -> hectare
			'Q712226': {'mult':0.386, 'unit': 'Q232291'}, // sq kilometre -> sq mile
			'Q232291': {'mult':2.59, 'unit': 'Q712226'} // sq mile -> sq kilometre
		}, 'temp': {
			'Q25267': {'mult':1.8, 'add':32, 'unit': 'Q42289'}, // C -> F
			'Q42289': {'minus': 32, 'mult':0.556, 'unit': 'Q25267'} // F -> C
		}, 'speed': {
			'Q182429': {'mult':3.281, 'unit': 'Q748716'}, // meter / second -> foot / second
			'Q748716': {'mult':0.305, 'unit': 'Q182429'}, // foot / second -> meter / second
			'Q180154': {'mult':0.621, 'unit': 'Q211256'}, // kilometer / hour -> mile / hour
			'Q211256': {'mult':1.609, 'unit': 'Q180154'} // mile / hour -> kilometer / hour
		}
	},
	propPointInTime: 'P585',
	defaultCurISO: 'EUR',
	langPriority: ['en','de','es','fr','it','pt'],

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: self.commonjs, dataType: 'script', cache: true } )
		).done( function() {
			self.gc = Bamyers99.GadgetCommon;

			if ( ! mw.config.exists( 'wbEntity' )) {
				return;
			}

			var pointInTimeYear = null;
			var propHits = [];

			var data = JSON.parse( mw.config.get( 'wbEntity' ) );
			$.each( data.claims || {}, function ( prop, claims ) {
				if ( prop === self.propPointInTime ) {
					var dv = claims[0].mainsnak.datavalue;
					pointInTimeYear = self.parseDateToYear( dv );
					return true;
				}

				$.each( claims, function ( index, claim ) {
					var pointInTimeYear = null;
					var qualOffset = 0;
					var qualifierHits = [];

					$.each( claim.qualifiers || {}, function ( prop, qualifiers) {

						$.each( qualifiers, function ( index, qualifier ) {
							if ( prop === self.propPointInTime ) {
								var dv = qualifiers[0].mainsnak.datavalue;
								pointInTimeYear = self.parseDateToYear( dv );
								++qualOffset;
								return true;
							}

							if ( ! self.propQuantities.hasOwnProperty( prop ) ) {
								++qualOffset;
								return true;
							}

							var quantity = self.parseQuantity( qualifier.datavalue );
							if ( quantity === null ) {
								++qualOffset;
								return true;
							}

							qualifierHits.push( {'type': self.propQuantities[prop], 'cid': claim.id,
								'qoffset': qualOffset, 'unit': quantity.unit, 'amount': quantity.amount} );
							++qualOffset;
						});
					});

					for ( var i = 0, al = qualifierHits.length; i < al; ++i ) {
						if ( pointInTimeYear ) qualifierHits[i].pity = pointInTimeYear;
						propHits.push( qualifierHits[i] );
					}

					if ( ! self.propQuantities.hasOwnProperty( prop ) ) {
						return true;
					}

					var quantity = self.parseQuantity( claim.datavalue );
					if ( quantity === null ) {
						return true;
					}

					propHits.push( {'type': self.propQuantities[prop], 'cid': claim.id,
						'unit': quantity.unit, 'amount': quantity.amount} );
				});
			});

			var currencyFound = false;

			for ( var i = 0, al = propHits.length; i < al; ++i ) {
				if (pointInTimeYear && ! propHits[i].hasOwnProperty( 'pity' ) ) propHits[i].pity = pointInTimeYear;
				if (propHits[i].type === 'cur') currencyFound = true;
			}

			if ( currencyFound ) {
				$.getJSON( '/wiki/User:Bamyers99/currency.json', function( data ) {
					self.displayUnits( propHits, data );
				});
			} else {
				self.displayUnits( propHits, null );
			}
		});
	},

	/**
	 * Display converted units
	 *
	 * @param propHits
	 * @param currencies
	 */
	displayUnits: function ( propHits, currencies ) {
		var self = this;

		$.each( propHits, function( idx, hit ) {

		});
	},

	/**
	 * Parse a quantity value
	 *
	 * @param dv
	 * @return { 'unit': unit, 'amount': amount } or null
	 */
	parseQuantity: function ( dv ) {
		if ( dv.type !== 'quantity' ) return null;

		var unit = dv.value.unit;
		if ( unit === '1' ) return null;

		var slashPos = unit.lastIndexOf('/');
		if ( slashPos === -1 ) return null;

		unit = unit.substr( slashPos + 1 );

		var amount = dv.value.amount;
		if ( amount[0] === '+' ) amount = amount.substr(1);

		return { 'unit': unit, 'amount': amount };
	},

	/**
	 * Parse a date and return the year
	 *
	 * @param dv
	 * @return year or null
	 */
	parseDateToYear: function( dv ) {
		if ( dv === undefined ||
			dv.type !== 'time' ||
			dv.value.after !== 0 ||
			dv.value.before !== 0 ||
			dv.value.precision < 9) return null;

		var datetime = dv.value.time;

		if (datetime[0] === '+') datetime = datetime.substr(1);
		else if (datetime[0] === '-') return null; // BCE

		var parts = datetime.split('-');
		while (parts[0].length && parts[0][0] === '0') parts[0] = parts[0].substr(1);
		return parts[0];
	}

};

$( function() {
	if (mw.config.get( 'wgNamespaceNumber' ) !== 0) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.UnitsConverter.init() ;
} );
