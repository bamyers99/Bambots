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
 */

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Config;
use com_brucemyers\InceptionBot\DeletedArticleLister;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'InceptionBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$project = @ $_REQUEST['project'];
$month = @ $_REQUEST['month'];

display_pages($project, $month);

/**
 * Display new pages for a user
 *
 * @param $project string Project
 * @param $month int Month to display
 */
function display_pages($project, $month)
{
    $prevmonth = date("Y-m", strtotime('-1 month', strtotime($month . '-01')));
    
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>Deleted New Articles</title>
	    <style>
           table {
	          background: #fff;
	          border:1px solid #ccc;
	          color: #333;
	          margin-bottom: 10px;
           }
           th {
      	      background: #f2f2f2;
	          border:1px solid #bbb;
	          border-top: 1px solid #fff;
	          border-left: 1px solid #fff;
	          text-align: center;
           }
           table tr.altrow td {
	          background: #f8f8f8;
           }
           td {
                padding: 0 10px;
           }
	    </style>
	</head>
	<body>
        <h2>Deleted New Articles</h2>
        <form action="rejectbot" method="post" onsubmit="this.action='DeletedNewPages' + '.php'; return true;"><table class="form">
        <tr><td><b>Project</b> <input name="project" type="text" size="20" id="testfield1" autofocus="1" value="<?php echo htmlentities($project, ENT_COMPAT, 'UTF-8') ?>" /></td></tr>
        <tr><td><b>Month</b> <input name="month" type="text" size="10" id="testfield2" value="<?php echo htmlentities($month, ENT_COMPAT, 'UTF-8') ?>" /> format: YYYY-MM</td></tr>
        <tr><td><input type="submit" value="Submit" /> <input type="submit" value="Previous Month" onclick="const inputField = document.getElementById('testfield2'); inputField.value='<?php echo $prevmonth ?>'; return true;" /></td></tr>
        </table></form>
    <?php

    if (isset($_POST['project']) && isset($_SERVER['HTTP_USER_AGENT']) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
        display_articles($project, $month);
    }

    ?><br /><div style="display: table; margin: 0 auto;">Note: Javascript must be enabled to use this page.<br /><a href="/privacy.html">Privacy Policy</a> <b>&bull;</b> Author: <a href="https://en.wikipedia.org/wiki/User:Bamyers99" class='novisited'>Bamyers99</a></div></body></html><?php
}

/**
 * Display deleted new articles
 *
 * @param $project string Project
 * @param $month int Month to display
 */
function display_articles($project, $month)
{
    if (! preg_match('/\d{4}-\d{2}/', $month)) {
        echo 'Invalid month format';
        return;
    }
    
    $curmonth = date('Y-m');
    if ($month > $curmonth) $month = $curmonth;

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);

    echo '<table><caption>Deleted Articles</caption><tr><th>Title</th><th>Draft</th></tr>';

    $lister = new DeletedArticleLister($wiki);
    $line = 1;

    $pages = $lister->getRedLinks($project, $month);
    
    foreach ($pages as $title => $hasdraft) {
        $urlencodedtitle = urlencode(str_replace(' ', '_', $title));
        $htmltitle = htmlspecialchars($title);
        $class = '';
        if ($line++ % 2 == 1) $class = ' class="altrow"';
        
        echo "<tr$class><td><a href='https://en.wikipedia.org/wiki/$urlencodedtitle'>$htmltitle</a></td><td>";
        
        if ($hasdraft) {
            $drafttitle = "Draft:$title";
            $urlencodedtitle = urlencode(str_replace(' ', '_', $drafttitle));
            $htmltitle = htmlspecialchars($drafttitle);
            echo "<a href='https://en.wikipedia.org/wiki/$urlencodedtitle'>$htmltitle</a>";
        }
        else echo "&nbsp;";
        
        echo "</td></tr>";
    }

    echo '</table>';
}