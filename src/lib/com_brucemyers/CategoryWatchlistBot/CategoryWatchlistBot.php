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

namespace com_brucemyers\CategoryWatchlistBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\MySQLDate;
use PDO;

class CategoryWatchlistBot
{
    const QUERY_SAVE_DAYS = 'CategoryWatchlistBot.query_save_days';
    const MAX_WATCH_DAYS = 'CategoryWatchlistBot.max_watch_days';
    const OUTPUTDIR = 'CategoryWatchlistBot.outputdir';
    const RULETYPE = 'CategoryWatchlistBot.ruletype';
    const CUSTOMRULE = 'CategoryWatchlistBot.customrule';
    const ERROREMAIL = 'CategoryWatchlistBot.erroremail';
    const CURRENTWIKI = 'CategoryWatchlistBot.currentwiki';
    const HTMLDIR = 'CategoryWatchlistBot.htmldir';
    const URLPATH = 'CategoryWatchlistBot.urlpath';
    const WIKI_HOST = 'CategoryWatchlistBot.wiki_host';
    const TOOLS_HOST = 'CategoryWatchlistBot.tools_host';
    const LABSDB_USERNAME = 'CategoryWatchlistBot.labsdb_username';
    const LABSDB_PASSWORD = 'CategoryWatchlistBot.labsdb_password';

    const CACHE_PREFIX_RESULT = 'CatWBResult:';
    const CACHE_PREFIX_ATOM = 'CatWBAtom:';

    protected $dbh_tools;

    public function __construct(&$ruleconfigs)
    {
        $totaltimer = new Timer();
        $totaltimer->start();
        $startwiki = Config::get(self::CURRENTWIKI);

    	$wiki_host = Config::get(self::WIKI_HOST);
    	$tools_host = Config::get(self::TOOLS_HOST);
    	$user = Config::get(self::LABSDB_USERNAME);
    	$pass = Config::get(self::LABSDB_PASSWORD);

    	$outputdir = Config::get(self::OUTPUTDIR);
    	$outputdir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputdir);
    	$outputdir = preg_replace('!(/|\\\\)$!', '', $outputdir); // Drop trailing slash
    	$outputdir .= DIRECTORY_SEPARATOR;

    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CategoryWatchlistBot", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$this->dbh_tools = $dbh_tools;

        new CreateTables($dbh_tools);

        $asof_date = time();
    	$htmldir = Config::get(self::HTMLDIR);
        $urlpath = Config::get(self::URLPATH);

        // Generate each wikis diffs.
        $catcount = 0;
        $errorrulsets = array();

        $catLinksDiff = new CategoryLinksDiff($wiki_host, $dbh_tools, $outputdir, $user, $pass, $asof_date, $tools_host);

        foreach ($ruleconfigs as $wikiname => $wikidata) {
        	if (! empty($startwiki) && $wikiname != $startwiki) continue;
            $startwiki = '';
            Config::set(self::CURRENTWIKI, $wikiname, true);

            $catcount += $catLinksDiff->processWiki($wikiname, $wikidata);

        	Config::set(self::CURRENTWIKI, '', true);
        }

		$ts = $totaltimer->stop();
		$totaltime = sprintf("%d days %d:%02d:%02d", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']);

        $this->_writeHtmlStatus($ruleconfigs, $totaltime, $errorrulsets, $asof_date, $htmldir, $catcount);

        $this->_backupHistory($tools_host, $user, $pass, $outputdir);

        // Clear the cache, order is important
        FileCache::purgeAllPrefix(self::CACHE_PREFIX_RESULT);
        FileCache::purgeAllPrefix(self::CACHE_PREFIX_ATOM);

        // Email if approvals needed
		$sth = $dbh_tools->query('SELECT id FROM querys WHERE catcount = ' . QueryCats::CATEGORY_COUNT_UNAPPROVED . ' LIMIT 1');
		if ($sth->fetch(PDO::FETCH_ASSOC)) {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'From: WMF Labs <admin@brucemyers.com>' . "\r\n";
			mail(Config::get(CategoryWatchlistBot::ERROREMAIL), 'CategoryWatchlistBot Approvals Needed', 'Approvals needed', $headers);
		}

        // Purge old queries
        $keepdays = Config::get(self::QUERY_SAVE_DAYS) + 1;
        $purgebefore = MySQLDate::toMySQLDatetime(strtotime("-$keepdays day"));
        $sth = $dbh_tools->prepare('SELECT id FROM querys WHERE lastaccess < ?');
        $sth->bindParam(1, $purgebefore);
        $sth->execute();

		$results = $sth->fetchAll(PDO::FETCH_ASSOC);
		$sth->closeCursor();

		foreach ($results as $row) {
			$id = $row['id'];
			$dbh_tools->exec("DELETE FROM querycats WHERE queryid = $id");
			$dbh_tools->exec("DELETE FROM querys WHERE id = $id");
		}

		// Purge old diffs/runs
        $keepdays = Config::get(self::MAX_WATCH_DAYS) + 1;
        $purgebefore = MySQLDate::toMySQLDatetime(strtotime("-$keepdays day"));

		foreach ($ruleconfigs as $wikiname => $wikidata) {
			$sth = $dbh_tools->prepare("DELETE FROM `{$wikiname}_diffs` WHERE diffdate < ?");
			$sth->bindParam(1, $purgebefore);
			$sth->execute();

			$sth = $dbh_tools->prepare("DELETE FROM runs WHERE wikiname = ? AND rundate < ?");
			$sth->bindParam(1, $wikiname);
			$sth->bindParam(2, $purgebefore);
			$sth->execute();
		}
    }

    /**
     * Write the bot status page
     */
    protected function _writeHtmlStatus($ruleconfigs, $totaltime, $errorrulsets, $asof_date, $outputdir, $catcount)
    {
    	$rulesetcnt = count($ruleconfigs);
    	$errcnt = count($errorrulsets);
    	$asof_date = getdate($asof_date);
    	$asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];

		$path = $outputdir . 'status.html';
		$hndl = fopen($path, 'wb');

    	$output = <<<EOT
<!DOCTYPE html>
<html><head>
<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
<title>CategoryWatchlistBot Status</title></head>
<body>
<h2>CategoryWatchlistBot Status</h2>
<b>Last run:</b> $asof_date<br />
<b>Processing time:</b> $totaltime<br />
<b>Wiki count:</b> $rulesetcnt<br />
<b>Category count:</b> $catcount<br />
<b>Errors:</b> $errcnt
EOT;

    	if ($errcnt) {
    		$output .= '<h3>Errors</h3><ul>';
    		foreach ($errorrulsets as $project) {
    			$output .= "<li>$project</li>";
    		}
    		$output .= '</ul>';
    	}

    	$output .= "<h3>Wikis</h3>\n<ul>\n";

    	foreach ($ruleconfigs as $wikiname => $wikidata) {
    		$wikititle = htmlentities($wikidata['title'], ENT_COMPAT, 'UTF-8');
    		$output .= "<li>$wikiname - $wikititle</li>\n";
    	}

    	$output .= '</ul>';

    	$output .= '</body></html>';

    	fwrite($hndl, $output);
    	fclose($hndl);
    }

    /**
     * Backup history table
     *
     * @param string $tools_host
     * @param string $user
     * @param string $pass
     */
    protected function _backupHistory($tools_host, $user, $pass, $outputdir)
    {
    	$backupFile = $outputdir . 'CategoryWatchlistBot_History.bz2';
    	$command = "mysqldump -h {$tools_host} -u {$user} -p{$pass} s51454__CategoryWatchlistBot | bzip2 -9 > $backupFile";
    	system($command);
    }
}