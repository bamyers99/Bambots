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

 bunzip2 -c wikidatawiki.xml.bz2 | ./xml2 | grep '"P131"' | grep '"P17"' | grep '"P625"' | php wdsubdivcoords.php&
 */

define('PROP_SUBDIV', 'P131');
define('PROP_COUNTRY', 'P17');
define('PROP_COORD', 'P625');

$count = 0;
$countrycodes = array();
$subdivcodes = array();

// Q889	Afghanistan	AF
$hndl = fopen('countryisocodes.tsv', 'r');

while (! feof($hndl)) {
	$buffer = trim(fgets($hndl));
	if (empty($buffer)) continue;
	$record = csvparse($buffer, "\t", '');
	$qid = $record[0];
	$iso = $record[2];

	$countrycodes[$qid] = $iso;
}

fclose($hndl);

// Q1649	US-OK
$hndl = fopen('countrysubdivisocodes.tsv', 'r');

while (! feof($hndl)) {
	$buffer = trim(fgets($hndl));
	if (empty($buffer)) continue;
	$record = csvparse($buffer, "\t", '');
	$qid = $record[0];
	$iso = $record[1];

	$subdivcodes[$qid] = $iso;
}

fclose($hndl);

$whndl = fopen('wdsubdivcoords.tsv', 'w');
$hndl = fopen('php://stdin', 'r');

while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Loaded $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);

	$data = json_decode($buffer, true);
	$qid = $data['id'];
	if (empty($qid)) continue;

	if (! isset($data['claims']) || ! isset($data['claims'][PROP_SUBDIV]) || ! isset($data['claims'][PROP_COUNTRY]) || ! isset($data['claims'][PROP_COORD])) continue;

	if (count($data['claims'][PROP_COUNTRY]) > 1) continue;
	if (!isset($data['claims'][PROP_COUNTRY][0]['mainsnak']['datavalue'])) continue;
    $countryqid = 'Q' . $data['claims'][PROP_COUNTRY][0]['mainsnak']['datavalue']['value']['numeric-id'];

    $label = $qid;
    if (isset($data['labels']['en']['value'])) $label = $data['labels']['en']['value'];

    if (! isset($countrycodes[$countryqid])) continue;

    $countryisocode = $countrycodes[$countryqid];

    if (count($data['claims'][PROP_COORD]) > 1) continue;
    if (!isset($data['claims'][PROP_COORD][0]['mainsnak']['datavalue'])) continue;

    $value = $data['claims'][PROP_COORD][0]['mainsnak']['datavalue']['value'];
    $latitude = $value['latitude'];
    $longitude = $value['longitude'];
    $globe = isset($value['globe']) ? $value['globe'] : '';

    if (! empty($globe) && $globe != 'http://www.wikidata.org/entity/Q2') continue;

    $output = "$qid\t$label\t$latitude\t$longitude";
    $subdivcnt = 0;

    foreach ($data['claims'][PROP_SUBDIV] as $claim) {
    	if (! isset($claim['mainsnak']['datavalue'])) continue;
    	$subdivqid = 'Q' . $claim['mainsnak']['datavalue']['value']['numeric-id'];
    	if (! isset($subdivcodes[$subdivqid])) continue;

    	$subdivisocode = $subdivcodes[$subdivqid];
    	list($subdivcountry, $subdiv) = explode('-', $subdivisocode);

    	if ($countryisocode != $subdivcountry) continue;

    	$output .= "\t$subdivisocode";
    	++$subdivcnt;
    }

    if ($subdivcnt) fwrite($whndl, "$output\n");
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
