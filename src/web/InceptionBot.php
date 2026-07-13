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
use com_brucemyers\InceptionBot\OresDraftTopicLister;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'InceptionBot';

//error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
//ini_set("display_errors", 1);

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$action = @ $_REQUEST['action'];
$rulename = @ $_REQUEST['rulename'];
$testpage = @ $_REQUEST['testpage'];
$custom_rules = @ $_REQUEST['custom_rules'];

switch ($action) {
	case 'test':
	    rule_test($rulename, $testpage, $custom_rules);
		break;

	default:
	    rule_display($rulename, $testpage, $custom_rules);
		break;
}

/**
 * Display the input page with optional results
 *
 * @param $rulename string New page search rule name
 * @param $testpage string (Optional) Page to test the rules against
 * @param $results string (Optional) Test results to display
 */
function rule_display($rulename, $testpage, $custom_rules, $results = null)
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
        <meta name="color-scheme" content="light dark" />
	    <title>New Page Search Rule Test</title>
	    <style>
	        li {
                margin-bottom: 5px;
            }
	    </style>
	</head>
	<body>
        <h2>New Page Search Rule Test</h2>
        <form action="InceptionBot.php" method="post"><table class="form">
        <tr><td><b>Rule Page Name</b> <input name="action" type="hidden" value="test" /><input name="rulename" type="text" size="10" id="testfield1" value="<?php echo $rulename ?>" /> ex. Architecture</td></tr>
        <tr><td><b>Test Page Name (optional)</b> <input name="testpage" type="text" size="15" value="<?php echo $testpage ?>" /> ex. Grouted roof</td></tr>
        <tr><td><b>Custom Rules (optional)</b> <textarea name="custom_rules" rows="5" cols="50"><?php echo $custom_rules ?></textarea> Rule Page Name is ignored</td></tr>
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
function rule_test($rulename, $testpage, $custom_rules)
{
    if (empty($rulename) && empty($custom_rules)) {
        rule_display($rulename, $testpage, $custom_rules);
        return;
    }
    
    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);
    
    if (! empty($custom_rules)) {
        $ruledata = $custom_rules;
    } else {
        $testpage = str_replace('_', ' ', $testpage);
        $prefix = 'User:AlexNewArtBot/';
        $fullrulename = $rulename;
        if (strpos($fullrulename, $prefix) === false) $fullrulename = $prefix . $rulename;
        
        $ruledata = $wiki->getPage($fullrulename);
    }

    if (empty($ruledata)) {
        $results = 'Rule page ' . htmlentities($rulename, ENT_COMPAT, 'UTF-8') . ' not found.';
        rule_display($rulename, $testpage, $custom_rules, $results);
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

            $lister = new OresDraftTopicLister($wiki);
            $oresscores = $lister->getScores(array($testpage));
            $oresscore = array();
            if (isset($oresscores[$testpage])) $oresscore = $oresscores[$testpage];

            $scores = $processor->processData($testpagedata, $testpage, $oresscore);

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

    rule_display($rulename, $testpage, $custom_rules, $results);
}