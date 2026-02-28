<?php
/**
 Copyright 2026 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 
 https://github.com/wikimedia/Wikibase/blob/master/lib/i18n/en.json
 https://github.com/wikimedia/mediawiki-extensions-WikibaseLexeme/blob/master/i18n/en.json
 https://github.com/wikimedia/mediawiki-extensions-EntitySchema/blob/master/i18n/en.json
 https://dumps.wikimedia.org/other/mediawiki_history/
 https://wikitech.wikimedia.org/wiki/Data_Platform/Data_Lake/Edits/MediaWiki_history_dumps
 */

$count = 0;
$edits = [];
$langs = [];
$tools = [];
$username = false;
$matches = [];
$propmeta = [];
$edittypes = [
	'!^wbsetclaim-create:!' => 0,
	'!^wbcreateclaim!' => 0, // no : because matches 5 actions
	'!^wbsetlabel-add:!' => -1,
	'!^wbsetdescription-add:!' => -2,
	'!^wbsetaliases-add:!' => -3,
    '!^wbsetaliases-set:!' => -3,
    '!^wbsetsitelink-add!' => -4, // no : because matches multiple actions
	'!^wbmergeitems-from:!' => -5,
    '!^add-form:!' => -6,
    '!^wbeditentity-create-form!' => -6,
    '!^add-form-representations:!' => -7,
    '!^add-form-grammatical-features:!' => -8,
    '!^add-sense:!' => -9,
    '!^wbeditentity-create-sense!' => -9,
    '!^add-sense-glosses:!' => -10,
    '!^wbsetreference-add:!' => -11,
    '!^wbsetreference:!' => -11,
    '!^wbsetqualifier-add:!' => -12,
    '!^wbsetlabel-set:!' => -13,
    '!^wbsetlabeldescriptionaliases:!' => -13,
    '!^wbsetdescription-set:!' => -14,
    '!^wbremoveclaims-remove:!' => -15,
    '!^wbsetclaim-update:!' => -16,
    '!^undo:!' => -17,
    '!^wbeditentity-create:!' => -18,
    '!^wbeditentity-create-item:!' => -18,
    '!^wbeditentity-create-property:!' => -18,
    '!^wbeditentity:!' => -18,
    '!^special-create-item:!' => -18,
    '!^special-create-property:!' => -18,
    '!^wbcreate-new:!' => -18,
    '!^wbeditentity-update!' => -19, // no : because matches multiple actions
    '!^wbsetsitelink-remove:!' => -20,
    '!^wbsetdescription-remove:!' => -21,
    '!^wbsetaliases-remove:!' => -22,
    '!^restore:!' => -23,
    '!^wbsetlabel-remove:!' => -24,
    '!^wbsetsitelink-set!' => -25, // no : because matches multiple actions
    '!^wbremovereferences-remove:!' => -26,
    '!^wbsetaliases-update:!' => -27,
    '!^wbsetreference-set:!' => -28,
    '!^wbsetclaim-update-references:!' => -28,
    '!^wbsetclaim-update-qualifiers:!' => -29,
    '!^wbsetqualifier-update:!' => -29,
    '!^update-form-grammatical-features:!' => -30,
    '!^update-form-representations:!' => -30,
    '!^update-form-elements:!' => -30,
    '!^remove-form:!' => -31,
    '!^set-form-representations:!' => -32,
    '!^remove-form-representations:!' => -33,
    '!^remove-form-grammatical-features:!' => -34,
    '!^remove-sense:!' => -35,
    '!^update-sense-glosses:!' => -36,
    '!^set-sense-glosses:!' => -36,
    '!^update-sense-elements:!' => -36,
    '!^remove-sense-glosses:!' => -37,
    '!^wbremovequalifiers-remove:!' => -38,
    '!^wbeditentity-create-lexeme!' => -39,
    '!^wblmergelexemes-from:!' => -40,
    '!^wbsetclaim-update-rank!' => -41,
    '!^entityschema-summary-newschema-nolabel!' => -42,
    '!^entityschema-summary-update !' => -43,
    '!^entityschema-summary-update-schema-text!' => -44,
    '!^entityschema-summary-update-schema-namebadge!' => -45,
    '!^entityschema-summary-update-schema-label!' => -46,
    '!^entityschema-summary-update-schema-description!' => -47,
    '!^entityschema-summary-update-schema-aliases!' => -48,
    '!^entityschema-summary-undo!' => -49,
    '!^entityschema-summary-restore!' => -50
];

