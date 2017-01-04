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
 */

$count = 0;
$edits = array();
$username = false;
$edittypes = array(
	'wbsetclaim-create' => 0,
	'wbcreateclaim' => 0,
	'wbsetlabel-add' => -1,
	'wbsetdescription-add' => -2,
	'wbsetaliases-add' => -3,
	'wbsetsitelink-add' => -4,
	'wbmergeitems-from' => -5
);

$prevmonth = date('Y-m', strtotime('-1 month'));

$hndl = fopen('php://stdin', 'r');
while (! feof($hndl)) {
	if (++$count % 1000000 == 0) echo "Processed $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision', '', $buffer);

	if (preg_match('!^/contributor/ip=([^\n]+)!', $buffer, $matches)) {
		$username = false;
	} elseif (preg_match('!^/contributor/username=([^\n]+)!', $buffer, $matches)) {
		$username = $matches[1];
	} elseif (preg_match('!^/timestamp=(\d{4}-\d{2})!', $buffer, $matches)) {
		$timestamp = $matches[1];
	} elseif (preg_match('!^/comment=([^\n]+)!', $buffer, $matches)) {
		if ($username === false) continue;
		$comment = $matches[1];

		foreach ($edittypes as $edittype => $typevalue) {
			if (strpos($comment, $edittype) !== false) {
				if ($typevalue === 0) {
					if (! preg_match('!\\[\\[Property:P(\d+)!', $comment, $matches)) break;
					$typevalue = $matches[1];
				}

				$key = "a$typevalue"; // don't want a numeric key

				if (! isset($edits[$username])) $edits[$username] = array();
				if (! isset($edits[$username][$key])) $edits[$username][$key] = array('t' => 0, 'm' => 0);
				++$edits[$username][$key]['t'];
				if ($timestamp == $prevmonth) ++$edits[$username][$key]['m'];
				break;
			}
		}
	}

}

echo "Processed $count\n";

fclose($hndl);
$hndl = fopen('navelgazer.tsv', 'w');

foreach ($edits as $username => $totals) {
	foreach ($totals as $key => $total) {
		$key = substr($key, 1);
		fwrite($hndl, "$username\t$key\t{$total['t']}\t{$total['m']}\n");
	}
}

fclose($hndl);
