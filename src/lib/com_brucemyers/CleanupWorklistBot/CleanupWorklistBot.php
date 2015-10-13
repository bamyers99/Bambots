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

namespace com_brucemyers\CleanupWorklistBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\MediaWiki\ResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Email;
use PDO;

class CleanupWorklistBot
{
    const OUTPUTDIR = 'CleanupWorklistBot.outputdir';
    const OUTPUTTYPE = 'CleanupWorklistBot.outputtype';
    const RULETYPE = 'CleanupWorklistBot.ruletype';
    const CUSTOMRULE = 'CleanupWorklistBot.customrule';
    const ERROREMAIL = 'CleanupWorklistBot.erroremail';
    const CURRENTPROJECT = 'CleanupWorklistBot.currentproject';
    const HTMLDIR = 'CleanupWorklistBot.htmldir';
    const URLPATH = 'CleanupWorklistBot.urlpath';
    const ENWIKI_HOST = 'CleanupWorklistBot.enwiki_host';
    const TOOLS_HOST = 'CleanupWorklistBot.tools_host';
    const LABSDB_USERNAME = 'CleanupWorklistBot.labsdb_username';
    const LABSDB_PASSWORD = 'CleanupWorklistBot.labsdb_password';
    protected $resultWriter;

    public function __construct(&$ruleconfigs, ResultWriter $resultWriter, $skipCatLoad)
    {
    	$errorrulsets = array();
        $this->resultWriter = $resultWriter;
        $totaltimer = new Timer();
        $totaltimer->start();
        $startProject = Config::get(self::CURRENTPROJECT);

    	$enwiki_host = Config::get(self::ENWIKI_HOST);
    	$tools_host = Config::get(self::TOOLS_HOST);
    	$user = Config::get(self::LABSDB_USERNAME);
    	$pass = Config::get(self::LABSDB_PASSWORD);

        new CreateTables($dbh_tools);

        if (empty($startProject) && count($ruleconfigs) > 100) $dbh_tools->exec('TRUNCATE project');

        $restarted = '';
        if (! empty($startProject)) $restarted = ' (restarted)';

        $categories = new Categories($enwiki_host, $user, $pass, $tools_host);
        $categories->load($skipCatLoad);

        $asof_date = getdate();
    	$outputdir = Config::get(self::HTMLDIR);
        $urlpath = Config::get(self::URLPATH);

        $project_pages = new ProjectPages($enwiki_host, $user, $pass, $tools_host);

        $repgen = new ReportGenerator($tools_host, $outputdir, $urlpath, $asof_date, $resultWriter, $categories, $user, $pass);

        // Generate each projects reports.

        foreach ($ruleconfigs as $project => $category) {
        	if (! empty($startProject) && $project != $startProject) continue;
            $startProject = '';
            Config::set(self::CURRENTPROJECT, $project, true);

        	$isWikiProject = false;
        	if (strpos($project, 'WikiProject_') === 0) {
        		$project = substr($project, 12);
        		$isWikiProject = true;
        	}
        	if (empty($category)) $category = $project;

        	try {
	        	$page_count = $project_pages->load($category);
	        	if (! $page_count) {
	        		$errorrulsets[] = $project . ' (no pages in project)';
	        		Logger::log($project . ' (no pages in project)');
	        		continue;
	        	}

	        	$wikiPageCreated = $repgen->generateReports($project, $isWikiProject, $page_count, true); // Temporary until bot approval.
	        	//$wikiPageCreated = $repgen->generateReports($project, $isWikiProject, $page_count);
	        	if (! $wikiPageCreated) $repgen->generateReports($project, $isWikiProject, $page_count, true,
	        		MediaWiki::MAX_PAGE_SIZE, false);
        	} catch (CatTypeNotFoundException $ex) {
        		$errorrulsets[] = $project . ' (project category not found)';
        	}

        	Config::set(self::CURRENTPROJECT, '', true);
        }

        // Generate the index page, doing separate from above because do not want the file open for a long time.
        $this->_writeIndex($outputdir, $urlpath, $tools_host, $user, $pass);

		$ts = $totaltimer->stop();
		$totaltime = sprintf("%d days %d:%02d:%02d", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']);
		$totaltime .= $restarted;

        $this->_writeHtmlStatus(count($ruleconfigs), $totaltime, $errorrulsets, $asof_date, $outputdir);
        //$this->_writeStatus(count($ruleconfigs), $totaltime, $errorrulsets);

        $this->_backupHistory($tools_host, $user, $pass);
    }

    /**
     * Write the project index page
     */
    protected function _writeIndex($outputdir, $urlpath, $tools_host, $user, $pass)
    {
        $idxpath = $outputdir . 'index.html';
        $idxhndl = fopen($idxpath, 'wb');
        fwrite($idxhndl, "<!DOCTYPE html>
        <html><head>
        <meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
        <title>WikiProject Cleanup Listings</title>
        <link rel='stylesheet' type='text/css' href='../css/cwb.css' />
        </head><body>
        <h2>WikiProject Cleanup Listings</h2>\n
        <ul>\n
        ");

    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot;charset=utf8", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $results = $dbh_tools->query('SELECT * FROM `project` ORDER BY `name`');

        while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        	$project = $row['name'];
        	$wiki_too_big = (int)$row['wiki_too_big'];

            $isWikiProject = false;
        	if (strpos($project, 'WikiProject_') === 0) {
        		$project = substr($project, 12);
        		$isWikiProject = true;
        	}
    		$filesafe_project = str_replace('/', '_', $project);

    		$project = str_replace('_', ' ', $project);
        	$wikiproject = ($isWikiProject) ? 'WikiProject ' : '';
			$projecturl = "https://en.wikipedia.org/wiki/Wikipedia:{$wikiproject}" . $project;
			$histurl = $urlpath . 'history/' . $filesafe_project . '.html';
			if ($wiki_too_big) $bycaturl = $urlpath . 'bycat/' . $filesafe_project . '.html';
			else $bycaturl = 'https://en.wikipedia.org/wiki/User:CleanupWorklistBot/lists/' . $filesafe_project;
			$csvurl = $urlpath . 'csv/' . $filesafe_project . '.csv';
			$alphaurl = $urlpath . 'alpha/' . $filesafe_project . '.html';

	        fwrite($idxhndl, "<li><a href=\"$projecturl\">$project</a> (<a href=\"$alphaurl\">alphabetic</a>, <a href=\"$bycaturl\">by cat</a>, <a href=\"$csvurl\">CSV</a>, <a href=\"$histurl\">history</a>)</li>\n");
        }

        fwrite($idxhndl, "</ul>\nGenerated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot'>CleanupWorklistBot</a></body></html>");
		fclose($idxhndl);
    }

    /**
     * Write the bot status page
     */
    protected function _writeHtmlStatus($rulesetcnt, $totaltime, $errorrulsets, $asof_date, $outputdir)
    {
    	$errcnt = count($errorrulsets);
    	$asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];

		$path = $outputdir . 'status.html';
		$hndl = fopen($path, 'wb');

    	$output = <<<EOT
<!DOCTYPE html>
<html><head>
<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
<title>CleanupWorklistBot Status</title></head>
<body>
<h2>CleanupWorklistBot Status</h2>
<b>Last run:</b> $asof_date<br />
<b>Processing time:</b> $totaltime<br />
<b>Project count:</b> $rulesetcnt<br />
<b>Errors:</b> $errcnt
EOT;

    	if ($errcnt) {
    		$output .= '<h3>Errors</h3><ul>';
    		foreach ($errorrulsets as $project) {
    			$output .= "<li>$project</li>";
    		}
    		$output .= '</ul>';
    	}

    	$output .= '</body></html>';

    	fwrite($hndl, $output);
    	fclose($hndl);
    }

