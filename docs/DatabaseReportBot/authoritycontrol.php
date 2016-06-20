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

$authctls = array('VIAF' => 	'P214',
		'ISNI' => 			'P213',
		'GND' =>			'P227',
		'SELIBR' =>			'P906',
		'SUDOC' =>			'P269',
		'BNF' =>			'P268',
		'ResearcherID' =>	'P1053',
		'BIBSYS' =>			'P1015',
		'ULAN' =>			'P245',
		'MusicBrainz' =>	'P434',
		'MGP' =>			'P549',
		'NLA' =>			'P409',
		'NDL' =>			'P349',
		'NKC' =>			'P691',
		'SBN' =>			'P396',
		'BNE' =>			'P950',
		'ORCID' =>			'P496',
		'BPN' =>			'P651',
		'IPNI' =>			'P428' // International Plant Names Index
);

$count = 0;
$authitems = array();
foreach ($authctls as $propid) $authitems[$propid] = array();

$hndl = fopen('authoritycontrol.txt', 'r');
while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Processed $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);

	$data = json_decode($buffer, true);
	$id = $data['id'];

	if (! isset($data['claims'])) continue;
	$claims = $data['claims'];

	foreach ($authctls as $propid) {
		if (! isset($claims[$propid])) continue;
		if (count($claims[$propid]) > 1) continue;
		if (! isset($claims[$propid][0]['mainsnak']['datavalue'])) continue;

		$authid = $claims[$propid][0]['mainsnak']['datavalue']['value'];

		if (! isset($authitems[$propid][$authid])) $authitems[$propid][$authid] = array();
		$authitems[$propid][$authid][] = $id;
	}
}

fclose($hndl);
$hndl = fopen('authoritydups.txt', 'w');

foreach ($authctls as $authcode => $propid) {
	if (! empty($authitems[$propid])) fwrite($hndl,"==$authcode==\n");

	foreach ($authitems[$propid] as $items) {
		if (count($items) < 2) continue;
		foreach ($items as $id) fwrite($hndl, "[https://www.wikidata.org/wiki/$id $id] ");
		fwrite($hndl, "<br />\n");
	}
}

fclose($hndl);