DEFINE('MONTHLY_INCREMENT', 0x100000000);
DEFINE('GRANDTOTAL_MASK', MONTHLY_INCREMENT - 1);

DEFINE('EVENT_ENTITY', 1); // string (revision)
DEFINE('EVENT_TYPE', 2); // string (create)
DEFINE('EVENT_TIMESTAMP', 3); // string
DEFINE('EVENT_COMMENT', 4); // string
DEFINE('EVENT_USER_TEXT', 8); // string
DEFINE('EVENT_USER_IS_PERMANENT', 20); // boolean
DEFINE('REVISION_IS_IDENTITY_REVERTED', 70); // boolean
DEFINE('REVISION_TAGS', 75); // array<string>

/* wbsetlabel-add:1|he */
/* wbsetdescription-add:1|yo */
/* wbsetaliases-add:1|sv */
/* wbsetsitelink-add:1|nlwikinews */

$prevmonth = date('Y-m', strtotime('-1 month'));

$hndl = fopen('changetags.tsv', 'r');
$tool_list = [];

while (! feof($hndl)) {
    $buffer = rtrim(fgets($hndl), "\n");
    if (empty($buffer)) continue;
    $parts = explode("\t", $buffer);
    
    $tool_list[$parts[1]] = $parts[0];
}

fclose($hndl);

$hndl = fopen('php://stdin', 'r');

while (! feof($hndl)) {
    $buffer = rtrim(fgets($hndl), "\n");
	if (empty($buffer)) continue;
	$buffer = explode("\t", $buffer);
	
	if ($buffer[EVENT_ENTITY] != 'revision') continue;
	if ($buffer[EVENT_TYPE] != 'create') continue;
	if ($buffer[REVISION_IS_IDENTITY_REVERTED] == 'true') continue;
	
	preg_match('!^(\d{4}-\d{2})(-\d{2})!', $buffer[EVENT_TIMESTAMP], $matches);
	$timestamp = $matches[1];
	$fulltimestamp = $matches[1] . $matches[2];
	
	$username = $buffer[EVENT_USER_TEXT];
	if ($buffer[EVENT_USER_IS_PERMANENT] != 'true') $username = ''; // anonymous edit
	
	if (! preg_match('!^/\\* ([^\n]+)!', $buffer[EVENT_COMMENT], $matches)) continue;
	
    if (++$count % 10000000 == 0) echo "Processed " . number_format($count) . "\n";
	$comment = $matches[1];

	foreach ($edittypes as $edittype => $typevalue) {
	    if (preg_match($edittype, $comment)) {
			if ($typevalue === 0) {
			    if (! preg_match('!\\[\\[Property:P(\d+)!', $comment, $matches)){
			        if (! preg_match('!^wbcreateclaim:1 \\*/ p(\d+)!', $comment, $matches)) break;
			    }

				$typevalue = $matches[1];
				
				if (! isset($propmeta[$typevalue])) $propmeta[$typevalue] = ['f' => '9999-99-99', 'l' => '0000-00-00', 'uc' => 0];
				if ($fulltimestamp < $propmeta[$typevalue]['f']) $propmeta[$typevalue]['f'] = $fulltimestamp;
				if ($fulltimestamp > $propmeta[$typevalue]['l']) $propmeta[$typevalue]['l'] = $fulltimestamp;
			}

			$key = "a$typevalue"; // don't want a numeric key

			if (! isset($edits[$username])) $edits[$username] = [];
			if (! isset($edits[$username][$key])) $edits[$username][$key] = 0; // grand total lower 32 bits, month total upper 32 bits

			$multiplier = 1;

			if ($typevalue === -3 || $typevalue === -22 || $typevalue === -27) { // can have multiple alias changes per edit
			    preg_match('!^([a-z\-]+)\s*(:\s*(.*?)\s*)?\\*/!', $comment, $matches);

			    $args = isset($matches[3]) ? explode('|', $matches[3]) : [];

			    if (isset($args[0])) {
			        $multiplier = intval($args[0]);
			    }
			}

			$edits[$username][$key] += $multiplier;

			if ($timestamp == $prevmonth) {
			    $edits[$username][$key] += (MONTHLY_INCREMENT * $multiplier);
			}

			// Lang
			
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
			        if (! isset($langs[$lang][$username])) $langs[$lang][$username] = 0;
			        $langs[$lang][$username] += $multiplier;
			        if ($timestamp == $prevmonth) $langs[$lang][$username] += (MONTHLY_INCREMENT * $multiplier);
			    }
			}
			
			// Tool
			$tags = explode(',', $buffer[REVISION_TAGS]);
			
			foreach ($tags as $tag) {
			    if (isset($tool_list[$tag])) $toolid = $tool_list[$tag];
			    else $toolid = 0;
			    
			    if (! isset($tools[$username])) $tools[$username] = [];
			    if (! isset($tools[$username][$toolid])) $tools[$username][$toolid] = 0; // grand total lower 32 bits, month total upper 32 bits
			    
			    $tools[$username][$toolid] += 1;
			    
			    if ($timestamp == $prevmonth) {
			        $tools[$username][$toolid] += MONTHLY_INCREMENT;
			    }
			}

			break;
		}
	}
}

