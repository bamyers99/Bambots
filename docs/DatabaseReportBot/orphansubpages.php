<?php
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
 */

$count = 0;

$fleft = fopen('/data/project/bambots/Bambots/data/redirbasepagetitles', 'r');
$fright = fopen('/data/project/bambots/Bambots/data/sortedsubpagetitles', 'r');
$fsubpages = fopen('/data/project/bambots/Bambots/data/subpages', 'w');

$lleft = trim(fgets($fleft));
$lright = trim(fgets($fright));

list($rns, $rbasepage, $rpage) = explode(' ', $lright);
$rns = (int)$rns;

while (! feof($fleft)) {
	++$count;
	if ($count % 1000 == 0) fprintf(STDERR, "Processed $count\n");

	list($lns, $lbasepage) = explode("\t", $lleft);

	$lns = (int)$lns;

	while ($lns > $rns || ($lns == $rns && $lbasepage != $rbasepage)) {
		$lright = trim(fgets($fright));
		if (feof($fright)) break;
		list($rns, $rbasepage, $rpage) = explode(' ', $lright);
		$rns = (int)$rns;
	}

	while ($lns == $rns && $lbasepage == $rbasepage) {
		fwrite($fsubpages, "$rns\t$rbasepage\t$rpage\n");

		$lright = trim(fgets($fright));
		list($rns, $rbasepage, $rpage) = explode(' ', $lright);
		$rns = (int)$rns;
	}

	$lleft = trim(fgets($fleft));

}

fclose($fsubpages);
