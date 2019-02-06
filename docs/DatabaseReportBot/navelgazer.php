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
$edits = [];
$langs = [];
$username = false;
$edittypes = [
	'wbsetclaim-create' => 0,
	'wbcreateclaim' => 0,
	'wbsetlabel-add' => -1,
	'wbsetdescription-add' => -2,
	'wbsetaliases-add' => -3,
	'wbsetsitelink-add' => -4,
	'wbmergeitems-from' => -5,
    'add-form' => -6,
    'add-form-representations' => -7,
    'add-form-grammatical-features' => -8,
    'add-sense' => -9,
    'add-sense-glosses' => -10
];

/* wbsetlabel-add:1|he */
/* wbsetdescription-add:1|yo */
/* wbsetaliases-add:1|sv */
/* wbsetsitelink-add:1|nlwikinews */

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

				if (! isset($edits[$username])) $edits[$username] = [];
				if (! isset($edits[$username][$key])) $edits[$username][$key] = ['t' => 0, 'm' => 0];
				++$edits[$username][$key]['t'];
				if ($timestamp == $prevmonth) ++$edits[$username][$key]['m'];

				if ($typevalue === -1 || $typevalue === -2 || $typevalue === -3 || $typevalue === -4) {
				    $lang = '';

				    if ($typevalue == -4) {
				        if (preg_match('!\\|([a-z]{2,3})wiki!', $comment, $matches)) {
				            $lang = $matches[1];
				        }
				    } else {
				        if (preg_match('!\\|([a-z]{2,3}(?:-[a-z]+)*)!', $comment, $matches)) {
				            $lang = $matches[1];
				        }
				    }

				    if (! empty($lang)) {
				        if (! isset($langs[$lang])) $langs[$lang] = [];
				        if (! isset($langs[$lang][$username])) $langs[$lang][$username] = ['t' => 0, 'm' => 0];
				        ++$langs[$lang][$username]['t'];
				        if ($timestamp == $prevmonth) ++$langs[$lang][$username]['m'];
				    }
				}

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

$hndl = fopen('navelgazerlang.tsv', 'w');

foreach ($langs as $lang => $totals) {
    foreach ($totals as $username => $total) {
        fwrite($hndl, "$lang\t$username\t{$total['t']}\t{$total['m']}\n");
    }
}

fclose($hndl);
