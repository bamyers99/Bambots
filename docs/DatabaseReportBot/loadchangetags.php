<?php
/**
 Copyright 2021 Myers Enterprises II

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

if ($argc < 3) {
    echo 'Usage: loadchangetags.php "dbusername" "dbpassword"';
    exit;
}

$user = $argv[1];
$pass = $argv[2];

$dbh_wikidata = new PDO("mysql:host=127.0.0.1;dbname=s51454__wikidata;charset=utf8mb4", $user, $pass);
$dbh_wikidata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Load the tag data
$sql = 'SELECT id FROM navelgazertagdef';
$result = $dbh_wikidata->query($sql);
$result->setFetchMode(PDO::FETCH_ASSOC);
$tagdefs = [];
$users = [];

foreach ($result as $row) {
    $tagdefs[] = (int)$row['id'];
}

$hndl = fopen('userchangetag.tsv', 'r');

while (! feof($hndl)) {
    $buffer = rtrim(fgets($hndl));
    if (empty($buffer)) continue;

    $parts = explode("\t", $buffer);
    if (count($parts) == 1) $parts[1] = 0; // No tool
    $username = $parts[0];
    $toolid = (int)$parts[1];
    
    if ($toolid == 708) $toolid = 0; // wikidata-ui

    if (! in_array($toolid, $tagdefs)) continue;
    if (! isset($users[$username])) $users[$username] = [];
    if (! isset($users[$username][$toolid])) $users[$username][$toolid] = 0;
    $users[$username][$toolid] += 1;
}

fclose($hndl);

// Update navelgazertag table

foreach ($users as $username => $totals) {
    foreach ($totals as $toolid => $count) {
        $sql = "INSERT INTO navelgazertag VALUES ($toolid,?,$count,$count) ON DUPLICATE KEY UPDATE month_count = $count, total_count = total_count + $count";
        $sth = $dbh_wikidata->prepare($sql);
        $sth->bindValue(1, $username);
        $sth->execute();
    }
}

// Backup navelgazertag table
$backupFile = 'navelgazertag.sql.bz2';
$command = "mysqldump -h 127.0.0.1 -u {$user} -p{$pass} s51454__wikidata navelgazertag | bzip2 -9 > $backupFile";
$ret = system($command, $return_var);

