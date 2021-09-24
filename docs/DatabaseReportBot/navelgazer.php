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
$matches = [];
$edittypes = [
	'!^wbsetclaim-create:!' => 0,
	'!^wbcreateclaim!' => 0, // no : because matches 5 actions
	'!^wbsetlabel-add:!' => -1,
	'!^wbsetdescription-add:!' => -2,
	'!^wbsetaliases-add:!' => -3,
    '!^wbsetaliases-set:!' => -3,
    '!^wbsetsitelink-add:!' => -4,
	'!^wbmergeitems-from:!' => -5,
    '!^add-form:!' => -6,
    '!^wbeditentity-create-form:!' => -6,
    '!^add-form-representations:!' => -7,
    '!^add-form-grammatical-features:!' => -8,
    '!^add-sense:!' => -9,
    '!^wbeditentity-create-sense:!' => -9,
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
    '!^wbsetsitelink-set:!' => -25,
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
    '!^wbeditentity-create-lexeme:!' => -39,
    '!^wblmergelexemes-from:!' => -40
];

DEFINE('MONTHLY_INCREMENT', 0x100000000);
DEFINE('GRANDTOTAL_MASK', MONTHLY_INCREMENT - 1);

/* wbsetlabel-add:1|he */
/* wbsetdescription-add:1|yo */
/* wbsetaliases-add:1|sv */
/* wbsetsitelink-add:1|nlwikinews */

if ($argc > 1) {
    $action = $argv[1];
    switch ($action) {
        case 'parseMetaHistory':
            parseMetaHistory();
            exit;
            break;
    }
}

$prevmonth = date('Y-m', strtotime('-1 month'));

$hndl = fopen('php://stdin', 'r');
$revhndl = fopen('navelgazerrev.tsv', 'w');

