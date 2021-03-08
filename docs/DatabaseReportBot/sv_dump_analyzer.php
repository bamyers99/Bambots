<?php
/**
 Copyright 2020 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.

 Perl program instrumentation:

 use Test::LeakTrace;
 leaktrace{
   code_to_analyze();
 } -verbose;

 */

if ($argc < 2) {
    echo "Usage: sv_dump_analyzer.php <dump file name>\n";
    exit;
}

$hndl = fopen($argv[1], 'r');

$lines = [];

while (! feof($hndl)) {
    $buffer = rtrim(fgets($hndl));

    // leaked SCALAR(0x55aac170bb50) from ./checkwiki.pl line 2348
    if (preg_match('!^leaked SCALAR.* from (.*) line (\d+)!', $buffer, $matches)) {
        $sourcefile = $matches[1];
        $linenum = $matches[2];

        $buffer = rtrim(fgets($hndl)); // pre source line
        $sourcecode = str_replace('\\', '\\\\', rtrim(fgets($hndl))); // source line
        $buffer = rtrim(fgets($hndl)); // post source line
        $buffer = rtrim(fgets($hndl)); // SV
        $buffer = rtrim(fgets($hndl)); // REFCNT
        $buffer = rtrim(fgets($hndl)); // FLAGS

        if (strpos($buffer, 'POK') === false) continue;
        $buffer = rtrim(fgets($hndl));
        if (strpos($buffer, '  IV =') === 0) $buffer = rtrim(fgets($hndl));
        if (strpos($buffer, '  NV =') === 0) $buffer = rtrim(fgets($hndl));
        if (strpos($buffer, '  OFFSET =') === 0) $buffer = rtrim(fgets($hndl));

        // PV = 0x55aac1753cc0 "\n"\0 [UTF8 "\n"]
        $matched = preg_match('!"(.*?)"\\\\0!', $buffer, $matches);

        if (! $matched) {
            $matched = preg_match('!"(.*?)"!', $buffer, $matches); // try without trailed \0
        }

        if (! $matched) {
            echo "value not found: $buffer\n";
            continue;
        }

        $value = str_replace('\\', '\\\\', $matches[1]);

        $buffer = rtrim(fgets($hndl)); // CUR
        $buffer = rtrim(fgets($hndl)); // LEN = 82184
        preg_match('!(\d+)!', $buffer, $matches);
        $bufsize = $matches[1];

        $index = "$sourcefile:$linenum";

        if (! isset($lines[$index])) $lines[$index] = ['sourcecode' => $sourcecode, 'count' => 0, 'bytes' => 0, 'values' => []];

        ++$lines[$index]['count'];
        $lines[$index]['bytes'] += $bufsize;

        if (! isset($lines[$index]['values'][$value])) $lines[$index]['values'][$value] = 0;
        ++$lines[$index]['values'][$value];
    }
}

fclose($hndl);

ksort($lines);

foreach ($lines as $file_line => $data) {
    if ($data['count'] < 10) continue;

    echo "\nFile: $file_line Count: {$data['count']} Bytes: {$data['bytes']} Code: {$data['sourcecode']}\n";

    foreach ($data['values'] as $value => $count) {
        if (strlen($value) > 70) $value = substr($value, 0, 65) . '...';
        echo "   $value ($count)\n";
    }
}
