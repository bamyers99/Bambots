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

use PDO;
use com_brucemyers\Util\Logger;

class CategoryLinksDiff
{
	var $dbh_tools;
	var $wiki_host;
	var $outputdir;
	var $asof;
	protected $user;
	protected $pass;

    /**
     * Constructor
     *
     * @param string $wiki_host, empty = $wikiname.labsdb
     * @param PDO $dbh_tools
     * @param string $outputdir
     */
     public function __construct($wiki_host, PDO $dbh_tools, $outputdir, $user, $pass, $asof)
    {
        $this->wiki_host = $wiki_host;
        $this->dbh_tools = $dbh_tools;
        $this->outputdir = $outputdir;
    	$this->user = $user;
    	$this->pass = $pass;
    	$this->asof = sprintf('%d-%02d-%02d', $asof['year'], $asof['mon'], $asof['mday']);
    }

	/**
     * Process a wiki.
     *
     * @param string $wikiname
     * @param string $wikititle
     */
    function processWiki($wikiname, $wikititle)
    {
    	$wiki_host = $this->wiki_host;
    	if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";
    	$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p", $this->user, $this->pass);
    	$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_diffs` (
		       `diffdate` date NOT NULL,
		       `plusminus` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
			   `pageid` int unsigned NOT NULL,
			   `category` varchar(255) binary NOT NULL,
			   UNIQUE KEY `categoryplus` (`category`, `diffdate`, `plusminus`, `pageid`)
			   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->dbh_tools->exec($sql);

        // Add the wiki table entry if needed
    	$sth = $this->dbh_tools->prepare("SELECT * FROM wikis WHERE wikiname = ?");
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if (! $sth->fetch(PDO::FETCH_ASSOC)) {
    		$sth = $this->dbh_tools->prepare('INSERT INTO wikis (wikiname, wikititle) VALUES (?,?)');
    		$sth->bindParam(1, $wikiname);
    		$sth->bindParam(2, $wikititle);
    		$sth->execute();
    	}

    	// See if this is a rerun
		$sth = $this->dbh_tools->prepare("SELECT * FROM runs WHERE wikiname=? AND rundate=?");
		$sth->bindParam(1, $wikiname);
		$sth->bindParam(2, $this->asof);
		$sth->execute();

		$rerun = false;
		if ($sth->fetch(PDO::FETCH_ASSOC)) {
			$rerun = true;

			$sth = $this->dbh_tools->prepare("DELETE FROM runs WHERE wikiname=? AND rundate=?");
			$sth->bindParam(1, $wikiname);
			$sth->bindParam(2, $this->asof);
			$sth->execute();

			$sth = $this->dbh_tools->prepare("DELETE FROM {$wikiname}_diffs WHERE diffdate=?");
			$sth->bindParam(1, $this->asof);
			$sth->execute();
		}

    	// Backup the previous run
		$dumppath = $this->outputdir . "$wikiname.dump";
		$bakdumppath = $this->outputdir . "$wikiname.dump.bak";
		$diffpath = $this->outputdir . "$wikiname.diff";

    	if (! $rerun && file_exists($dumppath)) {
			@unlink($bakdumppath);
			rename($dumppath, $bakdumppath);
		}

		// Dump the current category links
		$dumphndl = fopen($dumppath . '.unsorted', 'wb');

		$sql = "SELECT cl_from, cl_to FROM categorylinks ORDER BY cl_from, cl_to";
		$sth = $dbh_wiki->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute();

		while($row = $sth->fetch()) {
			$pageid = str_pad($row['cl_from'], 10, '0', STR_PAD_LEFT);
			fwrite($dumphndl, "$pageid|{$row['cl_to']}\n");
		}

		fclose($dumphndl);

		// Guarantee that it is sorted to comm's liking.
		$command = "sort $dumppath.unsorted >$dumppath ; rm $dumppath.unsorted";
		system($command);

		if (! file_exists($bakdumppath)) {
			Logger::log("Previous dump not found for $wikiname");
			return;
		}

		// Calc the diff
		$command = "comm -3 $bakdumppath $dumppath >$diffpath";
		system($command);

		// Load the diff
		$diffhndl = fopen($diffpath, 'rb');
		$this->dbh_tools->beginTransaction();

		$isth = $this->dbh_tools->prepare("INSERT INTO {$wikiname}_diffs (diffdate, plusminus, pageid, category) VALUES (?,?,?,?)");
		$insert_count = 0;

		while (! feof($diffhndl)) {
			$buffer = rtrim(fgets($diffhndl)); // Don't want to trim off leading tab
			$tabpos = strpos($buffer, "\t");
			if ($tabpos !== false) list($minus, $plus) = explode("\t", $buffer);
			else {
				$minus = $buffer;
				$plus = '';
			}

			foreach (array('-' => $minus, '+' => $plus) as $type => $catpage) {
				if (! empty($catpage)) {
					++$insert_count;
					if ($insert_count % 1000 == 0) {
						$this->dbh_tools->commit();
						$this->dbh_tools->beginTransaction();
					}

					list($pageid, $category) = explode('|', $catpage);
					$pageid = (int)$pageid;

		    		$isth->bindParam(1, $this->asof);
		    		$isth->bindParam(2, $type);
					$isth->bindParam(3, $pageid);
		    		$isth->bindParam(4, $category);
		    		$isth->execute();
				}
			}
		}

    	$this->dbh_tools->commit();
		fclose($diffhndl);

		// Update the runs table
		$isth = $this->dbh_tools->prepare("INSERT INTO runs (wikiname, rundate) VALUES (?,?)");
		$isth->bindParam(1, $wikiname);
		$isth->bindParam(2, $this->asof);
		$isth->execute();

    }
}