while (! feof($hndl)) {
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = substr($buffer, 24); // strip /mediawiki/page/revision

	if (preg_match('!^/id=(\d+)!', $buffer, $matches)) {
	    $revid = $matches[1];
	} elseif (preg_match('!^/contributor/ip=!', $buffer)) {
	    $username = false;
	} elseif (preg_match('!^/contributor/@deleted!', $buffer)) {
	    $username = false;
	} elseif (preg_match('!^/contributor/username=([^\n]+)!', $buffer, $matches)) {
		$username = $matches[1];
	} elseif (preg_match('!^/timestamp=(\d{4}-\d{2})!', $buffer, $matches)) {
		$timestamp = $matches[1];
	} elseif (preg_match('!^/comment=/\\* ([^\n]+)!', $buffer, $matches)) {
	    if (++$count % 10000000 == 0) echo "Processed " . number_format($count) . "\n";
	    if ($username === false) $username = ''; // anonymous edit
		$comment = $matches[1];

		foreach ($edittypes as $edittype => $typevalue) {
		    if (preg_match($edittype, $comment)) {
				if ($typevalue === 0) {
				    if (! preg_match('!\\[\\[Property:P(\d+)!', $comment, $matches)){
				        if (! preg_match('!^wbcreateclaim:1 \\*/ p(\d+)!', $comment, $matches)) break;
				    }

					$typevalue = $matches[1];
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

				break;
			}
		}

		if (! empty ($username) && strcasecmp(substr($username, -3), 'bot') != 0) {
		    if ($timestamp == $prevmonth) fwrite($revhndl, "$revid\t$username\n");
		}
	}

}

echo "Processed " . number_format($count) . "\n";

fclose($revhndl);
fclose($hndl);
$hndl = fopen('navelgazer.tsv', 'w');

foreach ($edits as $username => $totals) {
    foreach ($totals as $key => $total) {
        $key = substr($key, 1);
        $grandtotal = $total & GRANDTOTAL_MASK;
        $monthtotal = $total >> 32;
        fwrite($hndl, "$username\t$key\t$grandtotal\t$monthtotal\n");
    }
}

fclose($hndl);

$hndl = fopen('navelgazerlang.tsv', 'w');

foreach ($langs as $lang => $totals) {
    foreach ($totals as $username => $total) {
        $grandtotal = $total & GRANDTOTAL_MASK;
        $monthtotal = $total >> 32;
        fwrite($hndl, "$lang\t$username\t$grandtotal\t$monthtotal\n");
    }
}

fclose($hndl);
exit;

/**
 * Parse revision change text for non-standard comments.
 */
function parseMetaHistory()
{
    global $edittypes;
    include 'Swaggest/JsonDiff/JsonDiff.php';
    
    spl_autoload_register(function ($class) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    });
    
    $hndl = fopen('navelgazernoncomment.tsv', 'r');
    $edits = [];
        
    while (! feof($hndl)) {
        $buffer = fgets($hndl);
        $buffer = rtrim($buffer);
        if (empty($buffer)) continue;
        list($username,$key,$grandtotal) = explode("\t", $buffer);
        if (! isset($edits[$username])) $edits[$username] = [];
        $edits[$username][$key] = (int)$grandtotal;
    }
    
    echo "Loaded previous totals count: " . number_format(count($edits)) . "\n";
    
    fclose($hndl);
    $hndl = fopen('php://stdin', 'r');
    $count = 0;
    
    while (! feof($hndl)) {
        $buffer = fgets($hndl);
        $buffer = rtrim($buffer);
        if (empty($buffer)) continue;
        
        if (strpos($buffer, '/mediawiki/page/revision') === 0)
            $buffer = substr($buffer, 24); // strip /mediawiki/page/revision
        else
            $buffer = substr($buffer, 15); // strip /mediawiki/page
        
        if (preg_match('!^/title=(.*)!', $buffer, $matches)) {
            $prev_json = '{}';
            if (preg_match('!^Q\d+$!', $matches[1])) $skip_text = false;
            else $skip_text = true;
        } elseif (preg_match('!^/contributor/ip=!', $buffer)) {
             $username = false;
        } elseif (preg_match('!^/contributor/@deleted!', $buffer)) {
            $username = false;
        } elseif (preg_match('!^/contributor/username=(.*)!', $buffer, $matches)) {
            $username = $matches[1];
        } elseif (preg_match('!^/comment=/\\* (.*)!', $buffer, $matches)) {
            $comment = $matches[1];
            
            foreach ($edittypes as $edittype => $typevalue) {
                if (preg_match($edittype, $comment)) {
                    $skip_text = true;
                    break; // skip recognized comment format
                }
            }
        } elseif (preg_match('!^/text=(.*)!', $buffer, $matches)) {
            if ($skip_text) continue;
            if ($username === false) $username = ''; // anonymous edit
            
            $r = new Swaggest\JsonDiff\JsonDiff(
                json_decode($prev_json),
                json_decode($matches[1]),
                Swaggest\JsonDiff\JsonDiff::SKIP_JSON_MERGE_PATCH + Swaggest\JsonDiff\JsonDiff::SKIP_JSON_PATCH
                );
            
            $paths = $r->getAddedPaths();
            
            foreach ($paths as $path) {
                if (preg_match('!^/claims/P(\d+)$!', $path, $matches2)) {
                    $key = $matches2[1];
                    if (! isset($edits[$username])) $edits[$username] = [];
                    if (! isset($edits[$username][$key])) $edits[$username][$key] = 0;
                    $edits[$username][$key] += 1;
                    ++$count;
                    if ($count == 1000) break 2;
                }
            }

            $prev_json = $matches[1];
        }
    }
    
    echo " Processed " . number_format($count) . "\n";
    
    fclose($hndl);
    $hndl = fopen('navelgazernoncomment.tsv', 'w');
    
    foreach ($edits as $username => $totals) {
        foreach ($totals as $key => $total) {
            fwrite($hndl, "$username\t$key\t$total\n");
        }
    }
    
    fclose($hndl);

    /*
    $originalJson = '{"type":"item","id":"Q518475","labels":{"hu":{"language":"hu","value":"2011\u20132012-es f\u00e9rfi EHF-kupa"},"de":{"language":"de","value":"EHF-Pokal 2011\/12"},"en":{"language":"en","value":"2011\u201312 EHF Cup"},"fr":{"language":"fr","value":"Coupe EHF 2011-2012"},"it":{"language":"it","value":"EHF Cup 2011-2012"}},"descriptions":{"fr":{"language":"fr","value":"comp\u00e9tition de handball"}},"aliases":[],"claims":[],"sitelinks":{"huwiki":{"site":"huwiki","title":"2011\u20132012-es f\u00e9rfi EHF-kupa","badges":[]},"dewiki":{"site":"dewiki","title":"EHF-Pokal 2011\/12","badges":[]},"enwiki":{"site":"enwiki","title":"2011\u201312 EHF Cup","badges":[]},"frwiki":{"site":"frwiki","title":"Coupe EHF 2011-2012","badges":[]},"itwiki":{"site":"itwiki","title":"EHF Cup 2011-2012 (pallamano maschile)","badges":[]}}}';
    
    $newJson = '{"type":"item","id":"Q518475","labels":{"hu":{"language":"hu","value":"2011\u20132012-es f\u00e9rfi EHF-kupa"},"de":{"language":"de","value":"EHF-Pokal 2011\/12"},"en":{"language":"en","value":"2011\u201312 EHF Cup"},"fr":{"language":"fr","value":"Coupe EHF 2011-2012"},"it":{"language":"it","value":"EHF Cup 2011-2012"}},"descriptions":{"fr":{"language":"fr","value":"comp\u00e9tition de handball"}},"aliases":[],"claims":{"P646":[{"mainsnak":{"snaktype":"value","property":"P646","hash":"e7b27072bc4563912164b62c8af54db229c371ba","datavalue":{"value":"\/m\/0j289ln","type":"string"}},"type":"statement","id":"Q518475$F3973CD0-B8E3-4078-BF7E-A54A045D0DB3","rank":"normal"}]},"sitelinks":{"huwiki":{"site":"huwiki","title":"2011\u20132012-es f\u00e9rfi EHF-kupa","badges":[]},"dewiki":{"site":"dewiki","title":"EHF-Pokal 2011\/12","badges":[]},"enwiki":{"site":"enwiki","title":"2011\u201312 EHF Cup","badges":[]},"frwiki":{"site":"frwiki","title":"Coupe EHF 2011-2012","badges":[]},"itwiki":{"site":"itwiki","title":"EHF Cup 2011-2012 (pallamano maschile)","badges":[]}}}';
        
    $r = new Swaggest\JsonDiff\JsonDiff(
        json_decode($originalJson),
        json_decode($newJson),
        Swaggest\JsonDiff\JsonDiff::SKIP_JSON_MERGE_PATCH + Swaggest\JsonDiff\JsonDiff::SKIP_JSON_PATCH
        );
    
    print_r($r->getAddedPaths());
    */
}