    /**
     * Write the bot status page
     */
    protected function _writeStatus($rulesetcnt, $totaltime, $errorrulsets)
    {
    	$errcnt = count($errorrulsets);

    	$output = <<<EOT
<noinclude>__NOINDEX__</noinclude>
'''Last run:''' {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)<br />
'''Processing time:''' $totaltime<br />
'''Project count:''' $rulesetcnt<br />
'''Errors:''' $errcnt
EOT;

    	if ($errcnt) {
    		$output .= "\n===Errors===\n";
    		foreach ($errorrulsets as $project) {
    			$output .= "*$project\n";
    		}
    	}

    	$this->resultWriter->writeResults('User:CleanupWorklistBot/Status', $output, "$errcnt errors; Total time: $totaltime");
    }

    /**
     * Backup history table
     *
     * @param string $tools_host
     * @param string $user
     * @param string $pass
     */
    protected function _backupHistory($tools_host, $user, $pass)
    {
        $outputDir = Config::get(self::OUTPUTDIR);
        $outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
        $outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
        $outputDir .= DIRECTORY_SEPARATOR;

    	$backupFile = $outputDir . 'CleanupWorklistBot_History.bz2';
    	$command = "mysqldump -h {$tools_host} -u {$user} -p{$pass} s51454__CleanupWorklistBot history | bzip2 -9 > $backupFile";
    	system($command);

    	$email = new Email();
    	$attach = array($backupFile);
    	$email->sendEmail('admin@brucemyers.com', Config::get(self::ERROREMAIL), 'CleanupWorklistBot backup', 'DB backup', $attach);
    }
}