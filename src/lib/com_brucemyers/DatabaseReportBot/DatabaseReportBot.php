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

namespace com_brucemyers\DatabaseReportBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\MediaWiki\ResultWriter;
use com_brucemyers\RenderedWiki\RenderedWiki;
use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\Util\Config;
use PDO;

class DatabaseReportBot
{
    const HTMLDIR = 'DatabaseReportBot.htmldir';
	const OUTPUTDIR = 'DatabaseReportBot.outputdir';
    const OUTPUTTYPE = 'DatabaseReportBot.outputtype';
    const ERROREMAIL = 'DatabaseReportBot.erroremail';
    const LABSDB_USERNAME = 'DatabaseReportBot.labsdb_username';
    const LABSDB_PASSWORD = 'DatabaseReportBot.labsdb_password';
    const MAX_ROWS_PER_PAGE = 5000;

    protected $resultWriter;
    protected $dbh_wiki;
    protected $dbh_tools;
    protected $dbh_wikidata;
    protected $mediawiki;
    protected $renderedwiki;
    protected $datawiki;
    protected $wiki_host;
    protected $tools_host;
    protected $wikidata_host;
    protected $user;
    protected $pass;

    public function __construct(ResultWriter $resultWriter, MediaWiki $mediawiki, RenderedWiki $renderedwiki, $wiki_host, $wiki_db,
    		$tools_host, $wikidata_host, WikidataWiki $datawiki)
    {
        $this->resultWriter = $resultWriter;
        $this->mediawiki = $mediawiki;
        $this->renderedwiki = $renderedwiki;
        $this->datawiki = $datawiki;

     	$user = Config::get(self::LABSDB_USERNAME);
    	$pass = Config::get(self::LABSDB_PASSWORD);

    	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname=$wiki_db", $user, $pass);
    	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     	$this->dbh_wiki = $dbh_wiki;
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__DatabaseReportBot", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     	$this->dbh_tools = $dbh_tools;
    	$dbh_wikidata = new PDO("mysql:host=$wikidata_host;dbname=wikidatawiki_p", $user, $pass);
    	$dbh_wikidata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     	$this->dbh_wikidata = $dbh_wikidata;
     	$this->wiki_host = $wiki_host;
     	$this->tools_host = $tools_host;
     	$this->wikidata_host = $wikidata_host;
     	$this->user = $user;
     	$this->pass = $pass;
    }

    public function generateReport($reportname, $outputPage, $params)
    {
    	$apis = array(
    	    'dbh_wiki' => $this->dbh_wiki,
    		'wiki_host' => $this->wiki_host,
    		'dbh_tools' => $this->dbh_tools,
    		'tools_host' => $this->tools_host,
    		'dbh_wikidata' => $this->dbh_wikidata,
    		'data_host' => $this->wikidata_host,
    		'mediawiki' => $this->mediawiki,
    		'renderedwiki' => $this->renderedwiki,
    		'datawiki' => $this->datawiki,
    		'user' => $this->user,
    		'pass' => $this->pass
    	);

    	$classname = "com_brucemyers\\DatabaseReportBot\\Reports\\$reportname";
    	$report = new $classname();
    	$continue = $report->init($apis, $params);
    	if (! $continue) return;

    	$reportTitle = $report->getTitle();
    	$rows = $report->getRows($apis);

		$linktemplate = 'dbr link';

    	if (isset($rows['groups'])) {
    		$chunkcount = 1;
			$groups = true;
			if (isset($rows['linktemplate'])) $linktemplate = $rows['linktemplate'];
    	} else {
    		if (isset($rows['linktemplate'])) {
    			$linktemplate = $rows['linktemplate'];
    			unset ($rows['linktemplate']);
    		}

	    	$rowchunks = array_chunk($rows, self::MAX_ROWS_PER_PAGE);
	    	$chunkcount = count($rowchunks);
			$comment = 'Record count: ' . count($rows);
			$groups = false;
    	}

    	$intro = str_replace('%s', gmdate('H:i, d F Y') . ' (UTC)', $report->getIntro()) . "\n";

    	$header = "{| class=\"wikitable sortable plainlinks\"\n";
		$header .= "|- style=\"white-space:nowrap;\"\n";
		$header .= "! No.\n";
		$headings = $report->getHeadings();
		foreach ($headings as $heading) {
			$header .= "! $heading\n";
		}

		$footer = "|}\n";

		// Write index
		if ($chunkcount > 1) {
			$output = $intro . "\n";
			for ($x=1; $x <= $chunkcount; ++$x) {
				$output .= "*[[$outputPage/$reportTitle/$x|Page $x]]\n";
			}
			$this->resultWriter->writeResults($outputPage . '/' . $reportTitle, $output, $comment);
		}

		if ($groups) {
			$recordcnt = 0;
			$output = $intro;

			foreach ($rows['groups'] as $groupname => &$group) {
				$recordcnt += count($group);
				$output .= "==$groupname==\n" . $header;
				$rowcnt = 1;

				foreach ($group as $row) {
					$output .= "|-\n";
					$output .= "| $rowcnt\n";
					foreach ($row as $colnum => $column) {
						$output .= "| ";
						if ($column !== '') {
							if ($colnum == 0 && $linktemplate !== false) $output .= "{{{$linktemplate}|1=$column}}";
							else $output .= $column;
						}
						$output .= "\n";
					}
					++$rowcnt;
				}

				$output .= $footer;
			}

			unset($group);

			$comment = 'Record count: ' . $recordcnt;
		    $this->resultWriter->writeResults($outputPage . '/' . $reportTitle, $output, $comment);
		} else {

			$rowcnt = 1;
			$pagecnt = 1;

			foreach ($rowchunks as $rowchunk) {
				$output = $intro . $header;

				foreach ($rowchunk as $row) {
					$output .= "|-\n";
					$output .= "| $rowcnt\n";
					foreach ($row as $colnum => $column) {
						$output .= "| ";
						if ($column !== '') {
							if ($colnum == 0 && $linktemplate !== false) $output .= "{{{$linktemplate}|1=$column}}";
							else $output .= $column;
						}
						$output .= "\n";
					}
					++$rowcnt;
				}

				$output .= $footer;

				$pagetitle = $outputPage . '/' . $reportTitle;
				if ($chunkcount > 1) $pagetitle .= '/' . $pagecnt;

		    	$this->resultWriter->writeResults($pagetitle, $output, $comment);
		    	++$pagecnt;
			}
		}
    }
}