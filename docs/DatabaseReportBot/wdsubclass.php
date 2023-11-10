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

 bunzip2 -c wikidatawiki*-pages-articles.xml.bz2 | ./xml2 | grep '"P279"\|"P31"\|"P360"' | php wdsubclass.php&
 */

define('PROP_SUBCLASSOF', 'P279');
define('PROP_INSTANCEOF', 'P31');
define('PROP_ISLISTOF', 'P360');

define('MIN_ORPHAN_DIRECT_INST_CNT', 5);
define('DIRECT_CHILD_INSTANCE_CNT', 'a');
define('INDIRECT_INSTANCE_CHILD_CNT', 'b');
define('CLASS_FOUND_ISLISTOF_COUNT', 'e');
define('PARENTS', 'p');

DEFINE('MONTHLY_INCREMENT', 0x100000000);
DEFINE('GRANDTOTAL_MASK', MONTHLY_INCREMENT - 1);

$count = 0;
$classes = [];

$whndl = fopen('wdsubclassclasses.tsv', 'w');
$hndl = fopen('php://stdin', 'r');
$config = file_get_contents('../replica.my.cnf');
preg_match("!password='([^']+)'!", $config, $matches);
$pass = $matches[1];
$dbh = new PDO("mysql:host=tools.db.svc.wikimedia.cloud;dbname=s51454__wikidata;charset=utf8mb4", 's51454', $pass);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->exec('TRUNCATE tempscvalcnt');
$dbh->exec('TRUNCATE tempscvalscnt');
$dbh->exec('TRUNCATE tempscvals');

$sql = 'INSERT INTO tempscvalcnt VALUES (?,1) ON DUPLICATE KEY UPDATE valcount = valcount + 1';
$sth_tempscvalcnt_upsert = $dbh->prepare($sql);

$sql = 'INSERT INTO tempscvalscnt (ckey,valcount) VALUES (?,?) ON DUPLICATE KEY UPDATE valcount = VALUE(valcount)';
$sth_tempscvalscnt_upsert = $dbh->prepare($sql);

$sql = 'SELECT valcount FROM tempscvalscnt WHERE ckey = ?';
$sth_tempscvalscnt_query = $dbh->prepare($sql);

$sql = 'SELECT count(*) FROM tempscvals WHERE ckey = ?';
$sth_tempscvals_query = $dbh->prepare($sql);

$sql = 'DELETE FROM tempscvals WHERE ckey = ?';
$sth_tempscvals_delete = $dbh->prepare($sql);

$sql = 'INSERT INTO tempscvals VALUES (?,?,1) ON DUPLICATE KEY UPDATE valcount = valcount + 1';
$sth_tempscvals_upsert = $dbh->prepare($sql);

