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
	commonjs: Bamyers99_UnitsConverter_testmode ? 'https://bambots.brucemyers.com/GadgetCommon.js' :
		'https://www.wikidata.org/w/index.php?title=User:Bamyers99/GadgetCommon.js&action=raw&ctype=text/javascript',
	unitCurrencies: ['Q1104069','Q122922','Q123213','Q131473','Q132643','Q1472704','Q172872','Q173117','Q181907',
	                 'Q25224','Q25344','Q25417','Q259502','Q39099','Q41044','Q4730','Q4916','Q4917','Q80524','Q8146'],
	unitConverts: {
		// mass
		'Q41803': [{'mult':0.035274, 'unit': 'Q48013'}], // gram -> ounce
		'Q48013': [{'mult':28.34952, 'unit': 'Q41803'}], // ounce -> gram
		'Q11570': [{'mult':2.204622, 'unit': 'Q100995'}], // kilogram -> pound
		'Q100995': [{'mult':0.453592, 'unit': 'Q11570'}], // pound -> kilogram
		'Q469356': [{'mult':0.892857, 'unit': 'Q667419'}, // short ton -> long ton
		           {'mult':0.90718474, 'unit': 'Q191118'}], // short ton -> tonne
		'Q667419': [{'mult':1.12, 'unit': 'Q469356'}, // long ton -> short ton
		           {'mult':1.0160469088, 'unit': 'Q191118'}], // long ton -> tonne
		'Q191118': [{'mult':1.102311311, 'unit': 'Q469356'}, // tonne -> short ton
		           {'mult':0.9842065276, 'unit': 'Q667419'}], // tonne -> long ton
		// dimensions
		'Q174789': [{'mult':0.03937, 'unit': 'Q218593'}], // millimeter -> inch
		'Q174728': [{'mult':0.3937, 'unit': 'Q218593'}], // centimetre -> inch
		'Q218593': [{'mult':2.54, 'unit': 'Q174728'}], // inch -> centimetre
		'Q11573': [{'mult':3.28084, 'unit': 'Q3710'}], // metre -> foot
		'Q3710': [{'mult':0.3048, 'unit': 'Q11573'}], // foot -> metre
		'Q482798': [{'mult':0.9144, 'unit': 'Q11573'}], // yard -> metre
		'Q828224': [{'mult':0.621371, 'unit': 'Q253276'}], // kilometre -> mile
		'Q253276': [{'mult':1.609344, 'unit': 'Q828224'}], // mile -> kilometre
		// area
		'Q2489298': [{'mult':0.155, 'unit': 'Q1063786'}], // sq centimetre -> sq inch
		'Q1063786': [{'mult':6.4516, 'unit': 'Q2489298'}], // sq inch -> sq centimetre
		'Q25343': [{'mult':10.7639, 'unit': 'Q857027'}], // sq metre -> sq foot
		'Q857027': [{'mult':0.092903, 'unit': 'Q25343'}], // sq foot -> sq metre
		'Q1550511': [{'mult':0.836127, 'unit': 'Q11573'}], // sq yard -> sq metre
		'Q35852': [{'mult':2.471054, 'unit': 'Q81292'}], // hectare -> acre
		'Q81292': [{'mult':0.404686, 'unit': 'Q35852'}], // acre -> hectare
		'Q712226': [{'mult':0.386102, 'unit': 'Q232291'}], // sq kilometre -> sq mile
		'Q232291': [{'mult':2.59, 'unit': 'Q712226'}], // sq mile -> sq kilometre
		// temperature
		'Q25267': [{'mult':1.8, 'addafter':32, 'unit': 'Q42289'}], // C -> F
		'Q42289': [{'subbefore': 32, 'mult':0.556, 'unit': 'Q25267'}], // F -> C
		'Q11579': [{'mult':1, 'subafter': 273.15, 'unit': 'Q25267'}, // K -> C
		           {'mult':1.8, 'subafter': 459.67, 'unit': 'Q42289'}], // K -> F
		// speed
		'Q182429': [{'mult':3.28084, 'unit': 'Q748716'}], // meter / second -> foot / second
		'Q748716': [{'mult':0.3048, 'unit': 'Q182429'}], // foot / second -> meter / second
		'Q180154': [{'mult':0.621371, 'unit': 'Q211256'}], // kilometer / hour -> mile / hour
		'Q211256': [{'mult':1.609344, 'unit': 'Q180154'}], // mile / hour -> kilometer / hour
		'Q128822': [{'mult':1.15078, 'unit': 'Q211256'}, // knot -> mile / hour
		           {'mult':1.852, 'unit': 'Q180154'}], // knot -> kilometer / hour
	},
	propPointInTime: 'P585',
	defaultCurISO: 'EUR',

	/**
	 * Init
	 */
	init: function() {
		var self = this ;

		$.when(
			$.ajax( { url: self.commonjs, dataType: 'script', cache: true } ),
			mw.loader.using( 'mediawiki.language' )
		).done( function() {
			self.gc = Bamyers99.GadgetCommon;

			mw.hook( 'wikibase.entityPage.entityLoaded' ).add( function ( data ) {
				'use strict';

				var pointInTimeYear = null;
				var propHits = [];

				$.each( data.claims || {}, function ( prop, claims ) {

					$.each( claims, function ( index, claim ) {
						var qpointInTimeYear = null;
						var qualOffset = 0;
						var qualifierHits = [];

						if ( ! claim.mainsnak || ! claim.mainsnak.datavalue ) return true;

						var dv = claim.mainsnak.datavalue;

						if ( prop === self.propPointInTime ) {
							pointInTimeYear = self.parseDateToYear( dv );
							if ( pointInTimeYear ) return false;
							return true;
						}

						var claimQualifiers = {};

						if ( claim['qualifiers-order'] && claim.qualifiers ) {
							$.each( claim['qualifiers-order'], function ( i, prop ){
								claimQualifiers[prop] = claim.qualifiers[prop];
							});
						} else if ( claim.qualifiers ) {
							claimQualifiers = claim.qualifiers;
						}

						$.each( claimQualifiers, function ( prop, qualifiers) {

							$.each( qualifiers, function ( index, qualifier ) {
								if ( ! qualifier.datavalue ) {
									++qualOffset;
									return true;
								}

								if ( prop === self.propPointInTime ) {
									qpointInTimeYear = self.parseDateToYear( qualifier.datavalue );
									++qualOffset;
									if ( qpointInTimeYear ) return false;
									return true;
								}

								if ( qualifier.datavalue.type !== 'quantity' ) {
									++qualOffset;
									return true;
								}

								var quantity = self.parseQuantity( qualifier.datavalue );
								if ( quantity === null || ( ! self.unitConverts.hasOwnProperty( quantity.unit ) &&
										self.unitCurrencies.indexOf( quantity.unit ) === -1 )) {
									++qualOffset;
									return true;
								}

								qualifierHits.push( {'cid': claim.id, 'qoffset': qualOffset,
									'unit': quantity.unit, 'amount': quantity.amount} );
								++qualOffset;
							});
						});

						for ( var i = 0, al = qualifierHits.length; i < al; ++i ) {
							if ( qpointInTimeYear ) qualifierHits[i].pity = qpointInTimeYear;
							propHits.push( qualifierHits[i] );
						}

						if ( dv.type !== 'quantity' ) {
							return true;
						}

						var quantity = self.parseQuantity( dv );
						if ( quantity === null || ( ! self.unitConverts.hasOwnProperty( quantity.unit ) &&
								self.unitCurrencies.indexOf( quantity.unit ) === -1 )) {
							return true;
						}

						propHits.push( {'cid': claim.id, 'unit': quantity.unit,
							'amount': quantity.amount, 'pity': qpointInTimeYear} );
					});
				});

				var currencyFound = false;

				for ( var i = 0, al = propHits.length; i < al; ++i ) {
					if (pointInTimeYear && ! propHits[i].hasOwnProperty( 'pity' ) ) propHits[i].pity = pointInTimeYear;
					if ( ! currencyFound && self.unitCurrencies.indexOf( propHits[i].unit ) !== -1 ) currencyFound = true;
				}

				if ( currencyFound ) {
					$.getJSON( '/w/index.php?title=User:Bamyers99/currency.json&action=raw&ctype=application/json', function( data ) {
						self.displayUnits( propHits, data );
					});
				} else {
					self.displayUnits( propHits, null );
				}
			});
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
		var toUnits = {};
		var toCurUnit = null;

		// Get the 'to unit' language labels
		if ( currencies ) {
			if ( ! currencies.ISOs.hasOwnProperty( Bamyers99_UnitsConverter_currency ) )
				Bamyers99_UnitsConverter_currency = self.defaultCurISO;
			toCurUnit = currencies.ISOs[Bamyers99_UnitsConverter_currency];
			toUnits[toCurUnit] = '';
		}

		for ( var i = 0, al = propHits.length; i < al; ++i ) {
			if ( self.unitConverts.hasOwnProperty( propHits[i].unit ) ) {
				for ( var x = 0, cl = self.unitConverts[propHits[i].unit].length; x < cl; ++x )
					toUnits[self.unitConverts[propHits[i].unit][x].unit] = '';
			}
		}

		var opts = {
			action: 'wbgetentities',
			props: 'labels',
			ids: $.map( toUnits, function( v, k ) {
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
						if ( label ) toUnits[id] = label;
					}
				});
			}

			$.each( propHits, function( i, hit ) {
				var $claimview = $( '.wikibase-statementview' )
					.filter( function () {
						return $( this ).hasClass( 'wikibase-statement-' + hit.cid );
					});

				if ( hit.hasOwnProperty( 'qoffset' ) ) {
					$claimview = $claimview.find( '.wikibase-statementview-qualifiers .wikibase-snakview' ).eq( hit.qoffset );
				} else {
					$claimview = $claimview.find( '.wikibase-statementview-mainsnak' );
				}

				var h = '';
				var toLabel, val;

				if ( self.unitConverts.hasOwnProperty( hit.unit ) ) {
					$.each( self.unitConverts[hit.unit], function ( i, convert) {
						toLabel = toUnits[convert.unit];
						val = hit.amount;
						if ( convert.subbefore ) val -= convert.subbefore;
						val *= convert.mult;
						if ( convert.addafter ) val += convert.addafter;
						if ( convert.subafter ) val -= convert.subafter;

						if ( val < 5 ) val = val.toFixed(1);
						else val = Math.round(val);

						val = mw.language.convertNumber( val );

						h += '<br />(' + val + ' <span class="wb-unit">' + self.gc.htmlEncode( toLabel ) + '</span>)';
					})
				} else {
					var fromCurUnit = hit.unit;
					toLabel = toUnits[toCurUnit];
					var mults = [0,0];
					var fromYear, toYear;
					var currentYear = '' + currencies.current_year;

					if ( hit.pity ) {
						fromYear = hit.pity;
						if ( currencies[fromCurUnit].cpis[fromYear] ) toYear = currentYear;
						else toYear = fromYear;
					} else {
						fromYear = toYear = currentYear;
					}

					if ( fromCurUnit === toCurUnit && fromYear === toYear ) return true;

					if ( ! currencies[fromCurUnit].rates[fromYear] ||
						! currencies[toCurUnit].rates[toYear] ) return true;

					$.each( [fromYear, toYear], function( x, year ) {
						var multToCheck = ( x === 0 ) ? currencies[fromCurUnit].multipliers : currencies[toCurUnit].multipliers;
						year = parseInt(year, 10);

						$.each( multToCheck , function( y, multRange ) {
							if ( year >= multRange.start && year <= multRange.end ) {
								mults[x] = multRange.mult;
								return false;
							}
						});
					});

					if (mults[0] === 0 || mults[1] === 0 ) return true;

					var startCPI = currencies[fromCurUnit].cpis[fromYear];
					var endCPI = currencies[fromCurUnit].cpis[toYear];
					var fromRate = currencies[fromCurUnit].rates[fromYear];
					var toRate = currencies[toCurUnit].rates[toYear];
					
					// Calculate from year converted amount
					if (fromCurUnit != toCurUnit && currencies[toCurUnit].rates[fromYear]) {
						val = hit.amount / fromRate * currencies[toCurUnit].rates[fromYear];
						val = Math.round(val);
						val = mw.language.commafy( val, '#,##0' );
						
						h += '<br />(' + val + ' <span class="wb-unit">' + self.gc.htmlEncode( toLabel ) + '</span>';
						if ( hit.pity ) h += ' approximate (' + fromYear + ')';
						h += ')';
					}

					// Inflate 'from currency' amount
					if (fromCurUnit == toCurUnit) {
					    val = hit.amount / mults[0] * endCPI / startCPI * mults[1];
					} else {
					    val = hit.amount / mults[0] / fromRate * endCPI / startCPI * toRate * mults[1];
					}
					
					if (! isNaN(val)) {
					    val = Math.round(val);
					    val = mw.language.commafy( val, '#,##0' );
					
					    h += '<br />(' + val + ' <span class="wb-unit">' + self.gc.htmlEncode( toLabel ) + '</span>';
					    if ( hit.pity ) h += ' approximate (' + toYear + ')';
					    h += ')';
					}
				}

				$claimview.find( '.wikibase-snakview-value' ).append( h );

			});
		});
	},

	/**
	 * Parse a quantity value
	 *
	 * @param dv
	 * @return { 'unit': unit, 'amount': amount } or null
	 */
	parseQuantity: function ( dv ) {
		if ( dv === undefined || dv.type !== 'quantity' ) return null;

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
	var ns = mw.config.get( 'wgNamespaceNumber' );
	if ( ns !== 0 && ns !== 120 ) return;
	if (mw.config.get( 'wgAction' ) !== 'view') return;
	if (mw.config.get( 'wbIsEditView' ) === false) return;
	if (mw.config.get( 'wgIsRedirect' ) === true) return;

	Bamyers99.UnitsConverter.init() ;
} );
