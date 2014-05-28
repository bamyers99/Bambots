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

use com_brucemyers\MediaWiki\ResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\Config;
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

    public function __construct($ruleconfigs, ResultWriter $resultWriter, $skipCatLoad)
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

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        new CreateTables($dbh_tools);

        if (! $skipCatLoad) {
        	$categories = new Categories($dbh_enwiki, $dbh_tools);
        	$categories->load();
        }

        $asof_date = getdate();
    	$outputdir = Config::get(self::HTMLDIR);
        $urlpath = Config::get(self::URLPATH);

        $project_pages = new ProjectPages($dbh_enwiki, $dbh_tools);

        $repgen = new ReportGenerator($dbh_tools, $outputdir, $urlpath, $asof_date, $resultWriter);

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

	        	$repgen->generateReports($project, $isWikiProject, $page_count);
        	} catch (CatTypeNotFoundException $ex) {
        		$errorrulsets[] = $project . ' (project category not found)';
        	} catch (Exception $ex2) {
        		echo $ex2->getMessage() . "\n";
        		$strpos = strpos($ex2->getMessage(), 'exceeds the article size limit');
        		print_r($strpos);
        		if (strpos($ex2->getMessage(), 'exceeds the article size limit') !== false) $errorrulsets[] = $project . ' (wikipage too big)';
        		else throw $ex2;
        	}

        	Config::set(self::CURRENTPROJECT, '', true);
        }

        // Generate the index page, doing separate from above because do not want the file open for a long time.
        $this->_writeIndex($ruleconfigs, $outputdir, $urlpath);

		$ts = $totaltimer->stop();
		$totaltime = sprintf("%d:%02d:%02d", $ts['hours'], $ts['minutes'], $ts['seconds']);

        $this->_writeStatus(count($ruleconfigs), $totaltime, $errorrulsets);
    }

    /**
     * Write the project index page
     */
    protected function _writeIndex(&$ruleconfigs, $outputdir, $urlpath)
    {
        $idxpath = $outputdir . 'index.html';
        $idxhndl = fopen($idxpath, 'wb');
        fwrite($idxhndl, "<html><head>
        <meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
        <title>WikiProject Cleanup Listings</title>
        <link rel='stylesheet' type='text/css' href='../../css/cwb.css' />
        </head><body>
        <p>WikiProject Cleanup Listings</p>\n\n
        <ul>\n
        ");

        foreach ($ruleconfigs as $project => $category) {
            $isWikiProject = false;
        	if (strpos($project, 'WikiProject_') === 0) {
        		$project = substr($project, 12);
        		$isWikiProject = true;
        	}
    		$filesafe_project = str_replace('/', '_', $project);

        	$wikiproject = ($isWikiProject) ? 'WikiProject_' : '';
			$projecturl = "https://en.wikipedia.org/wiki/Wikipedia:{$wikiproject}" . urlencode($project);
			$histurl = $urlpath . 'history/' . urlencode($filesafe_project) . '.html';
			$bycaturl = 'https://en.wikipedia.org/wiki/User:CleanupWorklistBot/lists/' . urlencode($filesafe_project);
			$csvurl = $urlpath . 'csv/' . urlencode($filesafe_project) . '.csv';
			$alphaurl = $urlpath . 'alpha/' . urlencode($filesafe_project) . '.html';

	        fwrite($idxhndl, "<li><a href='$projecturl'>$project</a> (<a href='$alphaurl'>alphabetic</a>, <a href='$bycaturl'>by cat</a>, <a href='$csvurl'>CSV</a>, <a href='$histurl'>history</a>)</li>\n");
        }

        fwrite($idxhndl, "</ul>\nGenerated by <a href='https://en.wikipedia.org/wiki/User:CleanupWorklistBot'>CleanupWorklistBot</a></body></html>");
		fclose($idxhndl);
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
'''Rule errors:''' $errcnt
EOT;

        if ($errcnt) {
    	    $output .= "\n===Rule errors===\n";
    	    foreach ($errorrulsets as $project) {
    	        $output .= "*$project\n";
    	    }
    	}

    	$this->resultWriter->writeResults('User:CleanupWorklistBot/Status', $output, "$errcnt errors; Total time: $totaltime");
    }
}