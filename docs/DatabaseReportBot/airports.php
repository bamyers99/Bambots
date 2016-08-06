<?php
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

 bunzip2 -c wikidatawiki.xml.bz2 | ./xml2 | grep ':62447}\|:1248784}' >airports.txt&
 */

define('PROP_INSTANCEOF', 'P31');
define('PROP_ICAO', 'P239');
define('PROP_IATA', 'P238');
define('INSTANCEOF_AIRPORT', 1248784);
define('INSTANCEOF_AERODROME', 62447);

$count = 0;
$iatas = array();
$icaos = array();
$airports = array();
$searchwords = array();
$commons = array('INTL', 'INTERNATIONAL', 'AIRPORT', 'FIELD', 'HELIPORT', 'RGNL', 'REGIONAL', 'COUNTY', 'STATE', 'CITY', 'FLD',
	'AFB', 'MUNI', 'MUNICIPAL', 'AFLD', 'AIRFIELD', 'BASE', 'AERODROME', 'GRAND', 'HARBOUR', 'WATER', 'AIR', 'FALLS', 'LAKE',
	'OCEAN', 'NEW', 'BIG', 'MEMORIAL', 'RIVER', 'MOUNTAIN', 'SEAPLANE', 'HILL', 'CREEK', 'BAY', 'AIRSTRIP', 'VALLEY', 'AIRPARK',
	'ISLAND', 'SEA', 'NORTH', 'SOUTH', 'EAST', 'WEST', 'PARK', 'DEL', 'COVE', 'PORT', 'NATIONAL', 'HOUSE', 'CENTRAL', 'BEACH',
	'LES', 'SOUTHWEST', 'GENERAL', 'CAPE', 'LANDING', 'POINT', 'HARBOR', 'ST.', 'MOUNT', 'FORT', 'FERRY', 'DOCK', 'LAC', 'OLD',
	'VILLAGE', 'ROCK', 'DAM', 'DOWNTOWN', 'SAN', 'SANTA', 'LOS', 'EXECUTIVE', 'KING', 'GREAT', 'LAKES', 'PUERTO', 'MONTE',
	'LONG', 'PRINCE', 'RAF', 'RED', 'LODGE', 'RIO', 'SAINT', 'PORTO', 'CAY', 'SAO', 'SPRINGS', 'LAS', 'NAS', 'ISLA', 'AAF',
	'JOSE', 'FORCE', 'CIUDAD', 'SOUTHEAST', 'NORTHEAST'
);
$rejects = array('TERMINAL', 'STATION', 'AIRPORTS');

// 1,"Goroka","Goroka","Papua New Guinea","GKA","AYGA",-6.081689,145.391881,5282,10,"U","Pacific/Port_Moresby"
$hndl = fopen('airports.csv', 'r');

while (! feof($hndl)) {
	if (++$count % 1000 == 0) echo "Loaded $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$record = csvparse($buffer);
	$id = $record[0];
	$name = $record[1];
	$country = $record[3];
	$iata = $record[4];
	$icao = $record[5];
	if ($icao == '\N') $icao = '';

	if (empty($iata) and empty($icao)) continue;

	$words = explode(' ', $name);

	foreach ($words as $word) {
		$uword = strtoupper($word);
		if (in_array($uword, $rejects)) continue 2;
	}

	if (! empty($iata)) $iatas[$iata] = $id;
	if (! empty($icao)) $icaos[$icao] = $id;
	$airports[$id] = array('name' => $name, 'country' => $country, 'iata' => $iata, 'icao' => $icao);

	foreach ($words as $word) {
		if (strlen($word) < 3) continue;
		$uword = strtoupper($word);
		if (in_array($uword, $commons)) continue;

		if (! isset($searchwords[$uword])) $searchwords[$uword] = array();
		$searchwords[$uword][] = $id;
	}
}

fclose($hndl);

