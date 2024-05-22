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
 
 https://github.com/wikimedia/Wikibase/blob/master/lib/i18n/en.json
 */

$count = 0;
$edits = [];
$langs = [];
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
            
        case 'testMetaHistory':
            testMetaHistory();
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
	} elseif (preg_match('!^/contributor/username=~2!', $buffer)) { // temporary user
	    $username = false;
	} elseif (preg_match('!^/contributor/username=([^\n]+)!', $buffer, $matches)) {
		$username = $matches[1];
	} elseif (preg_match('!^/timestamp=(\d{4}-\d{2})(-\d{2})!', $buffer, $matches)) {
		$timestamp = $matches[1];
		$fulltimestamp = $matches[1] . $matches[2];
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

// Get use counts
$ch = curl_init();

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Windows / Firefox 99: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:65.0) Gecko/20100101 Firefox/99.0');
curl_setopt($ch, CURLOPT_URL, 'https://query.wikidata.org/sparql');

$rdfdata = curl_exec($ch);

$responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

if ($rdfdata === false || $responseCode != 200) {
    echo "fallback to prop count pages\n";
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Windows / Firefox 99: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:65.0) Gecko/20100101 Firefox/99.0');
    curl_setopt($ch, CURLOPT_URL, 'https://www.wikidata.org/wiki/Template:Number_of_main_statements_by_property');
    
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
            $propmeta[$propid]['uc'] = $match[2];
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
            if (preg_match('!^Q\d+$!', $matches[1])) $skip_title = false;
            else $skip_title = true;
        } elseif (preg_match('!^/contributor/ip=!', $buffer)) {
             $username = false;
        } elseif (preg_match('!^/contributor/@deleted!', $buffer)) {
            $username = false;
        } elseif (preg_match('!^/contributor/username=(.*)!', $buffer, $matches)) {
            $username = $matches[1];
        } elseif (preg_match('!^/comment=/\\* (.*)!', $buffer, $matches)) {
            $comment = $matches[1];
            $skip_text = false;
            
            foreach ($edittypes as $edittype => $typevalue) {
                if (preg_match($edittype, $comment)) {
                    if ($typevalue === 0) {
                        if (! preg_match('!\\[\\[Property:P(\d+)!', $comment, $matches)){
                            if (! preg_match('!^wbcreateclaim:1 \\*/ p(\d+)!', $comment, $matches)) {
                                break;
                            }
                        }
                    }
                    
                    if ($typevalue !== -18 && $typevalue !== -19) $skip_text = true;
                    break; // skip recognized comment format
                }
            }
        } elseif (preg_match('!^/text=(.*)!', $buffer, $matches)) {
            if ($skip_title || $skip_text) {
                $prev_json = $matches[1];
                continue;
            }
            
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
}

