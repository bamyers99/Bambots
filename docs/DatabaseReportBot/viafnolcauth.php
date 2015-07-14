<?php
/**
 Copyright 2015 Myers Enterprises II

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

define('PROP_VIAF', 'P214');

$count = 0;
$wikidatas = array();

$hndl = fopen('viafnolcauth.txt', 'r');
while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Loaded $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);

	$data = json_decode($buffer, true);
	$id = $data['id'];

	if (! isset($data['claims']) || ! isset($data['claims'][PROP_VIAF])) {
		echo "VIAF not found for $id\n";
		continue;
	}

	if (count($data['claims'][PROP_VIAF]) > 1) continue; // Multiple VIAFs
	if (! isset($data['claims'][PROP_VIAF][0]['mainsnak']['datavalue'])) continue;

	$viaf = $data['claims'][PROP_VIAF][0]['mainsnak']['datavalue']['value'];

	$wikidatas[$viaf] = $id;
}

fclose($hndl);
$count = 0;

# read the viaf -> lcauth cross reference
$hndl = fopen('viafnolcauth.rdf', 'r');
$whndl = fopen('viaflcauths.html', 'w');

while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Processed $count\n";
	$buffer = trim(fgets($hndl));
	if (empty($buffer)) continue;

	list($viaf, $lcauth) = explode("\t", $buffer);

	if (isset($wikidatas[$viaf])) {
		$id = $wikidatas[$viaf];
		fwrite($whndl, "<a href='https://www.wikidata.org/wiki/$id'>$id</a> <a href='http://id.loc.gov/authorities/names/$lcauth'>$lcauth</a> $lcauth<br />\n");
	}
}

fclose($hndl);
fclose($whndl);
