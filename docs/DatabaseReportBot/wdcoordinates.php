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

 bunzip2 -c wikidatawiki.xml.bz2 | ./xml2 | grep '"P625"' | grep '"P17"' | php wdcoordinates.php&
 */

define('PROP_COORD', 'P625');
define('PROP_COUNTRY', 'P17');

$count = 0;
$isocodes = array();

// Q889	Afghanistan	AF
$hndl = fopen('countryisocodes.tsv', 'r');

while (! feof($hndl)) {
	$buffer = trim(fgets($hndl));
	if (empty($buffer)) continue;
	$record = csvparse($buffer, "\t", '');
	$qid = $record[0];
	$iso = $record[2];

	$isocodes[$qid] = $iso;
}

fclose($hndl);

$whndl = fopen('wdcoordinates.tsv', 'w');
$hndl = fopen('php://stdin', 'r');

while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Loaded $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);

	$data = json_decode($buffer, true);
	$qid = $data['id'];
	if (empty($qid)) continue;

	if (! isset($data['claims']) || ! isset($data['claims'][PROP_COORD]) || ! isset($data['claims'][PROP_COUNTRY])) continue;

	if (count($data['claims'][PROP_COUNTRY]) > 1) continue;
    $countryqid = 'Q' . $data['claims'][PROP_COUNTRY][0]['mainsnak']['datavalue']['value']['numeric-id'];

    if (! isset($isocodes[$countryqid])) {
        echo "$qid unknown country = $countryqid\n";
        continue;
    }

    $isocode = $isocodes[$countryqid];

    foreach ($data['claims'][PROP_COORD] as $claim) {
        $value = $claim['mainsnak']['datavalue']['value'];
        $latitude = $value['latitude'];
        $longitude = $value['longitude'];
        $globe = isset($value['globe']) ? $value['globe'] : '';

        if (! empty($globe) && $globe != 'http://www.wikidata.org/entity/Q2') continue;

		fwrite($whndl, "$qid\t$isocode\t$latitude\t$longitude\n");
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