$whndl = fopen('airports.html', 'w');
$hndl = fopen('airports.txt', 'r');
while (! feof($hndl)) {
	if (++$count % 1000 == 0) echo "Loaded $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);

	$data = json_decode($buffer, true);
	$qid = $data['id'];
	if (empty($qid)) continue;

	if (isset($data['claims']) && isset($data['claims'][PROP_INSTANCEOF])) {
	    $found = false;
	    foreach ($data['claims'][PROP_INSTANCEOF] as $claim) {
	        $iof = $claim['mainsnak']['datavalue']['value']['numeric-id'];
	        if ($iof == INSTANCEOF_AIRPORT || $iof == INSTANCEOF_AERODROME) {
	        	$found = true;
	        	break;
	        }
	    }

	    if (! $found) continue;

	    $icao = false;
	    $iata = false;

	    if (isset($data['claims'][PROP_ICAO][0]['mainsnak']['datavalue'])) {
			$icao = $data['claims'][PROP_ICAO][0]['mainsnak']['datavalue']['value'];
	    }

	    if (isset($data['claims'][PROP_IATA][0]['mainsnak']['datavalue'])) {
	    	$iata = $data['claims'][PROP_IATA][0]['mainsnak']['datavalue']['value'];
	    }

	    if ($icao !== false && $iata !== false) continue;

	    if (! isset($data['labels']['en']['value'])) continue;
	    $label = $data['labels']['en']['value'];

	    if ($icao !== false && isset($icaos[$icao])) {
	    	if (! empty($airports[$icaos[$icao]]['iata'])) {
	    		fwrite($whndl, "<a href='https://www.wikidata.org/wiki/$qid'>$label</a> IATA ({$airports[$icaos[$icao]]['iata']})<br />\n");
	    	}

	    	continue;
	    }

		if ($iata !== false && isset($iatas[$iata])) {
	    	if (! empty($airports[$iatas[$iata]]['icao'])) {
	    		fwrite($whndl, "<a href='https://www.wikidata.org/wiki/$qid'>$label</a> ICAO ({$airports[$iatas[$iata]]['icao']})<br />\n");
	    	}

	    	continue;
	    }

	    $words = explode(' ', $label);
	    $matchedwords = array();

	    foreach ($words as $key => $word) {
	    	$uword = strtoupper($word);
	    	if (in_array($uword, $commons)) {
	    		unset($words[$key]);
	    		continue;
	    	}

	    	if (isset($searchwords[$uword])) $matchedwords[] = $searchwords[$uword];
	    }

	    if (empty($matchedwords)) continue;

	    // Intersect the match ids
	    $sameids = $matchedwords[0];
	    $matchedcnt = count($matchedwords);

	    for ($x = 1; $x < $matchedcnt; ++$x) {
	    	$sameids = array_intersect($sameids, $matchedwords[$x]);
	    }

	    if (! empty($sameids)) {
			fwrite($whndl, "<a href='https://www.wikidata.org/wiki/$qid'>$label</a><br />\n");

			foreach ($sameids as $sameid) {
				fwrite($whndl, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$airports[$sameid]['name']} IATA ({$airports[$sameid]['iata']}) ICAO ({$airports[$sameid]['icao']})<br />\n");
			}

			continue;
	    }

	    $mergeids = $matchedwords[0];

	    for ($x = 1; $x < $matchedcnt; ++$x) {
	    	$mergeids = array_merge($mergeids, $matchedwords[$x]);
	    }

	    $mergeids = array_unique($mergeids);
		fwrite($whndl, "<a href='https://www.wikidata.org/wiki/$qid'>$label</a><br />\n");

		foreach ($mergeids as $mergeid) {
			fwrite($whndl, "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;~&nbsp;{$airports[$mergeid]['name']} IATA ({$airports[$mergeid]['iata']}) ICAO ({$airports[$mergeid]['icao']})<br />\n");
		}
	}
}

fclose($hndl);
fclose($whndl);

	/**
     * Parse a csv line that has "ed fields.
     * " is escaped using \"
     *
     * @param $buffer string Buffer to parse
     * @param $separator string Field separator (default: ,)
     * @param $delimiter string String delimiter (default: ")
     * @return array Fields
     */
	 function csvparse(&$buffer, $separator = ',', $delimiter = '"')
	{
		$fields = array();
		$field = 0;
		$buflen = strlen($buffer);
		$fields[0] = '';

		for ($x = 0; $x < $buflen; ++$x) {
			$char = $buffer[$x];

			if ($char == $delimiter) {
				$string = '';

				for (++$x; $x < $buflen; ++$x) {
					$char = $buffer[$x];

					if ($char == '\\') {
						if ($x+1 < $buflen) $string .= $buffer[++$x];
					} elseif ($char == $delimiter) {
						if ($x+1 < $buflen && $buffer[$x+1] != $separator) { // Hack because svick didn't escape "s
							$string .= $char;
						} else {
							break;
						}
					} else {
						$string .= $char;
					}
				}

				$fields[$field] = $string;
			} elseif ($char == $separator) {
				++$field;
				$fields[$field] = ''; // So that last field always has a value
			} else {
				$fields[$field] .= $char;
			}
		}

		return $fields;
	}