while (! feof($hndl)) {
	if (++$count % 1000000 == 0) echo "Processed $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = substr($buffer, 30); // /mediawiki/page/revision/text=

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
	    	$classes[$qid][CLASS_FOUND_ISLISTOF_COUNT] = MONTHLY_INCREMENT;
	    	$classes[$qid][PARENTS][] = $parentqid;

	    	if (! isset($classes[$parentqid])) $classes[$parentqid] = init_class();
	    	++$classes[$parentqid][DIRECT_CHILD_INSTANCE_CNT];
	    	$classes[$parentqid][CLASS_FOUND_ISLISTOF_COUNT] = MONTHLY_INCREMENT;

	    	fwrite($whndl, "$parentqid\t$qid\n");
		}
	}

	if (isset($data['claims'][PROP_INSTANCEOF])) {
		foreach ($data['claims'][PROP_INSTANCEOF] as $instanceof) {
			if (! isset($instanceof['mainsnak']['datavalue']['value']['numeric-id'])) continue;
			$instanceqid = (int)$instanceof['mainsnak']['datavalue']['value']['numeric-id'];

			if (! isset($classes[$instanceqid])) $classes[$instanceqid] = init_class();
			$classes[$instanceqid][DIRECT_CHILD_INSTANCE_CNT] += MONTHLY_INCREMENT;

			processvalues($instanceqid, $data);
		}
	}

	if (isset($data['claims'][PROP_ISLISTOF])) {
		foreach ($data['claims'][PROP_ISLISTOF] as $islistof) {
			if (! isset($islistof['mainsnak']['datavalue']['value']['numeric-id'])) continue;
			$classqid = (int)$islistof['mainsnak']['datavalue']['value']['numeric-id'];

	    	if (! isset($classes[$classqid])) $classes[$classqid] = init_class();
	    	++$classes[$classqid][CLASS_FOUND_ISLISTOF_COUNT];
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
	if (++$count % 1000000 == 0) echo "Processed $count\n";
	$directchildcnt = $class[DIRECT_CHILD_INSTANCE_CNT] & GRANDTOTAL_MASK;
	$directinstancecnt = $class[DIRECT_CHILD_INSTANCE_CNT] >> 32;
	$classfound = $class[CLASS_FOUND_ISLISTOF_COUNT] >> 32;
	
	if (! $classfound && $directinstancecnt < MIN_ORPHAN_DIRECT_INST_CNT) {
		unset($classes[$classqid]); // No need to report because other report catches these.
		continue;
	}

	$parents = [];
	foreach ($class[PARENTS] as $parentqid) {
		recurse_parents($parentqid, $parents, $classes, 0);
	}

	foreach ($parents as $parentqid => $dummy) {
	    $classes[$parentqid][INDIRECT_INSTANCE_CHILD_CNT] += $directchildcnt;
	    $classes[$parentqid][INDIRECT_INSTANCE_CHILD_CNT] += (MONTHLY_INCREMENT * $directinstancecnt);
	}
}

// Write totals
$whndl = fopen('wdsubclasstotals.tsv', 'w');

foreach ($classes as $classqid => $class) {
    $isroot = count($class[PARENTS]) ? 'N' : 'Y';
    $indirectchild = $class[INDIRECT_INSTANCE_CHILD_CNT] & GRANDTOTAL_MASK;
    $indirectinstanceof = $class[INDIRECT_INSTANCE_CHILD_CNT] >> 32;
    $directchildcnt = $class[DIRECT_CHILD_INSTANCE_CNT] & GRANDTOTAL_MASK;
    $directinstancecnt = $class[DIRECT_CHILD_INSTANCE_CNT] >> 32;
    $islistofcnt = $class[CLASS_FOUND_ISLISTOF_COUNT] & GRANDTOTAL_MASK;
    
    fwrite($whndl, "$classqid\t$isroot\t$directchildcnt\t$indirectchild\t$directinstancecnt\t$indirectinstanceof\t$islistofcnt\n");
}

fclose($whndl);

// Write value counts
$whndl = fopen('wdsubclassvalues.tsv', 'w');

$sql = 'SELECT * FROM tempscvalscnt';
$sth = $dbh->query($sql);
$sth->setFetchMode(PDO::FETCH_NUM);

$sql = 'SELECT valueid, valcount FROM tempscvals WHERE ckey = ?';
$sth2 = $dbh->prepare($sql);

while ($row = $sth->fetch()) {
    $key = $row[0];
    $count = $row[1];
    list($classqid, $pid, $qualpid) = explode('-', $key);
    
    if ($count > 50) {
        fwrite($whndl, "$classqid\t$pid\t$qualpid\t0\t0\n");
    } else {
        $sth2->bindParam(1, $key);
        $sth2->execute();
        $sth2->setFetchMode(PDO::FETCH_NUM);
        
        while ($row2 = $sth2->fetch()) {
            $vkey = $row2[0];
            $count = $row2[1];
            fwrite($whndl, "$classqid\t$pid\t$qualpid\t$vkey\t$count\n");
        }
    }
}

$sql = 'SELECT * FROM tempscvalcnt';
$sth = $dbh->query($sql);
$sth->setFetchMode(PDO::FETCH_NUM);

while ($row = $sth->fetch()) {
    $key = $row[0];
    $count = $row[1];
    list($classqid, $pid, $qualpid) = explode('-', $key);
    if ($count > 9) fwrite($whndl, "$classqid\t$pid\t$qualpid\tC\t$count\n");
}

fclose($whndl);

$dbh->exec('TRUNCATE tempscvalcnt');
$dbh->exec('TRUNCATE tempscvalscnt');
$dbh->exec('TRUNCATE tempscvals');

echo "Finished\n";

/**
 * Recurse class parents collecting qids
 *
 * @param unknown $parentqid
 * @param unknown $parents
 * @param unknown $classes
 * @param unknown $depth
 */
function recurse_parents($parentqid, &$parents, $classes, $depth)
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
    return [DIRECT_CHILD_INSTANCE_CNT => 0,  INDIRECT_INSTANCE_CHILD_CNT => 0, PARENTS => [],
        CLASS_FOUND_ISLISTOF_COUNT => 0];
}

