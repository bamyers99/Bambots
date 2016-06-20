<?php
/**
 Copyright 2014 Myers Enterprises II

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

define('PROP_DATE_OF_BIRTH', 'P569');
define('PROP_DATE_OF_DEATH', 'P570');

$count = 0;
$people = array();

$hndl = fopen('people.txt', 'r');
while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Processed $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);

	$data = json_decode($buffer, true);
	$id = $data['id'];

	if (! isset($data['claims']) || ! isset($data['claims'][PROP_DATE_OF_DEATH]) || ! isset($data['claims'][PROP_DATE_OF_BIRTH])) {
		echo "Dates not found for $id\n";
		continue;
	}

	if (count($data['claims'][PROP_DATE_OF_DEATH]) > 1) continue; // Multiple death dates
	if (count($data['claims'][PROP_DATE_OF_BIRTH]) > 1) continue; // Multiple birth dates
	if (! isset($data['claims'][PROP_DATE_OF_BIRTH][0]['mainsnak']['datavalue'])) continue;
	if (! isset($data['claims'][PROP_DATE_OF_DEATH][0]['mainsnak']['datavalue'])) continue;

	$precision = (int)$data['claims'][PROP_DATE_OF_BIRTH][0]['mainsnak']['datavalue']['value']['precision'];
	if ($precision < 11) continue; // Want at least day precision

	$birthdate = getdateparts($data['claims'][PROP_DATE_OF_BIRTH][0]['mainsnak']['datavalue']['value']['time']);
	$deathdate = getdateparts($data['claims'][PROP_DATE_OF_DEATH][0]['mainsnak']['datavalue']['value']['time']);

	$key = $birthdate['year'] . $birthdate['month'] . $birthdate['day'] . $deathdate['year'] . $deathdate['month'] . $deathdate['day'];
	if (! isset($people[$key])) $people[$key] = array();
	$people[$key][] = $id;
}

fclose($hndl);
$hndl = fopen('potentialdups.txt', 'w');

foreach ($people as $persons) {
	if (count($persons) < 2) continue;
	foreach ($persons as $id) fwrite($hndl, "[https://www.wikidata.org/wiki/$id $id] ");
	fwrite($hndl, "<br />\n");
}

fclose($hndl);

function getdateparts($date)
{
	$return = array();

	// Dates are stored as 28 characters if the year > 99, else 26 characters +00000002014-01-01T00:00:00Z or +000000050-01-01T00:00:00Z
	if (strlen($date) > 26) {
		$return['year'] = substr($date, 8, 4);
		$return['month'] = substr($date, 13, 2);
		$return['day'] = substr($date, 16, 2);
	} else {
		$return['year'] = substr($date, 8, 2);
		$return['month'] = substr($date, 11, 2);
		$return['day'] = substr($date, 14, 2);
	}

	return $return;
}