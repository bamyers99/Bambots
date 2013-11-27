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
use com_brucemyers\MediaWiki\UserContribLister;

$webdir = dirname(__FILE__);
// Marker so include files can tell if they are called directly.
$GLOBALS['included'] = true;
$GLOBALS['botname'] = 'InceptionBot';
define('BOT_REGEX', '!(?:spider|bot[\s_+:,\.\;\/\\\-]|[\s_+:,\.\;\/\\\-]bot)!i');

require $webdir . DIRECTORY_SEPARATOR . 'bootstrap.php';

$user = @ $_REQUEST['user'];
$days = @ $_REQUEST['days'];

display_pages($user, $days);

/**
 * Display new pages for a user
 *
 * @param $user string Username
 * @param $days int Days to go back
 */
function display_pages($user, $days)
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	    <meta name="robots" content="noindex, nofollow" />
	    <title>User New Pages</title>
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
        <h2>User New Pages</h2>
        <form action="UserNewPages.php" ><table class="form">
        <tr><td><b>Username</b> <input name="user" type="text" size="10" id="testfield1" value="<?php echo $user ?>" /></td></tr>
        <tr><td><b>Days</b> <input name="days" type="text" size="10" value="<?php echo $days ?>" /> 60 days max</td></tr>
        <tr><td><input type="submit" value="Submit" /></td></tr>
        </table></form>

        <script type="text/javascript">
            if (document.getElementById) {
                document.getElementById('testfield1').focus();
            }
        </script>
    <?php

    if (! empty($user) && ! preg_match(BOT_REGEX, $_SERVER['HTTP_USER_AGENT'])) {
        display_articles($user, $days);
    }

    ?></body></html><?php
}

/**
 * Display new articles
 *
 * @param $user string Username
 * @param $days int Days to go back
 */
function display_articles($user, $days)
{
    if (! is_numeric($days)) $days = 14;
    else $days = (int)$days;

    if ($days > 60) $days = 60;
    elseif ($days < 1) $days = 1;

    $earliest = gmdate('Ymd', strtotime("-$days days")) . '000000';

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);

    echo '<table><caption>New Pages</caption><tr><th>Title</th><th>Created</th><th>Size</th></tr>';

    $lister = new UserContribLister($wiki, $user, $earliest);
    $line = 1;

    while (($pages = $lister->getNextBatch()) !== false) {
        foreach ($pages as $page) {
            if (isset($page['new'])) {
                $title = $page['title'];
                $urlencodedtitle = urlencode(str_replace(' ', '_', $title));
                $timestamp = str_replace(array('T','Z'), array(' ',''), $page['timestamp']);
                $size = number_format($page['size']);
                $redirect = '';
                if (strpos($page['comment'], 'Redirected page to') !== false || strpos($page['comment'], 'moved page') !== false) $redirect = ' (redirect)';
                $class = '';
                if ($line++ % 2 == 1) $class = ' class="altrow"';
                echo "<tr$class><td><a href='https://en.wikipedia.org/wiki/$urlencodedtitle'>$title</a>$redirect</td><td>$timestamp</td><td style='text-align:right'>$size</td></tr>";
            }
        }
    }

    echo '</table>';
}