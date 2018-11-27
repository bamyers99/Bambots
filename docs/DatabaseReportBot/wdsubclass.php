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

 bunzip2 -c wikidatawiki*-pages-articles.xml.bz2 | ./xml2 | grep '"P279"\|"P31"' | php wdsubclass.php&
 */

define('PROP_SUBCLASSOF', 'P279');
define('PROP_INSTANCEOF', 'P31');
define('PROP_ISLISTOF', 'P360');

define('MIN_ORPHAN_DIRECT_INST_CNT', 5);
define('DIRECT_INSTANCE_CNT', 'a');
define('INDIRECT_INSTANCE_CNT', 'b');
define('DIRECT_CHILD_COUNT', 'c');
define('INDIRECT_CHILD_COUNT', 'd');
define('ISLISTOF_COUNT', 'e');
define('CLASS_FOUND', 'f');
define('PARENTS', 'p');

$count = 0;
$classes = array();

$whndl = fopen('wdsubclassclasses.tsv', 'w');
$hndl = fopen('php://stdin', 'r');

while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Processed $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);

	$data = json_decode($buffer, true);
	if (! isset($data['id'])) continue;
	$qid = $data['id'];
	if (empty($qid)) continue;
	$qid = (int)substr($qid, 1);

	if (! isset($data['claims']) || (! isset($data['claims'][PROP_SUBCLASSOF]) && ! isset($data['claims'][PROP_INSTANCEOF]) &&
		! isset($data['claims'][PROP_ISLISTOF]))) continue;

	if (isset($data['claims'][PROP_SUBCLASSOF])) {
		foreach ($data['claims'][PROP_SUBCLASSOF] as $parent) {
			if (! isset($parent['mainsnak']['datavalue']['value']['numeric-id'])) continue;
	    	$parentqid = (int)$parent['mainsnak']['datavalue']['value']['numeric-id'];

	    	if (! isset($classes[$qid])) $classes[$qid] = init_class();
	    	$classes[$qid][CLASS_FOUND] = true;
	    	$classes[$qid][PARENTS][] = $parentqid;

	    	if (! isset($classes[$parentqid])) $classes[$parentqid] = init_class();
	    	++$classes[$parentqid][DIRECT_CHILD_COUNT];
	    	$classes[$parentqid][CLASS_FOUND] = true;

	    	fwrite($whndl, "$parentqid\t$qid\n");
		}
	}

	if (isset($data['claims'][PROP_INSTANCEOF])) {
		foreach ($data['claims'][PROP_INSTANCEOF] as $instanceof) {
			if (! isset($instanceof['mainsnak']['datavalue']['value']['numeric-id'])) continue;
			$instanceqid = (int)$instanceof['mainsnak']['datavalue']['value']['numeric-id'];

			if (! isset($classes[$instanceqid])) $classes[$instanceqid] = init_class();
			++$classes[$instanceqid][DIRECT_INSTANCE_CNT];
		}
	}

	if (isset($data['claims'][PROP_ISLISTOF])) {
		foreach ($data['claims'][PROP_ISLISTOF] as $islistof) {
			if (! isset($islistof['mainsnak']['datavalue']['value']['numeric-id'])) continue;
			$classqid = (int)$islistof['mainsnak']['datavalue']['value']['numeric-id'];

	    	if (! isset($classes[$classqid])) $classes[$classqid] = init_class();
	    	++$classes[$classqid][ISLISTOF_COUNT];
		}
	}
}

echo "Processed $count\n";
echo "Class count " . count($classes) . "\n";

fclose($hndl);
fclose($whndl);

// Calc totals
$count = 0;

foreach ($classes as $classqid => $class) {
	if (++$count % 100000 == 0) echo "Processed $count\n";

	if (! $class[CLASS_FOUND] && $class[DIRECT_INSTANCE_CNT] < MIN_ORPHAN_DIRECT_INST_CNT) {
		unset($classes[$classqid]); // No need to report because other report catches these.
		continue;
	}

	$parents = array();
	foreach ($class[PARENTS] as $parentqid) {
		recurse_parents($parentqid, $parents, $classes, 0);
	}

	foreach ($parents as $parentqid => $dummy) {
		$classes[$parentqid][INDIRECT_CHILD_COUNT] += $class[DIRECT_CHILD_COUNT];
		$classes[$parentqid][INDIRECT_INSTANCE_CNT] += $class[DIRECT_INSTANCE_CNT];
	}
}

// Write totals
$whndl = fopen('wdsubclasstotals.tsv', 'w');

foreach ($classes as $classqid => $class) {
	$isroot = count($class[PARENTS]) ? 'N' : 'Y';

    fwrite($whndl, "$classqid\t$isroot\t{$class[DIRECT_CHILD_COUNT]}\t{$class[INDIRECT_CHILD_COUNT]}\t{$class[DIRECT_INSTANCE_CNT]}\t{$class[INDIRECT_INSTANCE_CNT]}\t{$class[ISLISTOF_COUNT]}\n");
}

fclose($whndl);
echo "Finished\n";

/**
 * Recurse class parents collecting qids
 *
 * @param unknown $parentqid
 * @param unknown $parents
 * @param unknown $classes
 * @param unknown $depth
 */
function recurse_parents($parentqid, &$parents, &$classes, $depth)
{
	++$depth;
	if ($depth > 100) return;

	if (isset($parents[$parentqid])) return;
	$parents[$parentqid] = true; // Prevents dups

	$parent = $classes[$parentqid];

	foreach ($parent[PARENTS] as $parentqid) {
		recurse_parents($parentqid, $parents, $classes, $depth);
	}
}

/**
 * Initialize a class object
 */
function init_class()
{
	return array(DIRECT_CHILD_COUNT => 0,  INDIRECT_CHILD_COUNT => 0, PARENTS => array(), DIRECT_INSTANCE_CNT => 0,
		INDIRECT_INSTANCE_CNT => 0, ISLISTOF_COUNT => 0, CLASS_FOUND => false);
}