echo "Processed " . number_format($count) . "\n";

fclose($hndl);
$hndl = fopen('navelgazer.tsv', 'w');

foreach ($edits as $username => $totals) {
    foreach ($totals as $key => $total) {
        $key = substr($key, 1);
        $grandtotal = $total & GRANDTOTAL_MASK;
        $monthtotal = $total >> 32;
        $username = str_replace('\\', '\\\\', $username);
        fwrite($hndl, "$username\t$key\t$grandtotal\t$monthtotal\n");
    }
}

fclose($hndl);

$hndl = fopen('navelgazerlang.tsv', 'w');

foreach ($langs as $lang => $totals) {
    foreach ($totals as $username => $total) {
        $grandtotal = $total & GRANDTOTAL_MASK;
        $monthtotal = $total >> 32;
        $username = str_replace('\\', '\\\\', $username);
        fwrite($hndl, "$lang\t$username\t$grandtotal\t$monthtotal\n");
    }
}

fclose($hndl);

$hndl = fopen('navelgazertag.tsv', 'w');

foreach ($tools as $username => $totals) {
    foreach ($totals as $toolid => $total) {
        $grandtotal = $total & GRANDTOTAL_MASK;
        $monthtotal = $total >> 32;
        $username = str_replace('\\', '\\\\', $username);
        fwrite($hndl, "$toolid\t$username\t$grandtotal\t$monthtotal\n");
    }
}

fclose($hndl);

// Get use counts

$rdfdata = _retrievePropertyCounts();

if ($rdfdata === false) {
    echo "fallback to prop count pages\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WMFLabs tools.bambots');
    curl_setopt($ch, CURLOPT_URL, 'https://www.wikidata.org/w/index.php?title=Template:Number_of_main_statements_by_property&action=raw&ctype=text');
    
    $usecounts = curl_exec($ch);
    
    preg_match_all('!(\d+)\s*=\s*(\d+)!', $usecounts, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $propid = $match[1];
        
        if (isset($propmeta[$propid])) {
            $propmeta[$propid]['uc'] = $match[2];
        }
    }
} else {
    preg_match_all("!http://www.wikidata.org/prop/P(\d+).*?>(\d+)</triples>!s", $rdfdata, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $propid = $match[1];
        
        if (isset($propmeta[$propid])) {
            if (! isset($propmeta[$propid]['uc'])) $propmeta[$propid]['uc'] = 0;
            $propmeta[$propid]['uc'] += $match[2];
        }
    }
    
}

$hndl = fopen('navelgazerpropmeta.tsv', 'w');

foreach ($propmeta as $propid => $values) {
        fwrite($hndl, "$propid\t{$values['f']}\t{$values['l']}\t{$values['uc']}\n");
}

fclose($hndl);
exit;

/**
 * Retrieve property usage counts from both SPARQL graphs
 */
function _retrievePropertyCounts()
{
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WMFLabs tools.bambots');
    curl_setopt($ch, CURLOPT_URL, 'https://query-main.wikidata.org/sparql');
    
    $rdfdata = curl_exec($ch);
    
    $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    if ($rdfdata === false || $responseCode != 200) return false;
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WMFLabs tools.bambots');
    curl_setopt($ch, CURLOPT_URL, 'https://query-scholarly.wikidata.org/sparql');
    
    $rdfdata2 = curl_exec($ch);
    
    $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    
    if ($rdfdata2 === false || $responseCode != 200) return false;
    
    return $rdfdata . $rdfdata2;
}
