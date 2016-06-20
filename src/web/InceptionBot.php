<?php
/**
 Copyright 2013 Myers Enterprises II

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

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Config;
use com_brucemyers\InceptionBot\RuleSet;
use com_brucemyers\InceptionBot\RuleSetProcessor;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'InceptionBot';

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$action = @ $_REQUEST['action'];
$rulename = @ $_REQUEST['rulename'];
$testpage = @ $_REQUEST['testpage'];

switch ($action) {
	case 'test':
		rule_test($rulename, $testpage);
		break;

	default:
		rule_display($rulename, $testpage);
		break;
}

/**
 * Display the input page with optional results
 *
 * @param $rulename string New page search rule name
 * @param $testpage string (Optional) Page to test the rules against
 * @param $results string (Optional) Test results to display
 */
function rule_display($rulename, $testpage, $results = null)
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>New Page Search Rule Test</title>
	    <style>
	        li {
                margin-bottom: 5px;
            }
	    </style>
	</head>
	<body>
        <h2>New Page Search Rule Test</h2>
        <form action="InceptionBot.php" ><table class="form">
        <tr><td><b>Rule Page Name</b> <input name="action" type="hidden" value="test" /><input name="rulename" type="text" size="10" id="testfield1" value="<?php echo $rulename ?>" /> ex. Architecture</td></tr>
        <tr><td><b>Test Page Name (optional)</b> <input name="testpage" type="text" size="15" value="<?php echo $testpage ?>" /> ex. Grouted roof<?php echo ''?></td></tr>
        <tr><td><input type="submit" value="Submit" /></td></tr>
        </table></form>

        <script type="text/javascript">
            if (document.getElementById) {
                document.getElementById('testfield1').focus();
            }
        </script>
    <?php

    if (! empty($results)) {
        echo '<h2>Results</h2>';
        echo $results;
    }

    ?></body></html><?php
}

/**
 * Test new page rules
 *
 * @param $rulename string New page search rule name
 * @param $testpage string (Optional) Page to test the rules against
 */
function rule_test($rulename, $testpage)
{
    if (empty($rulename)) {
        rule_display($rulename, $testpage);
        return;
    }
    $prefix = 'User:AlexNewArtBot/';
    $fullrulename = $rulename;
    if (strpos($fullrulename, $prefix) === false) $fullrulename = $prefix . $rulename;

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);

    $ruledata = $wiki->getPage($fullrulename);

    if (empty($ruledata)) {
        $results = 'Rule page ' . htmlentities($rulename, ENT_COMPAT, 'UTF-8') . ' not found.';
        rule_display($rulename, $testpage, $results);
        return;
    }

    $results = '';
    $ruleset = new RuleSet($rulename, $ruledata);
    $rulecnt = count($ruleset->rules);
    $errorcnt = count($ruleset->errors);
    $results .= "Pattern count: $rulecnt Error count: $errorcnt<br />";
    if ($errorcnt) {
        $results .= '<b>Errors</b><br />';
        foreach ($ruleset->errors as $error) {
            $results .= htmlentities($error, ENT_COMPAT, 'UTF-8') . '<br />';
        }
    }

    if (! empty($testpage)) {
        $testpagedata = $wiki->getPage($testpage);
        if (empty($testpagedata)) {
            $results .= 'Test page ' . htmlentities($testpage, ENT_COMPAT, 'UTF-8') . ' not found.<br />';
        } else {
            $processor = new RuleSetProcessor($ruleset);
            $scores = $processor->processData($testpagedata, $testpage);

            $totscore = 0;
            foreach ($scores as $score) {
                $totscore += $score['score'];
            }

            $threshold = $ruleset->minScore;

            $results .= "<br /><b>Scoring</b> Total score: $totscore Threshold: $threshold<br />";
            if (empty($scores)) {
                $results .= 'No pattern matches.<br />';
            } else {
                $results .= '<ul>';
                foreach ($scores as $score) {
                    $results .= '<li>Score: ' . $score['score'] . ', pattern: ' . htmlentities($score['regex'], ENT_COMPAT, 'UTF-8') . '</li>';
                }
                $results .= '</ul>';
            }
        }
    }

    rule_display($rulename, $testpage, $results);
}