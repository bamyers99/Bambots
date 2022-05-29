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
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Email;
use com_brucemyers\MediaWiki\MediaWiki;
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
    const TOOLS_HOST = 'CleanupWorklistBot.tools_host';
    const LABSDB_USERNAME = 'CleanupWorklistBot.labsdb_username';
    const LABSDB_PASSWORD = 'CleanupWorklistBot.labsdb_password';
    protected $resultWriter;

    public function __construct(&$ruleconfigs, ResultWriter $resultWriter, $skipCatLoad)
    {
    	$errorrulsets = [];
        $this->resultWriter = $resultWriter;
        $totaltimer = new Timer();
        $totaltimer->start();
        $startProject = Config::get(self::CURRENTPROJECT);

    	$tools_host = Config::get(self::TOOLS_HOST);
    	$user = Config::get(self::LABSDB_USERNAME);
    	$pass = Config::get(self::LABSDB_PASSWORD);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        new CreateTables($dbh_tools);

        // Retrieve the projects member category type
        $results = $dbh_tools->query('SELECT `name`, member_cat_type FROM project');

        while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
            $project = $row['name'];
            $member_cat_type = (int)$row['member_cat_type'];

            if (isset($ruleconfigs[$project])) {
                $ruleconfigs[$project] = ['category' => $ruleconfigs[$project], 'member_cat_type' => $member_cat_type];
            }
        }

        // wiki_too_big is now used to indicate if a wikiproject is active: 0=inactive, 1=active
        if (empty($startProject) && count($ruleconfigs) > 100) $dbh_tools->exec('UPDATE `project` SET wiki_too_big = 0');
        $dbh_tools = null;

        $restarted = '';
        if (! empty($startProject)) $restarted = ' (restarted)';

        $mediawiki = new MediaWiki('https://en.wikipedia.org/w/api.php');

        $categories = new Categories($mediawiki, $user, $pass, $tools_host);
        $categories->load($skipCatLoad);

        $asof_date = getdate();
    	$outputdir = Config::get(self::HTMLDIR);
        $urlpath = Config::get(self::URLPATH);

        $project_pages = new ProjectPages($mediawiki, $user, $pass, $tools_host);

        $repgen = new ReportGenerator($tools_host, $outputdir, $urlpath, $asof_date, $resultWriter, $categories, $user, $pass);

        // Generate each projects reports.

        foreach ($ruleconfigs as $project => $attribs) {
            if (is_array($attribs)) {
                $category = $attribs['category'];
                $member_cat_type = $attribs['member_cat_type'];
            } else {
                $category = $attribs;
                $member_cat_type = 0;
            }

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
        	    $page_count = $project_pages->load($category, $member_cat_type, $project);

	        	if ($page_count < 10 && $project != 'Bhubaneswar') {
	        		$errorrulsets[] = $project . ' (< 10 pages in project)';
	        		Logger::log($project . ' (< 10 pages in project)');
	        		Config::set(self::CURRENTPROJECT, '', true);
	        		continue;
	        	}

	        	$repgen->generateReports($project, $isWikiProject, $page_count, $member_cat_type);
	        	
        	} catch (CatTypeNotFoundException $ex) {
        		$errorrulsets[] = $project . ' (project category not found)';
        	}

        	Config::set(self::CURRENTPROJECT, '', true);
        }

        // Free up memory for backup
        unset($repgen);
        unset($project_pages);

        // Generate the index page, doing separate from above because do not want the file open for a long time.
        $this->_writeIndex($outputdir, $urlpath, $tools_host, $user, $pass);

		$ts = $totaltimer->stop();
		$totaltime = sprintf("%d days %d:%02d:%02d", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']);
		$totaltime .= $restarted;

        $this->_writeHtmlStatus(count($ruleconfigs), $totaltime, $errorrulsets, $asof_date, $outputdir);

        $this->_backupHistory($tools_host, $user, $pass, $errorrulsets);
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

    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $results = $dbh_tools->query('SELECT * FROM `project` WHERE wiki_too_big = 1 ORDER BY `name`');

        while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        	$project = $row['name'];

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
			$bycaturl = $urlpath . 'bycat/' . $filesafe_project . '.html';
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
     * Backup history table
     *
     * @param string $tools_host
     * @param string $user
     * @param string $pass
     * @param array $errorrulsets
     */
    protected function _backupHistory($tools_host, $user, $pass, $errorrulsets)
    {
        $outputDir = Config::get(self::OUTPUTDIR);
        $outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
        $outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
        $outputDir .= DIRECTORY_SEPARATOR;

    	$backupFile = $outputDir . 'CleanupWorklistBot_History.bz2';
    	$command = "mysqldump -h {$tools_host} -u {$user} -p{$pass} s51454__CleanupWorklistBot history project | bzip2 -9 > $backupFile";
    	Logger::log($command);
    	$ret = system($command, $return_var);

    	$email = new Email();
    	$subject = 'CleanupWorklistBot backup';
    	if (! empty($errorrulsets)) $subject .= ' - ERROR';
    	$attach = array($backupFile);
    	$email->sendEmail('admin@brucemyers.com', Config::get(self::ERROREMAIL), $subject, 'DB backup', $attach);
    }
}