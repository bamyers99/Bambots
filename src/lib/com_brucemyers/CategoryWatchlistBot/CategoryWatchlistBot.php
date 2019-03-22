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

use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\MySQLDate;
use com_brucemyers\Util\Email;
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

    const CACHE_PREFIX_RESULT = 'Result:';
    const CACHE_PREFIX_ATOM = 'Atom:';

    public function __construct(&$ruleconfigs)
    {
        $totaltimer = new Timer();
        $totaltimer->start();
        $startwiki = Config::get(self::CURRENTWIKI);

    	$outputdir = Config::get(self::OUTPUTDIR);
    	$outputdir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputdir);
    	$outputdir = preg_replace('!(/|\\\\)$!', '', $outputdir); // Drop trailing slash
    	$outputdir .= DIRECTORY_SEPARATOR;

    	$serviceMgr = new ServiceManager();

    	$dbh_tools = $serviceMgr->getDBConnection('tools');

        new CreateTables($dbh_tools);
        $dbh_tools = null;

        $asof_date = time();
    	$htmldir = Config::get(self::HTMLDIR);
        $urlpath = Config::get(self::URLPATH);

        // Generate each wikis diffs.
        $errorrulsets = array();

        $catLinksDiff = new CategoryLinksDiff($serviceMgr, $outputdir, $asof_date);

        foreach ($ruleconfigs as $wikiname => $wikidata) {
        	if (! empty($startwiki) && $wikiname != $startwiki) continue;
            $startwiki = '';
            Config::set(self::CURRENTWIKI, $wikiname, true);

            $catLinksDiff->processWiki($wikiname, $wikidata);

        	Config::set(self::CURRENTWIKI, '', true);
        }

		$ts = $totaltimer->stop();
		$totaltime = sprintf("%d days %d:%02d:%02d", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']);

        $this->_writeHtmlStatus($ruleconfigs, $totaltime, $errorrulsets, $asof_date, $htmldir);

        $this->_backupHistory($serviceMgr, $outputdir);

        // Clear the cache, order is important
        FileCache::purgeAllPrefix(self::CACHE_PREFIX_RESULT);
        FileCache::purgeAllPrefix(self::CACHE_PREFIX_ATOM);

        // Purge old queries
    	$dbh_tools = $serviceMgr->getDBConnection('tools');
        $keepdays = Config::get(self::QUERY_SAVE_DAYS) + 1;
        $purgebefore = MySQLDate::toMySQLDatetime(strtotime("-$keepdays day"));
        $sth = $dbh_tools->prepare('SELECT id FROM querys WHERE lastaccess < ?');
        $sth->bindParam(1, $purgebefore);
        $sth->execute();

		$results = $sth->fetchAll(PDO::FETCH_ASSOC);
		$sth->closeCursor();

		foreach ($results as $row) {
			$id = $row['id'];
			if ($id == 1) continue; // Save sample
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
    protected function _writeHtmlStatus($ruleconfigs, $totaltime, $errorrulsets, $asof_date, $outputdir)
    {
    	$rulesetcnt = count($ruleconfigs);
    	$errcnt = count($errorrulsets);
    	$asof_date = date('F j, Y H:i:s', $asof_date);

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
    protected function _backupHistory(ServiceManager $serviceMgr, $outputdir)
    {
    	$hour = date('G');
    	if ($hour != 0) return;

    	$tools_host = $serviceMgr->getToolsHost();
    	$user = $serviceMgr->getUser();
    	$pass = $serviceMgr->getPass();

    	$backupFile = $outputdir . 'CategoryWatchlistBot_History.bz2';
    	$command = "mysqldump -h {$tools_host} -u {$user} -p{$pass} s51454__CategoryWatchlistBot querys wikis querycats runs | bzip2 -9 > $backupFile";
    	system($command);

    	$dw = date('w');
    	if ($dw != 1) return; // Monday

    	$email = new Email();
    	$attach = array($backupFile);
    	$email->sendEmail('admin@brucemyers.com', Config::get(self::ERROREMAIL), 'CategoryWatchlistBot backup', 'DB backup', $attach);
    }
}