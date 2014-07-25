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

use com_brucemyers\WPPageListBot\WPPageListBot;
use com_brucemyers\MediaWiki\WikiResultWriter;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\MediaWiki\EmbeddedInLister;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'WPPageListBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activerules = array(
        'WikiProject Oregon' => array(
                        'categories' => array('WikiProject Oregon pages'),
                        'articles' => 'Wikipedia:WikiProject Oregon/Admin',
                        'nonarticles' => 'Wikipedia:WikiProject Oregon/Admin2',
                        'bannertemplate' => 'Wikipedia:WikiProject Oregon/Nav'
        ),
        'WikiProject Michigan' => array(
                        'categories' => array('WikiProject Michigan articles','Michigan road transport articles'),
                        'articles' => 'Wikipedia:WikiProject Michigan/Michigan recent changes',
                        'nonarticles' => '',
                        'bannertemplate' => ''
        ),
    );
//     $activerules = array(
//     	'WikiProject Oregon' => array(
//     		'categories' => array('WikiProject Oregon pages'),
//     		'articles' => 'User:Bamyers99/sandbox/WP Oregon Admin',
//     		'nonarticles' => 'User:Bamyers99/sandbox/WP Oregon Admin2',
//     		'bannertemplate' => 'Wikipedia:WikiProject Oregon/Nav'
//     	),
//     	'WikiProject Michigan' => array(
//     		'categories' => array('WikiProject Michigan articles'),
//     		'articles' => 'User:Bamyers99/sandbox/WP Michigan recent changes',
//     		'nonarticles' => '',
//     		'bannertemplate' => ''
//     	),
//     );

try {
    $ruletype = 'custom'; // 'active', 'custom', 'all'

    $timer = new Timer();
    $timer->start();
    Logger::log("Started");

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);

    if ($argc > 1) {
    	$action = $argv[1];
    	switch ($action) {
    		case 'templateParam':
    			templateParam($wiki, 'Template:United_States_topic', 'prefix', '0|10');
    			exit;
    			break;

    		default:
    			echo 'Unknown action = ' . $action;
    			exit;
    			break;
    	}
    }

    if ($ruletype == 'active') $rules = $activerules;
    elseif ($ruletype== 'custom') $rules = array('WikiProject Michigan' => $activerules['WikiProject Michigan']);
    else {
        $data = $wiki->getpage('User:AlexNewArtBot/Master');
        $rules = $data; // TODO: Parse WPPageListBot page for rules
    }


    //$bot = new WPPageListBot($wiki, $rules, new WikiResultWriter($wiki));
    $bot = new WPPageListBot($wiki, $rules, new FileResultWriter('/home/bruce/temp/tedderbot/'));

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed Time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    Logger::log($ex->getMessage() . "\n" . $ex->getTraceAsString());
}

/**
 * Get a template parameters values
 *
 * @param MediaWiki $wiki
 * @param string $template, include namespace prefix
 * @param string $param
 * @param $namespace string (optional) default = 0, separate multiple with '|'
 */
function templateParam($wiki, $template, $param, $namespace = '0')
{
	$output = '';
	$resultwriter = new FileResultWriter('/home/bruce/temp/tedderbot/');

	// Get the list of pages transcluding the template
    $lister = new EmbeddedInLister($wiki, $template, $namespace);

    $temppages = array();

    while (($pages = $lister->getNextBatch()) !== false) {
        $temppages = array_merge($temppages, $pages);
    }

    //$temppages = array(array('title' => 'Template:U.S._political_divisions_histories'));

    $pages = array();
    foreach ($temppages as $page) {
    	$pages[] = $page['title'];
    }

    $wiki->getPagesWithCache($pages);

    $results = array();
    list($prefix, $template) = explode(':', $template, 2);
    $template = str_replace('_', ' ', $template);
    $template = ucfirst($template);

    foreach ($temppages as $page) {
    	$data = $wiki->getPageWithCache($page['title']);
		$paramvalues = getTemplateParamValues($data, $template, $param);

		if (empty($paramvalues)) continue; // Could be embedded by another template.

		foreach ($paramvalues as $value) {
			if (! isset($results[$value])) $results[$value] = array();
			$results[$value][] = $page['title'];
		}
    }

    ksort($results);

    $output .= "Template parameter values for [[$template]], parameter: $param\n\nValue count: " . count($results) . "\n\n";
    $output .= "==Value summary==\n{| class=\"wikitable\"\n";

    foreach ($results as $value => $pages) {
    	$output .= "|-\n| $value || " . count($pages) . "\n";
    }

    $output .= "|}\n";

    foreach ($results as $value => $pages) {
    	$output .= "==$value==\n";
    	sort($pages);

    	foreach ($pages as $page) {
    		if (strpos($page, ':') !== false) $page = ':' . $page;
    		$output .= "*[[$page]]\n";
    	}
    }

	$resultwriter->writeResults("User:WPPageListBot/TemplateParam", $output, "");
}

/**
 * Get a template parameters values
 *
 * @param string $data
 * @param string $template
 * @param string $param
 * @returns array parameters values, may be empty
 */
function getTemplateParamValues($data, $template, $param)
{
	$values = array();
	$param = strtolower($param);
	$data = preg_replace('/<!--.*?-->/us', '', $data); // Strip comments
	$data = preg_replace('/\{\{\{.*?\}\}\}/us', '', $data); // Strip variables
//	echo $data . "\n\n";

	if (preg_match_all("!\{\{\s*$template([^\}]+?)\}\}!i", $data, $matches, PREG_PATTERN_ORDER)) {
		foreach ($matches[1] as $templatedata) {
//			echo $templatedata . "\n\n";
			$fields = explode('|', $templatedata);
//			print_r($fields);

			foreach ($fields as $field) {
				if (strpos($field, '=') !== false) {
					list($fieldname, $value) = explode('=', $field, 2);
					$fieldname = trim(strtolower($fieldname));

					if ($fieldname == $param) {
						$value = trim($value);
						if (! empty($value)) $values[$value] = true; // Removes duplicates
						break;
					}
				}
			}
		}
	}

	return array_keys($values);
}