/**
 * Save wikibase-item type values for properties and their qualifiers.
 *
 * @param unknown $classqid
 * @param unknown $data
 */
function processvalues($classqid, $data)
{
    global $sth_tempscvalcnt_upsert, $sth_tempscvalscnt_query, $sth_tempscvalscnt_upsert, $sth_tempscvals_upsert,
        $sth_tempscvals_query, $sth_tempscvals_delete;
    
    return;

    foreach ($data['claims'] as $pid => $claims) {
        if ($pid == PROP_INSTANCEOF || $pid == PROP_SUBCLASSOF) continue;
        $pid = substr($pid, 1);

        $ckey = "$classqid-$pid-0";
        $sth_tempscvalcnt_upsert->bindParam(1, $ckey);
        $sth_tempscvalcnt_upsert->execute();

        foreach ($claims as $claim) {
            $mainsnak = $claim['mainsnak'];

            if ($mainsnak['snaktype'] == 'value' && $mainsnak['datavalue']['type'] == 'wikibase-entityid' && $mainsnak['datavalue']['value']['entity-type'] == 'item') {
                $valueid = $mainsnak['datavalue']['value']['numeric-id'];

                $valcount = 0;
                $sth_tempscvalscnt_query->bindParam(1, $ckey);
                $sth_tempscvalscnt_query->execute();
                
                if ($row = $sth_tempscvalscnt_query->fetch(PDO::FETCH_NUM)) {
                    $valcount = $row[0];
                }
                
                if ($valcount <= 50) {
                    $sth_tempscvals_upsert->bindParam(1, $ckey);
                    $sth_tempscvals_upsert->bindParam(2, $valueid);
                    $sth_tempscvals_upsert->execute();
                    
                    $sth_tempscvals_query->bindParam(1, $ckey);
                    $sth_tempscvals_query->execute();
                    $row = $sth_tempscvals_query->fetch(PDO::FETCH_NUM);
                    
                    $valcount2 = $row[0];
                    
                    if ($valcount2 != $valcount) {
                        $sth_tempscvalscnt_upsert->bindParam(1, $ckey);
                        $sth_tempscvalscnt_upsert->bindParam(2, $valcount2);
                        $sth_tempscvalscnt_upsert->execute();
                        
                        if ($valcount2 == 51) {
                            $sth_tempscvals_delete->bindParam(1, $ckey);
                            $sth_tempscvals_delete->execute();
                        }
                    }
                }
            }

            if (isset($claim['qualifiers'])) {
                foreach ($claim['qualifiers'] as $qualpid => $qualifiers) {
                    $qualpid = substr($qualpid, 1);

                    $qkey = "$classqid-$pid-$qualpid";
                    $sth_tempscvalcnt_upsert->bindParam(1, $qkey);
                    $sth_tempscvalcnt_upsert->execute();

                    foreach ($qualifiers as $qualifier) {
                        if ($qualifier['snaktype'] != 'value' || $qualifier['datavalue']['type'] != 'wikibase-entityid' ||
                            $qualifier['datavalue']['value']['entity-type'] != 'item') continue;
                        $valueid = $qualifier['datavalue']['value']['numeric-id'];

                        $valcount = 0;
                        $sth_tempscvalscnt_query->bindParam(1, $qkey);
                        $sth_tempscvalscnt_query->execute();
                        
                        if ($row = $sth_tempscvalscnt_query->fetch(PDO::FETCH_NUM)) {
                            $valcount = $row[0];
                        }
                        
                        if ($valcount <= 50) {
                            $sth_tempscvals_upsert->bindParam(1, $qkey);
                            $sth_tempscvals_upsert->bindParam(2, $valueid);
                            $sth_tempscvals_upsert->execute();
                            
                            $sth_tempscvals_query->bindParam(1, $qkey);
                            $sth_tempscvals_query->execute();
                            $row = $sth_tempscvals_query->fetch(PDO::FETCH_NUM);
                            
                            $valcount2 = $row[0];
                            
                            if ($valcount2 != $valcount) {
                                $sth_tempscvalscnt_upsert->bindParam(1, $qkey);
                                $sth_tempscvalscnt_upsert->bindParam(2, $valcount2);
                                $sth_tempscvalscnt_upsert->execute();
                                
                                if ($valcount2 == 51) {
                                    $sth_tempscvals_delete->bindParam(1, $qkey);
                                    $sth_tempscvals_delete->execute();
                                }
                            }
                        }
                    }
                }
            } // qualifiers
        }
    }
}