function testMetaHistory() {
    include 'Swaggest/JsonDiff/JsonDiff.php';
    
    spl_autoload_register(function ($class) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    });
        
    $originalJson = '{"entities":{"Q1024969":{"pageid":974286,"ns":0,"title":"Q1024969","lastrevid":239710767,"modified":"2015-08-10T02:54:25Z","type":"item","id":"Q1024969","labels":{"de":{"language":"de","value":"Cabrillo College"},"en":{"language":"en","value":"Cabrillo College"},"ja":{"language":"ja","value":"\u30ab\u30d6\u30ea\u30aa\u30ab\u30ec\u30c3\u30b8"},"nl":{"language":"nl","value":"Cabrillo College"},"fr":{"language":"fr","value":"Cabrillo College"}},"descriptions":{},"aliases":{"de":[{"language":"de","value":"Cabrillo Community College"}]},"claims":{"P625":[{"mainsnak":{"snaktype":"value","property":"P625","hash":"520576a19ccc52277cd45ddb3a542ec7eba4f926","datavalue":{"value":{"latitude":36.9883,"longitude":-121.925,"altitude":null,"precision":0.0001,"globe":"http://www.wikidata.org/entity/Q2"},"type":"globecoordinate"},"datatype":"globe-coordinate"},"type":"statement","id":"q1024969$60307310-1BE8-4CA9-AD52-DE7D2A5C0224","rank":"normal","references":[{"hash":"fa278ebfc458360e5aed63d5058cca83c46134f1","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"e4f6d9441d0600513c4533c672b5ab472dc73694","datavalue":{"value":{"entity-type":"item","numeric-id":328,"id":"Q328"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P17":[{"mainsnak":{"snaktype":"value","property":"P17","hash":"be4c6eafa2984964f04be85667263f5642ba1a72","datavalue":{"value":{"entity-type":"item","numeric-id":30,"id":"Q30"},"type":"wikibase-entityid"},"datatype":"wikibase-item"},"type":"statement","id":"q1024969$E8D55822-0CB0-44BC-900E-E44122A1ECB2","rank":"normal","references":[{"hash":"fa278ebfc458360e5aed63d5058cca83c46134f1","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"e4f6d9441d0600513c4533c672b5ab472dc73694","datavalue":{"value":{"entity-type":"item","numeric-id":328,"id":"Q328"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P131":[{"mainsnak":{"snaktype":"value","property":"P131","hash":"5f9fc09b78eb5ba7a7baeaa87b2e1d398bcd1ed3","datavalue":{"value":{"entity-type":"item","numeric-id":99,"id":"Q99"},"type":"wikibase-entityid"},"datatype":"wikibase-item"},"type":"statement","id":"q1024969$4FFE9E25-A7B5-46CA-8E71-EC72A1893AC0","rank":"normal","references":[{"hash":"fa278ebfc458360e5aed63d5058cca83c46134f1","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"e4f6d9441d0600513c4533c672b5ab472dc73694","datavalue":{"value":{"entity-type":"item","numeric-id":328,"id":"Q328"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]},{"mainsnak":{"snaktype":"value","property":"P131","hash":"ec364bac8df0e128719ab8cc89c311e2c4d76aa4","datavalue":{"value":{"entity-type":"item","numeric-id":622269,"id":"Q622269"},"type":"wikibase-entityid"},"datatype":"wikibase-item"},"type":"statement","id":"Q1024969$2f5479e9-4252-a894-f1cf-af5203c2d998","rank":"normal"}],"P1566":[{"mainsnak":{"snaktype":"value","property":"P1566","hash":"fbccbcbf6b3a23c0999a1bbdb49c601a17831f16","datavalue":{"value":"5332433","type":"string"},"datatype":"external-id"},"type":"statement","id":"Q1024969$EC5B95C9-EA93-45D0-BC56-5DC4E6CA90DC","rank":"normal","references":[{"hash":"64133510dcdf15e7943de41e4835c673fc5d6fe4","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"3439bea208036ec33ec3fba8245410df3efb8044","datavalue":{"value":{"entity-type":"item","numeric-id":830106,"id":"Q830106"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P18":[{"mainsnak":{"snaktype":"value","property":"P18","hash":"e527bc636161ae5a9d5cbd4f18b48d3c127eed1c","datavalue":{"value":"Cabrillo College2.jpg","type":"string"},"datatype":"commonsMedia"},"type":"statement","id":"Q1024969$316051D7-D7CC-4F6D-81D0-CB0BF9E07A2E","rank":"normal"}],"P214":[{"mainsnak":{"snaktype":"value","property":"P214","hash":"5c17422648cb1101d7fbf31629f5b7d410d24e95","datavalue":{"value":"127130543","type":"string"},"datatype":"external-id"},"type":"statement","id":"Q1024969$68E73BA4-BE53-4102-837D-1BD83AC3959E","rank":"normal","references":[{"hash":"9a24f7c0208b05d6be97077d855671d1dfdbc0dd","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"d38375ffe6fe142663ff55cd783aa4df4301d83d","datavalue":{"value":{"entity-type":"item","numeric-id":48183,"id":"Q48183"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P244":[{"mainsnak":{"snaktype":"value","property":"P244","hash":"f92d4b3ca3b2cca669fef6345cb616e18bf9592e","datavalue":{"value":"nr99025965","type":"string"},"datatype":"external-id"},"type":"statement","id":"Q1024969$32C47B33-1833-4A75-814D-9E4805EC1A9F","rank":"normal","references":[{"hash":"9a24f7c0208b05d6be97077d855671d1dfdbc0dd","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"d38375ffe6fe142663ff55cd783aa4df4301d83d","datavalue":{"value":{"entity-type":"item","numeric-id":48183,"id":"Q48183"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}]},"sitelinks":{"dewiki":{"site":"dewiki","title":"Cabrillo College","badges":[],"url":"https://de.wikipedia.org/wiki/Cabrillo_College"},"enwiki":{"site":"enwiki","title":"Cabrillo College","badges":[],"url":"https://en.wikipedia.org/wiki/Cabrillo_College"},"jawiki":{"site":"jawiki","title":"\u30ab\u30d6\u30ea\u30aa\u30ab\u30ec\u30c3\u30b8","badges":[],"url":"https://ja.wikipedia.org/wiki/%E3%82%AB%E3%83%96%E3%83%AA%E3%82%AA%E3%82%AB%E3%83%AC%E3%83%83%E3%82%B8"}}}}}';
    
    $newJson = '{"entities":{"Q1024969":{"pageid":974286,"ns":0,"title":"Q1024969","lastrevid":239710771,"modified":"2015-08-10T02:54:35Z","type":"item","id":"Q1024969","labels":{"de":{"language":"de","value":"Cabrillo College"},"en":{"language":"en","value":"Cabrillo College"},"ja":{"language":"ja","value":"\u30ab\u30d6\u30ea\u30aa\u30ab\u30ec\u30c3\u30b8"},"nl":{"language":"nl","value":"Cabrillo College"},"fr":{"language":"fr","value":"Cabrillo College"}},"descriptions":{},"aliases":{"de":[{"language":"de","value":"Cabrillo Community College"}]},"claims":{"P625":[{"mainsnak":{"snaktype":"value","property":"P625","hash":"520576a19ccc52277cd45ddb3a542ec7eba4f926","datavalue":{"value":{"latitude":36.9883,"longitude":-121.925,"altitude":null,"precision":0.0001,"globe":"http://www.wikidata.org/entity/Q2"},"type":"globecoordinate"},"datatype":"globe-coordinate"},"type":"statement","id":"q1024969$60307310-1BE8-4CA9-AD52-DE7D2A5C0224","rank":"normal","references":[{"hash":"fa278ebfc458360e5aed63d5058cca83c46134f1","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"e4f6d9441d0600513c4533c672b5ab472dc73694","datavalue":{"value":{"entity-type":"item","numeric-id":328,"id":"Q328"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P17":[{"mainsnak":{"snaktype":"value","property":"P17","hash":"be4c6eafa2984964f04be85667263f5642ba1a72","datavalue":{"value":{"entity-type":"item","numeric-id":30,"id":"Q30"},"type":"wikibase-entityid"},"datatype":"wikibase-item"},"type":"statement","id":"q1024969$E8D55822-0CB0-44BC-900E-E44122A1ECB2","rank":"normal","references":[{"hash":"fa278ebfc458360e5aed63d5058cca83c46134f1","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"e4f6d9441d0600513c4533c672b5ab472dc73694","datavalue":{"value":{"entity-type":"item","numeric-id":328,"id":"Q328"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P131":[{"mainsnak":{"snaktype":"value","property":"P131","hash":"5f9fc09b78eb5ba7a7baeaa87b2e1d398bcd1ed3","datavalue":{"value":{"entity-type":"item","numeric-id":99,"id":"Q99"},"type":"wikibase-entityid"},"datatype":"wikibase-item"},"type":"statement","id":"q1024969$4FFE9E25-A7B5-46CA-8E71-EC72A1893AC0","rank":"normal","references":[{"hash":"fa278ebfc458360e5aed63d5058cca83c46134f1","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"e4f6d9441d0600513c4533c672b5ab472dc73694","datavalue":{"value":{"entity-type":"item","numeric-id":328,"id":"Q328"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]},{"mainsnak":{"snaktype":"value","property":"P131","hash":"ec364bac8df0e128719ab8cc89c311e2c4d76aa4","datavalue":{"value":{"entity-type":"item","numeric-id":622269,"id":"Q622269"},"type":"wikibase-entityid"},"datatype":"wikibase-item"},"type":"statement","id":"Q1024969$2f5479e9-4252-a894-f1cf-af5203c2d998","rank":"normal"}],"P1566":[{"mainsnak":{"snaktype":"value","property":"P1566","hash":"fbccbcbf6b3a23c0999a1bbdb49c601a17831f16","datavalue":{"value":"5332433","type":"string"},"datatype":"external-id"},"type":"statement","id":"Q1024969$EC5B95C9-EA93-45D0-BC56-5DC4E6CA90DC","rank":"normal","references":[{"hash":"64133510dcdf15e7943de41e4835c673fc5d6fe4","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"3439bea208036ec33ec3fba8245410df3efb8044","datavalue":{"value":{"entity-type":"item","numeric-id":830106,"id":"Q830106"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P18":[{"mainsnak":{"snaktype":"value","property":"P18","hash":"e527bc636161ae5a9d5cbd4f18b48d3c127eed1c","datavalue":{"value":"Cabrillo College2.jpg","type":"string"},"datatype":"commonsMedia"},"type":"statement","id":"Q1024969$316051D7-D7CC-4F6D-81D0-CB0BF9E07A2E","rank":"normal"}],"P214":[{"mainsnak":{"snaktype":"value","property":"P214","hash":"5c17422648cb1101d7fbf31629f5b7d410d24e95","datavalue":{"value":"127130543","type":"string"},"datatype":"external-id"},"type":"statement","id":"Q1024969$68E73BA4-BE53-4102-837D-1BD83AC3959E","rank":"normal","references":[{"hash":"9a24f7c0208b05d6be97077d855671d1dfdbc0dd","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"d38375ffe6fe142663ff55cd783aa4df4301d83d","datavalue":{"value":{"entity-type":"item","numeric-id":48183,"id":"Q48183"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P244":[{"mainsnak":{"snaktype":"value","property":"P244","hash":"f92d4b3ca3b2cca669fef6345cb616e18bf9592e","datavalue":{"value":"nr99025965","type":"string"},"datatype":"external-id"},"type":"statement","id":"Q1024969$32C47B33-1833-4A75-814D-9E4805EC1A9F","rank":"normal","references":[{"hash":"9a24f7c0208b05d6be97077d855671d1dfdbc0dd","snaks":{"P143":[{"snaktype":"value","property":"P143","hash":"d38375ffe6fe142663ff55cd783aa4df4301d83d","datavalue":{"value":{"entity-type":"item","numeric-id":48183,"id":"Q48183"},"type":"wikibase-entityid"},"datatype":"wikibase-item"}]},"snaks-order":["P143"]}]}],"P1771":[{"mainsnak":{"snaktype":"value","property":"P1771","hash":"3d5ce8def0a41d2d1c216ce07f99f1296b1686e5","datavalue":{"value":"110334","type":"string"},"datatype":"external-id"},"type":"statement","id":"Q1024969$4B8EC8BB-51FF-46F2-943D-0E5F12922D86","rank":"normal"}]},"sitelinks":{"dewiki":{"site":"dewiki","title":"Cabrillo College","badges":[],"url":"https://de.wikipedia.org/wiki/Cabrillo_College"},"enwiki":{"site":"enwiki","title":"Cabrillo College","badges":[],"url":"https://en.wikipedia.org/wiki/Cabrillo_College"},"jawiki":{"site":"jawiki","title":"\u30ab\u30d6\u30ea\u30aa\u30ab\u30ec\u30c3\u30b8","badges":[],"url":"https://ja.wikipedia.org/wiki/%E3%82%AB%E3%83%96%E3%83%AA%E3%82%AA%E3%82%AB%E3%83%AC%E3%83%83%E3%82%B8"}}}}}';
    
    $r = new Swaggest\JsonDiff\JsonDiff(
        json_decode($originalJson),
        json_decode($newJson),
        Swaggest\JsonDiff\JsonDiff::SKIP_JSON_MERGE_PATCH + Swaggest\JsonDiff\JsonDiff::SKIP_JSON_PATCH
        );
    
    print_r($r->getAddedPaths());
}
