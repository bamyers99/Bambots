<?php
/**
 Copyright 2014 Myers Enterprises II

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

use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use com_brucemyers\CategoryWatchlistBot\CategoryWatchlistBot;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'CategoryWatchlistBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activerules = array(
    	'commonswiki' => array('title' => 'Wikipedia Commons', 'domain' => 'commons.wikimedia.org', 'catNS' => 'Category', 'lang' => 'en'),
    	'enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'catNS' => 'Category', 'lang' => 'en'),
//    	'svwiki' => array('title' => 'Svenska Wikipedia', 'domain' => 'sv.wikipedia.org'),
//        'nlwiki' => array('title' => 'Nederlands Wikipedia', 'domain' => 'nl.wikipedia.org'),
//        'dewiki' => array('title' => 'Deutsch Wikipedia', 'domain' => 'de.wikipedia.org'),
//        'frwiki' => array('title' => 'Français Wikipedia', 'domain' => 'fr.wikipedia.org'),
//        'ruwiki' => array('title' => 'Ру́сский Wikipedia', 'domain' => 'ru.wikipedia.org'),
//        'itwiki' => array('title' => 'Italiano Wikipedia', 'domain' => 'it.wikipedia.org'),
//        'eswiki' => array('title' => 'Español Wikipedia', 'domain' => 'es.wikipedia.org'),
//        'viwiki' => array('title' => 'Tiếng Việt Wikipedia', 'domain' => 'vi.wikipedia.org'),
//        'warwiki' => array('title' => 'Winaray Wikipedia', 'domain' => 'war.wikipedia.org'),
//        'cebwiki' => array('title' => 'Sinugboanong Binisaya Wikipedia', 'domain' => 'ceb.wikipedia.org'),
//        'plwiki' => array('title' => 'Polski Wikipedia', 'domain' => 'pl.wikipedia.org'),
//        'jawiki' => array('title' => '日本語 Wikipedia', 'domain' => 'ja.wikipedia.org'),
//        'ptwiki' => array('title' => 'Português Wikipedia', 'domain' => 'pt.wikipedia.org', 'catNS' => 'Categoria', 'lang' => 'pt')
//        'zhwiki' => array('title' => '中文 Wikipedia', 'domain' => 'zh.wikipedia.org'),
//        'ukwiki' => array('title' => 'Українська Wikipedia', 'domain' => 'uk.wikipedia.org')
    );

try {

	if ($argc > 1) {
		$action = $argv[1];
		switch ($action) {
		    case 'cattree':
		        if ($argc < 4) {
		            echo 'Wiki and category required';
		            exit;
		        }
		        printCatTree($argv[2], $argv[3]);
		        exit;
		        break;

		    case 'tmplredirects':
		        if ($argc < 3) {
		            echo 'Wiki required';
		            exit;
		        }
		        dumpTemplateRedirects($argv[2]);
		        exit;
		        break;

		    default:
		    	echo 'Unknown action = ' . $action;
				exit;
		    	break;
		}
	}

    $ruletype = Config::get(CategoryWatchlistBot::RULETYPE);

    $timer = new Timer();
    $timer->start();

    if ($ruletype == 'active') $rules = $activerules;
    elseif ($ruletype == 'custom') {
    	$rulename = Config::get(CategoryWatchlistBot::CUSTOMRULE);
    	$rules = array($rulename => $activerules[$rulename]);
	} else {
    	echo 'Unknown ruletype = ' . $ruletype;
    	exit;
    }

    $bot = new CategoryWatchlistBot($rules);

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    $msg = $ex->getMessage() . "\n" . $ex->getTraceAsString();
    Logger::log($msg);
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'From: Bambots <admin@brucemyers.com>' . "\r\n";
    mail(Config::get(CategoryWatchlistBot::ERROREMAIL), 'CategoryWatchlistBot failed', $msg, $headers);
}

/**
 * Print a category tree
 *
 * @param string $wikiname
 * @param string $category
 */
function printCatTree($wikiname, $category)
{
	$foundcats = array();
    $wiki_host = Config::get(CategoryWatchlistBot::WIKI_HOST);
    if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";
    $user = Config::get(CategoryWatchlistBot::LABSDB_USERNAME);
    $pass = Config::get(CategoryWatchlistBot::LABSDB_PASSWORD);
	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8", $user, $pass);
    $dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Category tree for $wikiname - $category\n";;
    $category = str_replace(' ', '_', $category);
	traverseCats($dbh_wiki, $foundcats, $category, 10);
}

/**
 * Depth first search
 *
 * @param PDO $dbh_wiki
 * @param array $foundcats
 * @param mixed $searchcats - array or string
 * @param int $depth
 */
function traverseCats(&$dbh_wiki, &$foundcats, $searchcats, $depth)
{
	$searchcats = (array)$searchcats;
	sort($searchcats);

	$nextcats = array();

	foreach ($searchcats as $cat) {
		if (in_array($cat, $foundcats)) continue;

		$sth = $dbh_wiki->prepare('SELECT cat_pages,cat_subcats,cat_files FROM category WHERE cat_title = ?');
		$sth->bindParam(1, $cat);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		$subcatcnt = $row['cat_subcats'];
		$filecnt = $row['cat_files'];
		$articlecnt = $row['cat_pages'] - ($subcatcnt + $filecnt);

		$indent = '';
		$indents = 10 - $depth;
		while ($indents--) $indent .= '*';
		$displaycat = str_replace('_', ' ', $cat);
		echo "{$indent}[[:Category:$displaycat]] ($subcatcnt subcategories, $articlecnt articles, $filecnt files)\n";
		$foundcats[] = $cat;

		if ($depth) {
			$sth = $dbh_wiki->prepare("SELECT DISTINCT page_title FROM page,categorylinks WHERE page_id=cl_from AND cl_to = ? AND cl_type='subcat'");
			$sth->bindParam(1, $cat);
			$sth->execute();

			$subcats = array();

			while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$cat2 = $row['page_title'];
				if (in_array($cat2, $foundcats)) continue;
				$subcats[] = $cat2;
			}

			$sth->closeCursor();

			if (! count($subcats)) continue;

			traverseCats($dbh_wiki, $foundcats, $subcats, $depth - 1);
		}
	}
}

/**
 * Dump template redirects
 *
 * @param string $wikiname
 */
function dumpTemplateRedirects($wikiname)
{
    $wiki_host = Config::get('CategoryWatchlistBot.wiki_host');
    if (empty($wiki_host)) $wiki_host = "$wikiname.analytics.db.svc.eqiad.wmflabs";
    $user = Config::get('CategoryWatchlistBot.labsdb_username');
    $pass = Config::get('CategoryWatchlistBot.labsdb_password');
    $dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8", $user, $pass);
    $dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $outpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . $wikiname . 'TemplateRedirects.tsv';
    $hndl = fopen($outpath, 'w');

    $sql = "SELECT page_title, rd_title FROM page, redirect " .
   	    " WHERE page_namespace = 10 AND page_id = rd_from AND page_is_redirect = 1";

    $results = $dbh_wiki->query($sql);
    $results->setFetchMode(PDO::FETCH_NUM);

    while ($row = $results->fetch()) {
        $oldname = str_replace('_', ' ', $row[0]);
        $newname = str_replace('_', ' ', $row[1]);
        fwrite($hndl, "$oldname\t$newname\n");
    }

    fclose($hndl);
}