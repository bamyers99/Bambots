<?php
/**
 Copyright 2016 Myers Enterprises II

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

namespace com_brucemyers\TemplateParamBot;

use com_brucemyers\Util\TemplateParamParser;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Convert;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\MySQLDate;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Curl;
use com_brucemyers\Util\Logger;
use PDO;
use Exception;

/**
 * Sample usage:
 *
 * jsub curl http://website/enwikiTemplateParams -o /data/project/bambots/Bambots/data/TemplateParamBot/enwiki-20180101-TemplateParams
 * jsub curl http://website/enwikiTemplateTotals -o /data/project/bambots/Bambots/data/TemplateParamBot/enwiki-20180101-TemplateTotals
 * jsub curl http://website/enwikiTemplateOffsets -o /data/project/bambots/Bambots/data/TemplateParamBot/enwiki-20180101-TemplateOffsets
 * jsub -N TemplateParamBot -cwd -mem 768m php TemplateParamBot.php loadtotalsoffsets /data/project/bambots/Bambots/data/TemplateParamBot/enwiki-20180101-TemplateTotals /data/project/bambots/Bambots/data/TemplateParamBot/enwiki-20180101-TemplateOffsets
 * jsub -N TemplateParamBot -cwd -mem 768m php TemplateParamBot.php dumptemplateids enwiki
 */
class TemplateParamBot
{
    const ERROREMAIL = 'TemplateParamBot.erroremail';
    const MAX_INSTANCE_CNT = 'TemplateParamBot.max_instance_cnt';
    const DATADIR = 'TemplateParamBot.datadir';
    const LOAD_COMMAND = 'TemplateParamBot.load_command';

    protected $ruleconfigs;
    protected $serviceMgr;
    protected $parserState;
    protected $templates;
    protected $template_ids;
    protected $highest_revision_id;
    protected $outputdir;
    protected $wikiname;
    static $yesno = array('yes', 'y', 'true', '1',
		'no', 'n', 'false', '0');

    public function __construct(&$ruleconfigs)
    {
    	$this->ruleconfigs = $ruleconfigs;
    	$this->serviceMgr = new ServiceManager();
    }

    /**
     * Do an action
     *
     * @param int $argc
     * @param array $argv
     * @return string Error messsage
     */
    public function doAction($argc, $argv)
    {
    	$errmsg = '';

    	if ($argc < 2) {
    		return 'No action supplied';
    	}

		$action = $argv[1];
		switch ($action) {
		    case 'processloads':
		    	$errmsg = $this->processLoads();
		    	break;

		    case 'processxmldump':
				if ($argc < 4) {
		    		return 'No filepath and/or output dir supplied';
		    	}

		   		$errmsg = $this->processXMLDump($argv[2], $argv[3]);
		    	break;

	    	case 'processparamdump':
	    		if ($argc < 4) {
	    			return 'No filepath and/or output dir supplied';
	    		}

	    		$errmsg = $this->processParamDump($argv[2], $argv[3]);
	    		break;

		    case 'loadtotalsoffsets':
    			if ($argc < 4) {
    				return 'No totals and/or offset paths supplied';
    			}

    			$errmsg = $this->loadTotalsOffsets($argv[2], $argv[3]);
    			break;

		    case 'dumptemplateids':
		    	if ($argc < 3) {
		    		return 'No wikiname supplied';
		    	}

		    	$errmsg = $this->dumpTemplateIds($argv[2]);
		    	break;
		    	
		    case 'autoprocess':
		        if ($argc < 4) {
		            return 'No wikiname and/or dump date supplied';
		        }
		        
		        $errmsg = $this->autoprocess($argv[2], $argv[3]);
		        break;

		    default:
		    	return 'Unknown action = ' . $action;
		    	break;
		}

		return $errmsg;
    }

    /**
     * Process load requests
     *
     * @return string Error message
     */
    public function processLoads()
    {
        $datadir = Config::get(TemplateParamBot::DATADIR);
        $datadir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $datadir);
        $datadir = preg_replace('!(/|\\\\)$!', '', $datadir); // Drop trailing slash
        $datadir .= DIRECTORY_SEPARATOR;
        $rowsfound = true;
        $templateParamConfig = new TemplateParamConfig($this->serviceMgr);

    while ($rowsfound) {

        $dbh_tools = $this->serviceMgr->getDBConnection('tools');
        $sth = $dbh_tools->query("SELECT wikiname, template_id FROM loads WHERE status = 'S'");
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
        $timer = new Timer();
        $rowsfound = false;

        foreach ($rows as $row) {
        	$rowsfound = true;
        	$timer->start();
        	$wikiname = $row['wikiname'];
        	$templid = $row['template_id'];

        	// Get the template data
        	$ruleconfig = $this->ruleconfigs[$wikiname];
        	$mediawiki = $this->serviceMgr->getMediaWiki($ruleconfig['domain'], false);

        	$sth = $dbh_tools->query("SELECT * FROM `{$wikiname}_templates` WHERE id = $templid");
        	$template = $sth->fetch(PDO::FETCH_ASSOC);
        	$templname = $template['name'];

        	$query = '?action=templatedata&format=php&titles=' . urlencode($ruleconfig['templateNS'] . ':' . $templname);

        	$ret = $mediawiki->query($query);

        	if (isset($ret['error'])) {
        	    Logger::log('TemplateParamBot->processLoads Error ' . $ret['error']['info']);
        	    $ret = [];
        	}

        	if (! empty($ret) && ! empty($ret['pages'])) {
        	    $templatedata = new TemplateData(null, reset($ret['pages']));
    			$templatedata->enhanceConfig($templateParamConfig->getTemplate($templname));
        		$paramdefs = $templatedata->getParams();
        	} else {
        		$ts = $timer->stop();
        		$lastrun = MySQLDate::toMySQLDatetime(time());
        		$runtime = $ts['hours'] . ':' . $ts['minutes'] . ':' . $ts['seconds'];

        		$sth = $dbh_tools->prepare("UPDATE loads SET status = 'C', progress = ?, lastrun = ?, runtime = ? WHERE wikiname = ? AND template_id = $templid");
        		$sth->execute(array("Loaded 0 instances for $templid", $lastrun, $runtime, $wikiname));
        		$dbh_tools->exec("UPDATE `{$wikiname}_templates` SET loaded = 'Y' WHERE id = $templid");
        		continue;
        	}

        	// Calc validations
        	$validations = array();

        	foreach ($paramdefs as $param_name => $paramdef) {
        	    if (isset($paramdef['type']) && $paramdef['type'] == 'yesno') $validations[$param_name] = array('type' => 'yesno');
	        	elseif (isset($paramdef['regex'])) $validations[$param_name] = array('type' => 'regex', 'regex' => "!^{$paramdef['regex']}$!u");
	        	elseif (isset($paramdef['values'])) $validations[$param_name] = array('type' => 'values', 'values' => $paramdef['values']);
        	}

        	$offset = intval($template['file_offset']);
        	$dumpdate = date('Ymd', strtotime($template['last_update']));
        	$instancecnt = $template['instance_count'];

        	if ($offset < 0) {
        		if ($offset == -1) {
			    	$sth = $dbh_tools->prepare("UPDATE loads SET status = 'C' WHERE wikiname = ? AND template_id = $templid");
			    	$sth->execute(array($wikiname));
		        	$dbh_tools->exec("UPDATE `{$wikiname}_templates` SET loaded = 'Y' WHERE id = $templid");
        			return '';
        		}

        		$offset = -$offset;
        		$instancecnt = '?';
        	}

        	$sth = $dbh_tools->prepare("UPDATE loads SET status = 'R', progress = ? WHERE wikiname = ? AND template_id = $templid");
        	$sth->execute(array("Loading $instancecnt instances for $templname", $wikiname));

	    	$filepath = $datadir . 'TemplateParamBot' . DIRECTORY_SEPARATOR . "$wikiname-$dumpdate-TemplateParams";
	    	$hndl = fopen($filepath, 'r');
	    	if ($hndl === false) {
	    		$sth = $dbh_tools->prepare("UPDATE loads SET status = 'E', progress = ? WHERE wikiname = ? AND template_id = $templid");
	    		$sth->execute(array("File not found = $wikiname-$dumpdate-TemplateParams", $wikiname));
	    		continue;
	    	}
	    	fseek($hndl, $offset);

	    	$prev_pageid = '';
	    	$loadedcnt = 0;
	    	$valuecnt = 0;
	    	$sth = $dbh_tools->prepare("INSERT INTO `{$wikiname}_values` VALUES (?,?,?,?,?)");
			$sthprogress = $dbh_tools->prepare("UPDATE loads SET progress = ? WHERE wikiname = ? AND template_id = $templid");
			$sthmissing = $dbh_tools->prepare("INSERT INTO `{$wikiname}_missings` VALUES (?,?,?)");
			$sthinvalid = $dbh_tools->prepare("INSERT INTO `{$wikiname}_invalids` VALUES (?,?,?)");
			$dbh_tools->beginTransaction();

	    	while (! feof($hndl)) {
	    		$line = rtrim(fgets($hndl), "\n");
	    		if (empty($line)) continue;

	    		$line = explode("\t", $line);
	    		$readid = $line[0];
	    		if ($readid != $templid) break;

				$pageid = $line[1];
				if ($pageid != $prev_pageid) {
					$instancenum = -1;
					$missing_written = array();
				}
				$prev_pageid = $pageid;
				++$instancenum;
				++$loadedcnt;

				$params_used = array();
				foreach ($paramdefs as $param_name => $paramdef) {
					if (isset($paramdef['required']) || isset($paramdef['suggested'])) {
						$params_used[$param_name] = false;
					}
				}

				$cnt = count($line);
				for ($x = 2; $x < $cnt; $x += 2) {
					$param_name = $line[$x];
					$param_value = $line[$x + 1];
					$sth->execute(array($pageid, $templid, $instancenum, $param_name, $param_value));

					if (isset($params_used[$param_name])) $params_used[$param_name] = true;

					// Value validation

					if (isset($validations[$param_name]) && ! empty($param_value)) {
						$writeinvalid = false;
						$validation = $validations[$param_name];

						switch ($validation['type']) {
							case 'yesno':
								$lparam_value = strtolower($param_value);
								if (! in_array($lparam_value, self::$yesno)) $writeinvalid = true;
								break;

							case 'regex':
								if (! preg_match($validation['regex'], $param_value)) $writeinvalid = true;
								break;

							case 'values':
								if (! in_array($param_value, $validation['values'])) $writeinvalid = true;
								break;
						}

						if ($writeinvalid) $sthinvalid->execute(array($templid, $param_name, $pageid));
					}

					++$valuecnt;
					if ($valuecnt % 2000 == 0) {
					    $sthprogress->execute(array("Loaded $loadedcnt of $instancecnt instances for $templname", $wikiname));

						$dbh_tools->commit();
						$dbh_tools->beginTransaction();
					}
				}

				foreach ($params_used as $param_name => $param_used) {
					if (! $param_used && ! isset($missing_written[$param_name])) {
						$sthmissing->execute(array($templid, $param_name, $pageid));
						$missing_written[$param_name] = true;
					}
				}
	    	}

	    	$dbh_tools->commit();
	    	fclose($hndl);

    		$ts = $timer->stop();
	    	$lastrun = MySQLDate::toMySQLDatetime(time());
	    	$runtime = $ts['hours'] . ':' . $ts['minutes'] . ':' . $ts['seconds'];

	    	$sth = $dbh_tools->prepare("UPDATE loads SET status = 'C', progress = ?, lastrun = ?, runtime = ? WHERE wikiname = ? AND template_id = $templid");
	    	$sth->execute(array("Loaded $instancecnt instances for $templname", $lastrun, $runtime, $wikiname));
        	$dbh_tools->exec("UPDATE `{$wikiname}_templates` SET loaded = 'Y' WHERE id = $templid");
        }
    }

    	return '';
    }

    /**
     * Process an XML page dump
     *
     * @param string $filepath
     * @param string $outputdir
     * @return string Error message
     */
    public function processXMLDump($filepath, $outputdir)
    {
    	if (! preg_match('!(\\w+)-(\\d{8})-pages-articles.xml.bz2!', $filepath, $matches)) {
    		return 'File path must resemble enwiki-20160113-pages-articles.xml.bz2';
    	}
    	if ($outputdir[0] != '/') {
    		return 'Output dir must start with /';
    	}
    	$wikiname = $matches[1];
    	$dumpdate = $matches[2];
    	$this->wikiname = $wikiname;

    	if (! isset($this->ruleconfigs[$wikiname])) {
    		return "Wikiname not found = $wikiname";
    	}

    	if (substr($outputdir, -1) != DIRECTORY_SEPARATOR) $outputdir .= DIRECTORY_SEPARATOR;
    	$outputdir .= 'TemplateParamBot' . DIRECTORY_SEPARATOR;
    	$this->outputdir = $outputdir;

    	// Open the compressed dump file
    	$fh = bzopen($filepath, 'r');
    	if (! $fh) {
    		return "Dump file not found = $filepath";
    	}

    	// Create the xml parser
    	$xml_parser = xml_parser_create();
    	xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
    	xml_set_element_handler($xml_parser, array($this, 'startElement'), array($this, 'endElement'));
    	xml_set_character_data_handler($xml_parser, array($this, 'characterData'));

    	$this->clearParserState();
    	$this->highest_revision_id = 0;
    	$this->loadTemplateData($wikiname);

    	// Delete the value files
    	$tmplvalpath = $outputdir . $wikiname;
    	if (is_dir($tmplvalpath)) exec('rm -rf ' . $tmplvalpath);
    	clearstatcache();
    	if (! is_dir($tmplvalpath)) mkdir($tmplvalpath, 0775);

    	// Parse the xml
    	while(! feof($fh)) {
    		$buffer = bzread($fh);
    		if($buffer === FALSE || bzerrno($fh) !== 0) {
    			$bzerrno = bzerrno($fh);
    			return "bzread failed errno = $bzerrno";
    		}

    		if (! xml_parse($xml_parser, $buffer, feof($fh))) {
    			return sprintf('XML error: %s at line %d',
    					xml_error_string(xml_get_error_code($xml_parser)),
    					xml_get_current_line_number($xml_parser));
    		}
    	}

    	// Compress the value files
    	$subdirs = array_diff(scandir($tmplvalpath), array('..', '.'));
    	foreach ($subdirs as $subdir) {
    		if (! is_numeric($subdir)) continue;
    		$fullpath = $tmplvalpath . DIRECTORY_SEPARATOR . $subdir;
    		if (! is_dir($fullpath)) continue;
    		exec('bzip2 -q ' . $fullpath . DIRECTORY_SEPARATOR . '*.csv');
    	}

    	$this->updateTables($wikiname, $dumpdate);

    	return '';
    }

    /**
     * Process a template parameter dump
     *
     * @param string $infilepath
     * @param string $outputdir
     * @return string Error message
     */
    public function processParamDump($infilepath, $outputdir)
    {
    	if (! preg_match('!(\\w+)-(\\d{8})-TemplateParams.bz2!', $infilepath, $matches)) {
    		return 'File path must resemble enwiki-20160113-TemplateParams.bz2';
    	}
    	if ($outputdir[0] != '/') {
    		return 'Output dir must start with /';
    	}
    	$wikiname = $matches[1];
    	$dumpdate = $matches[2];
    	$this->wikiname = $wikiname;

		if (! isset($this->ruleconfigs[$wikiname])) {
			return "Wikiname not found = $wikiname";
		}

		if (substr($outputdir, -1) != DIRECTORY_SEPARATOR) $outputdir .= DIRECTORY_SEPARATOR;
		$outputdir .= 'TemplateParamBot' . DIRECTORY_SEPARATOR;
		$this->outputdir = $outputdir;

    	// Open the compressed dump file
    	$fh = fopen("compress.bzip2://$infilepath", 'r');
    	if (! $fh) {
    		return "Dump file not found = $infilepath";
    	}

    	$this->highest_revision_id = 0;
    	$this->loadTemplateData($wikiname);

    	// Delete the value files
    	$tmplvalpath = $outputdir . $wikiname;
    	if (is_dir($tmplvalpath)) exec('rm -rf ' . $tmplvalpath);
    	clearstatcache();
    	if (! is_dir($tmplvalpath)) mkdir($tmplvalpath, 0775);

    	$pageid = '';
    	$pagetemplates = array();

    	// Parse tsv file
    	while(! feof($fh)) {
			$buffer = fgets($fh);
			$buffer = rtrim($buffer, "\n");
			if (empty($buffer)) continue;

			$data = explode("\v", $buffer);

			if ($data[0][0] == 'P') { // page
				$pageid = substr($data[0], 1);
    			$revid = (int)$data[1];
				if ($revid > $this->highest_revision_id) $this->highest_revision_id = $revid;
				$pagetemplates = array();

			} else { // template
				$tmplid = substr($data[0], 1);
				if (! isset($this->templates[$tmplid])) continue; // template is gone

				if (! isset($pagetemplates[$tmplid])) $pagetemplates[$tmplid] = 0;
				++$pagetemplates[$tmplid];

				if ($pagetemplates[$tmplid] == 1) ++$this->templates[$tmplid]['pagecnt'];
				++$this->templates[$tmplid]['instancecnt'];

				// Write a line to the value file
				$values = array($pageid, $pagetemplates[$tmplid]);
				$paramcnt = count($data);

				for ($x=1; $x < $paramcnt; $x += 2) {
					$key = $data[$x];
					$value = $data[$x+1];
					$values[] = $key;
					$values[] = $value;
					++$this->templates[$tmplid]['valuecnt'];

					// Calc unique values
					if (! isset($this->templates[$tmplid]['values'][$key])) {
						$this->templates[$tmplid]['values'][$key] = array('cnt' => 0, 'vals' => array());
					}
					++$this->templates[$tmplid]['values'][$key]['cnt'];

					if ($this->templates[$tmplid]['values'][$key]['vals'] !== false) {
						if (! isset($this->templates[$tmplid]['values'][$key]['vals'][$value])) {
							$this->templates[$tmplid]['values'][$key]['vals'][$value] = 1;
						} else {
							++$this->templates[$tmplid]['values'][$key]['vals'][$value];
						}

						if (count($this->templates[$tmplid]['values'][$key]['vals']) == 50) {
							$this->templates[$tmplid]['values'][$key]['vals'] = false; // reclaim memory
						}
					}
				}

				$subdir = (Convert::crc16($tmplid) % 100);

				$filepath = $this->outputdir . $this->wikiname . DIRECTORY_SEPARATOR . $subdir;
				if (! is_dir($filepath)) mkdir($filepath, 0775);
				$filepath .= DIRECTORY_SEPARATOR . $tmplid . '.csv';
				$hndl = fopen($filepath, 'a');
				if (! $hndl) {
					throw new Exception("processPage - error opening $filepath");
				}

				$line = implode("\v", $values); // vertical tab
				fwrite($hndl, $line);
				fwrite($hndl, "\n");

				fclose($hndl);
			}
    	}

    	fclose($fh);

    	// Compress the value files
    	$subdirs = array_diff(scandir($tmplvalpath), array('..', '.'));
    	foreach ($subdirs as $subdir) {
    		if (! is_numeric($subdir)) continue;
    		$fullpath = $tmplvalpath . DIRECTORY_SEPARATOR . $subdir;
    		if (! is_dir($fullpath)) continue;
    		exec('bzip2 -q ' . $fullpath . DIRECTORY_SEPARATOR . '*.csv');
    	}

    	$this->updateTables($wikiname, $dumpdate);

    	return '';
    }

	/**
     * Update the tables
     *
     * @param string $wikiname
     * @param string $dumpdate
     */
    function updateTables($wikiname, $dumpdate)
    {
    	$dbh_tools = $this->serviceMgr->getDBConnection('tools');

    	new CreateTables($dbh_tools);

		$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_templates` (
    		`id` int unsigned NOT NULL PRIMARY KEY,
    		`name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    		`page_count` int unsigned NOT NULL,
    		`instance_count` int unsigned NOT NULL,
    		`file_offset` bigint NOT NULL,
    		`last_update` datetime NOT NULL,
    		`revision_id` int unsigned NOT NULL,
    		UNIQUE `name` (`name`),
    		KEY `instance_count` (`instance_count` DESC)
    		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_values` (
    		`page_id` int unsigned NOT NULL,
	    	`template_id` int unsigned NOT NULL,
    		`instance_num` int unsigned NOT NULL,
    		`param_name` varbinary(255) NOT NULL,
    		`param_value` varbinary(500) NOT NULL,
    		KEY `template_id` (`template_id`, `param_name`),
    		KEY `page_id` (`page_id`)
    		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_totals` (
    		`template_id` int unsigned NOT NULL,
    		`param_name` varbinary(255) NOT NULL,
    		`value_count` int unsigned NOT NULL,
    		`unique_values` blob NOT NULL,
    		KEY `template_id` (`template_id`)
    		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

		$dbh_tools->exec("TRUNCATE {$wikiname}_templates");
		$dbh_tools->exec("TRUNCATE {$wikiname}_values");
		$dbh_tools->exec("TRUNCATE {$wikiname}_totals");

    	preg_match('!(\\d{4})(\\d{2})(\\d{2})!', $dumpdate, $dd);

    	$last_update = "{$dd[1]}-{$dd[2]}-{$dd[3]} 00:00:00";
    	$templatecnt = $templateinstancecnt = 0;

    	// Write the template info
    	foreach ($this->templates as $tmplid => &$tp) {
    		if (! $tp['valuecnt']) continue;

    		$sth = $dbh_tools->prepare("INSERT INTO {$wikiname}_templates VALUES (?,?,?,?,?,?,?)");
   			$sth->execute(array($tmplid, $tp['name'], $tp['pagecnt'], $tp['instancecnt'],
   				$last_update, $this->highest_revision_id));

   			++$templatecnt;
   			$templateinstancecnt += $tp['instancecnt'];

   			$sth = $dbh_tools->prepare("INSERT INTO {$wikiname}_totals VALUES (?,?,?,?,?)");

   			foreach ($tp['values'] as $key => &$data) {

   				if ($data['vals'] === false) {
   					$uniquecount = 50;
   					$uniquevalues = '';
   				} else {
   					$uniquecount = count($data['vals']);
   					$tmp = array();
   					foreach ($data['vals'] as $val => $cnt) {
   						$tmp[] = $val;
   						$tmp[] = $cnt;
   					}
   					$uniquevalues = implode("\v", $tmp);
   				}

   				$sth->execute(array($tmplid, $key, $data['cnt'], $uniquecount, $uniquevalues));
   			}

   			unset($data);
	   	}

    	unset($tp);

    	// Add/update the wiki table entry
    	$sth = $dbh_tools->prepare("SELECT wikititle FROM wikis WHERE wikiname = ?");
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if (! $sth->fetch(PDO::FETCH_ASSOC)) {
    		$wikidata = $this->ruleconfigs[$wikiname];
    		$sth = $dbh_tools->prepare('INSERT INTO wikis (wikiname,wikititle,wikidomain,templateNS,lang,lastdumpdate,
    			revision_id,templatecnt,templateinstancecnt) VALUES (?,?,?,?,?,?,?,?,?,?)');
   			$sth->execute(array($wikiname, $wikidata['title'], $wikidata['domain'], $wikidata['templateNS'], $wikidata['lang'],
   				$dumpdate, $this->highest_revision_id, $templatecnt, $templateinstancecnt));
   			$sth = null;
    	} else {
			$sth = $dbh_tools->prepare('UPDATE wikis SET revision_id = ?, lastdumpdate = ?, templatecnt = ?,
				templateinstancecnt = ? WHERE wikiname = ?');
    		$sth->execute(array($this->highest_revision_id, $dumpdate, $templatecnt, $templateinstancecnt, $wikiname));
    	}

    	$sth = null;
    	$dbh_tools = null;
    }

    /**
     * Load totals and offsets.
     *
     * @param string $totalsfilepath
     * @param string $offsetsfilepath
     */
    function loadTotalsOffsets($totalsfilepath, $offsetsfilepath)
    {
        if (! preg_match('!(\\w+)-(\\d{8})-TemplateTotals!', $totalsfilepath, $matches)) {
    		return 'totalsfilepath must resemble enwiki-20160113-TemplateTotals';
    	}

    	$wikiname = $matches[1];
    	$dumpdate = $matches[2];

    	$dbh_tools = $this->serviceMgr->getDBConnection('tools');

    	new CreateTables($dbh_tools);

    	$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_templates` (
    	`id` int unsigned NOT NULL PRIMARY KEY,
    	`name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	`page_count` int unsigned NOT NULL,
    	`instance_count` int unsigned NOT NULL,
    	`file_offset` bigint NOT NULL,
    	`last_update` datetime NOT NULL,
    	`revision_id` int unsigned NOT NULL,
    	`loaded` char CHARACTER SET utf8 COLLATE utf8_bin,
    	UNIQUE `name` (`name`),
    	KEY `instance_count` (`instance_count` DESC)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_values` (
    	`page_id` int unsigned NOT NULL,
    	`template_id` int unsigned NOT NULL,
    	`instance_num` int unsigned NOT NULL,
    	`param_name` varbinary(255) NOT NULL,
    	`param_value` varbinary(500) NOT NULL,
    	KEY `template_id` (`template_id`, `param_name`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_totals` (
    	`template_id` int unsigned NOT NULL,
    	`param_name` varbinary(255) NOT NULL,
    	`value_count` int unsigned NOT NULL,
    	`unique_values` blob NOT NULL,
    	KEY `template_id` (`template_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_missings` (
    	`template_id` int unsigned NOT NULL,
    	`param_name` varbinary(255) NOT NULL,
    	`page_id` int unsigned NOT NULL,
    	KEY `template_id` (`template_id`, `param_name`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_invalids` (
    	`template_id` int unsigned NOT NULL,
    	`param_name` varbinary(255) NOT NULL,
    	`page_id` int unsigned NOT NULL,
    	KEY `template_id` (`template_id`, `param_name`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

    	$dbh_tools->exec("TRUNCATE `{$wikiname}_templates`");
    	$dbh_tools->exec("TRUNCATE `{$wikiname}_values`");
    	$dbh_tools->exec("TRUNCATE `{$wikiname}_totals`");
    	$dbh_tools->exec("TRUNCATE `{$wikiname}_missings`");
    	$dbh_tools->exec("TRUNCATE `{$wikiname}_invalids`");
    	$sth = $dbh_tools->prepare('DELETE FROM loads WHERE wikiname = ?');
    	$sth->execute(array($wikiname));

    	preg_match('!(\\d{4})(\\d{2})(\\d{2})!', $dumpdate, $dd);

    	$last_update = "{$dd[1]}-{$dd[2]}-{$dd[3]} 00:00:00";
    	$templatecnt = $templateinstancecnt = 0;

    	$hndl = fopen($totalsfilepath, 'r');
    	if ($hndl === false) return 'totalsfilepath not found';

    	$sth_template = $dbh_tools->prepare("INSERT INTO `{$wikiname}_templates` VALUES (?,?,?,?,?,?,?,?)");
    	$sth_total = $dbh_tools->prepare("INSERT INTO `{$wikiname}_totals` VALUES (?,?,?,?)");
    	$count = 0;
    	$dbh_tools->beginTransaction ();

    	while (! feof($hndl)) {
    		$buffer = fgets($hndl);
    		$buffer = rtrim($buffer, "\n");
    		if (empty($buffer)) continue;

    		$type = $buffer[0];
    		++$count;
    		if ($count % 1000 == 0) {
    			$dbh_tools->commit();
    			$dbh_tools->beginTransaction();
    		}

    		if ($type == 'T') {
    			$parts = explode("\t", $buffer);
    			$tmplid = substr($parts[0], 1);
    			$pagecnt = $parts[1];
    			$instancecnt = $parts[2];
    			$tmplname = $parts[3];

    			$sth_template->execute(array($tmplid, $tmplname, $pagecnt, $instancecnt, -1, $last_update, 0, 'N'));

    			++$templatecnt;
    			$templateinstancecnt += $instancecnt;

    		} else { // P
    			$parts = explode("\t", $buffer);
    			$paramname = substr($parts[0], 1);
    			$valuecnt = $parts[1];

    			$partcnt = count($parts);
    			if ($partcnt == 2) {
    				$uniquevalues = '';
    			} else {
					$tmp = array();

    				for ($x = 2; $x < $partcnt; $x += 2) {
    					$tmp[] = $parts[$x];
    					$tmp[] = $parts[$x+1];
					}
					$uniquevalues = implode("\t", $tmp);
    			}

   				$sth_total->execute(array($tmplid, $paramname, $valuecnt, $uniquevalues));
    		}
    	}

    	fclose($hndl);
    	$dbh_tools->commit();

    	// Load the offsets

    	$hndl = fopen($offsetsfilepath, 'r');
    	if ($hndl === false) return 'offsetsfilepath not found';

    	$sth_template = $dbh_tools->prepare("UPDATE `{$wikiname}_templates` SET file_offset = ? WHERE id = ?");
    	$count = 0;
    	$dbh_tools->beginTransaction ();

    	while (! feof($hndl)) {
    		$buffer = fgets($hndl);
    		$buffer = rtrim($buffer, "\n");
    		if (empty($buffer)) continue;
    		list($tmplid, $offset) = explode("\t", $buffer);

    	   	++$count;
    		if ($count % 1000 == 0) {
    			$dbh_tools->commit();
    			$dbh_tools->beginTransaction();
    		}

    		$sth_template->execute(array($offset, $tmplid));
    	}

    	fclose($hndl);
    	$dbh_tools->commit();

        // Add/update the wiki table entry
    	$sth = $dbh_tools->prepare("SELECT wikititle FROM wikis WHERE wikiname = ?");
    	$sth->bindParam(1, $wikiname);
    	$sth->execute();

    	if (! $sth->fetch(PDO::FETCH_ASSOC)) {
    		$wikidata = $this->ruleconfigs[$wikiname];
    		$sth = $dbh_tools->prepare('INSERT INTO wikis (wikiname,wikititle,wikidomain,templateNS,lang,lastdumpdate,
    			revision_id,templatecnt,templateinstancecnt) VALUES (?,?,?,?,?,?,?,?,?)');
   			$sth->execute(array($wikiname, $wikidata['title'], $wikidata['domain'], $wikidata['templateNS'], $wikidata['lang'],
   				$dumpdate, 0, $templatecnt, $templateinstancecnt));
   			$sth = null;
    	} else {
			$sth = $dbh_tools->prepare('UPDATE wikis SET revision_id = ?, lastdumpdate = ?, templatecnt = ?,
				templateinstancecnt = ? WHERE wikiname = ?');
    		$sth->execute(array(0, $dumpdate, $templatecnt, $templateinstancecnt, $wikiname));
    	}

    	return '';
    }

    /**
     * Process a wiki page
     */
    function processPage()
    {
    	static $pagecnt = 0;

    	$ns = (int)$this->parserState['namespace'];
    	if ($ns != 0) return; // only want articles
    	++$pagecnt;
    	if ($pagecnt % 100000 == 0) echo "$pagecnt\n";

    	$revid = (int)$this->parserState['revision_id'];
		if ($revid > $this->highest_revision_id) $this->highest_revision_id = $revid;

		// Parse the templates
		$templates = TemplateParamParser::getTemplates($this->parserState['data']);
		$pagetemplates = array();

		foreach ($templates as $template) {
			$tmplname = $template['name'];
			$params = $template['params'];
			if (! isset($this->template_ids[$tmplname])) continue;
			$tmplid = $this->template_ids[$tmplname];

			foreach ($params as $key => $value) {
				if (empty($value)) unset($params[$key]);
			}

			if (empty($params)) continue;

			if (! isset($pagetemplates[$tmplid])) $pagetemplates[$tmplid] = 0;
			++$pagetemplates[$tmplid];

			if ($pagetemplates[$tmplid] == 1) ++$this->templates[$tmplid]['pagecnt'];
			++$this->templates[$tmplid]['instancecnt'];

			// Write a line to the value file
			$values = array($this->parserState['page_id'], $pagetemplates[$tmplid]);

			foreach ($params as $key => $value) {
				$value = str_replace("\n", '<cr>', $value); // don't want newlines in csv file
				if (strlen($key) > 255) $key = substr($key, 0, 255);
				if (strlen($value) > 255) $value = substr($value, 0, 255);

				$values[] = $key;
				$values[] = $value;

				// Calc unique values
				if (! isset($this->templates[$tmplid]['values'][$key])) {
					$this->templates[$tmplid]['values'][$key] = array('cnt' => 0, 'vals' => array());
				}
				++$this->templates[$tmplid]['values'][$key]['cnt'];

				if ($this->templates[$tmplid]['values'][$key]['vals'] !== false) {
					if (! isset($this->templates[$tmplid]['values'][$key]['vals'][$value])) {
						$this->templates[$tmplid]['values'][$key]['vals'][$value] = 1;
					} else {
						++$this->templates[$tmplid]['values'][$key]['vals'][$value];
					}

					if (count($this->templates[$tmplid]['values'][$key]['vals']) == 50) {
						$this->templates[$tmplid]['values'][$key]['vals'] = false; // reclaim memory
					}
				}
			}

			$subdir = (Convert::crc16($tmplid) % 100);

			$filepath = $this->outputdir . $this->wikiname . DIRECTORY_SEPARATOR . $subdir;
			if (! is_dir($filepath)) mkdir($filepath, 0775);
			$filepath .= DIRECTORY_SEPARATOR . $tmplid . '.csv';
			$hndl = fopen($filepath, 'a');
			if (! $hndl) {
				throw new Exception("processPage - error opening $filepath");
			}

			$line = implode("\v", $values); // vertical tab
			fwrite($hndl, $line);
			fwrite($hndl, "\n");

			fclose($hndl);
		}
    }

    /**
     * Clear the xml parser state
     */
    function clearParserState()
    {
    	$this->parserState = array('container' => '', 'element' => '', 'page_id' => '', 'namespace' => '', 'data' => '',
    		'revision_id' => 0);
    }

    /**
     * XML parser startElement callback
     *
     * @param unknown $parser
     * @param string $name
     * @param array $attribs
     */
    function startElement($parser, $name, $attribs)
    {
    	$this->parserState['element'] = $name;

    	switch ($name) {
    		case 'page':
    			if ($this->parserState['container'] == '') $this->parserState['container'] = 'page';
    			break;

    		case 'revision':
    			if ($this->parserState['container'] == 'page') $this->parserState['container'] = 'revision';
    			break;
    	}
    }

    /**
     * XML parser endElement callback
     *
     * @param unknown $parser
     * @param string $name
     */
    function endElement($parser, $name)
    {
        switch ($this->parserState['container']) {
    		case 'page':
    			if ($name == 'page') {
    				$this->parserState['container'] = '';
    				$this->processPage();
    				$this->clearParserState();
    			}
    			break;

    		case 'revision':
    			if ($name == 'revision') $this->parserState['container'] = 'page';
    			break;
    	}

    	$this->parserState['element'] = '';
    }

    /**
     * XML parser characterData callback
     *
     * @param unknown $parser
     * @param string $data
      */
    function characterData($parser, $data)
    {
    	switch ($this->parserState['container']) {
    		case 'page':
    			switch ($this->parserState['element']) {
    				case 'ns':
    					$this->parserState['namespace'] .= $data;
    					break;

    				case 'id':
    					$this->parserState['page_id'] .= $data;
    					break;
    			}
    			break;

    		case 'revision':
    			switch ($this->parserState['element']) {
    				case 'id':
    					$this->parserState['revision_id'] .= $data;
    					break;

    				case 'text':
    					$this->parserState['data'] .= $data;
    					break;
    			}
    			break;
    	}
    }

    /**
     * Load template names and redirects to them
     *
     * @param string $wikiname
     */
    function loadTemplateData($wikiname)
    {
        $dbh_tools = $this->serviceMgr->getDBConnection($wikiname);

    	$sql = "SELECT p1.page_id, p1.page_title, GROUP_CONCAT(p2.page_title SEPARATOR '|') FROM page_props
    		STRAIGHT_JOIN page p1 ON pp_page = p1.page_id
    		LEFT JOIN redirect ON rd_namespace = p1.page_namespace AND rd_title = p1.page_title
    		LEFT JOIN page p2 ON rd_from = p2.page_id
    		WHERE pp_propname = 'templatedata'
    			AND p1.page_namespace = 10
    			AND p1.page_is_redirect = 0
    		GROUP BY p1.page_id";

    	$sth = $dbh_tools->query($sql);
    	$sth->setFetchMode(PDO::FETCH_NUM);

    	while ($row = $sth->fetch()) {
    	    $templid = $row[0];
    		$templname = str_replace('_', ' ', $row[1]);
    		$redirtmpls = explode('|', $row[2]);

    		$this->template_ids[$templname] = $templid;
    		$this->templates[$templid] = array('name' => $templname,'pagecnt' => 0, 'instancecnt' => 0,
    				'values' => array());

    		foreach ($redirtmpls as $templname) {
    			if (empty($templname)) continue;
    			$templname = str_replace('_', ' ', $templname);
    			$this->template_ids[$templname] = $templid;
    		}
    	}

    	$sth = null;
    	$dbh_tools = null;
    }

    /**
     * Dump template ids and redirects to them for templates with templatedata
     *
     * @param string $wikiname
     */
    function dumpTemplateIds($wikiname)
    {
    	$outpath = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . $wikiname . 'TemplateIds.tsv';
    	$hndl = fopen($outpath, 'w');
    	$templateParamConfig = new TemplateParamConfig($this->serviceMgr);
        $dbh_tools = $this->serviceMgr->getDBConnection($wikiname);

    	$sql = "SELECT p1.page_id, p1.page_title, GROUP_CONCAT(p2.page_title SEPARATOR '|'), pp_value FROM page_props
    		STRAIGHT_JOIN page p1 ON pp_page = p1.page_id
    		LEFT JOIN redirect ON rd_namespace = p1.page_namespace AND rd_title = p1.page_title
    		LEFT JOIN page p2 ON rd_from = p2.page_id
    		WHERE pp_propname = 'templatedata'
    			AND p1.page_namespace = 10
    			AND p1.page_is_redirect = 0
    		GROUP BY p1.page_id";

    	$sth = $dbh_tools->query($sql);
    	$sth->setFetchMode(PDO::FETCH_NUM);

    	while ($row = $sth->fetch()) {
    	    $templid = $row[0];
    		$templname = str_replace('_', ' ', $row[1]);
    		if (strpos($templname, '/doc') !== false) continue;
    		if (strpos($templname, '/sandbox') !== false) continue;
    		if (strpos($templname, '/testcases') !== false) continue;
    		if (strpos($templname, '/lua') !== false) continue;
    		$redirtmpls = explode('|', $row[2]);

    		$templatedata = new TemplateData($row[3]);
    		$templatedata->enhanceConfig($templateParamConfig->getTemplate($templname));
    		$paramdef = $templatedata->getParams();

    		fwrite($hndl, "$templname\t$templid");

    		foreach ($paramdef as $paramname => $config) {
    			if (isset($config['deprecated'])) $validparamname = 'D';
				else {
					$validparamname = 'Y';
					if (isset($config['required'])) $validparamname = 'R';
					elseif (isset($config['suggested'])) $validparamname = 'S';
				}

				$aliases = '';
				if (isset($config['aliases']) && ! empty($config['aliases'])) {
					$aliases = '|' . implode('|', $config['aliases']);
				}

    			fwrite($hndl, "\t$paramname$aliases\t$validparamname\t");

				if ($config['type'] == 'yesno') fwrite($hndl, 'Y');
				elseif (isset($config['regex'])) fwrite($hndl, "R\t" . $config['regex']);
				elseif (isset($config['values'])) fwrite($hndl, "V\t" . implode(';', $config['values']));
				else fwrite($hndl, '-');
    		}

    		fwrite($hndl, "\n");

    		foreach ($redirtmpls as $templname) {
    			if (empty($templname)) continue;
	    		if (strpos($templname, '/doc') !== false) continue;
	    		if (strpos($templname, '/sandbox') !== false) continue;
	    		if (strpos($templname, '/testcases') !== false) continue;
	    		if (strpos($templname, '/lua') !== false) continue;

	    		$templname = str_replace('_', ' ', $templname);
    			fwrite($hndl, "$templname\t$templid\n");
     		}
    	}

		fclose($hndl);
    	$sth = null;
    	$dbh_tools = null;

    	echo "Template IDs written to $outpath\n";
    }
    
    /**
     * Autoprocess a dump file.
     * 
     * curl pages-articles.xml.bz2
     * retrieve TemplateData into TemplateIds.tsv
     * rm /var/www/projects/Bambots/data/TemplateParamBot/enwiki*
     * bunzip2 -c *pages-articles.xml.bz2 | ./MWDumpTemplateParser -v - enwikiTemplateParams enwikiTemplateTotals
     * LC_ALL=C sort -n -k 1,1 -k 2,2 enwikiTemplateParams >enwikiTemplateParams.sorted
     * ./MWDumpTemplateParser -offsets enwikiTemplateParams.sorted enwikiTemplateOffsets
     * mv enwiki* /var/www/projects/bambots/Bambots/data/TemplateParamBot
     * php TemplateParamBot.php loadtotalsoffsets /var/www/projects/Bambots/data/TemplateParamBot/enwiki-20180101-TemplateTotals /var/www/projects/Bambots/data/TemplateParamBot/enwiki-20180101-TemplateOffsets
     *
     * @param string $wiki
     * @param string $date yyyymmdd
     */
    function autoprocess($wiki, $date)
    {
        if (! isset($this->ruleconfigs[$wiki])) return "wiki not supported = $wiki";
        $ruleconfig = $this->ruleconfigs[$wiki];
        
        Logger::log("Start autoprocess: $wiki $date");
        $totaltimer = new Timer();
        $totaltimer->start();
        
        // Curl
        
        $url = "https://dumps.wikimedia.org/$wiki/$date/$wiki-$date-pages-articles.xml.bz2";
        $outfile = '/wiki/pages-articles.xml.bz2';
        
        Logger::log("  Start curl $url");
        $retval = Curl::saveUrlContents($url, $outfile);
        
        if (! $retval || ! empty(Curl::$lastError) || Curl::$lastResponseCode < 200 || Curl::$lastResponseCode >= 300) {
            return "curl error ($url) " . Curl::$lastError . " responsecode:" . Curl::$lastResponseCode;
        }
        
        // Retrieve TemplateData
        
        $outfile = '/wiki/TemplateIds.tsv';
        Logger::log("  Start TemplateData retrieval to $outfile");
        $retval = $this->retrieveTemplateIds($ruleconfig, $outfile);
        
        if (! empty($retval)) return $retval;
        
        // rm data files to free up space
        
        $command = "rm /var/www/projects/Bambots/data/TemplateParamBot/$wiki-*";
        Logger::log("  $command");
        system($command);
        
        // Parse page template parameters
        
        $command = "cd /wiki; bunzip2 -c pages-articles.xml.bz2 | ./MWDumpTemplateParser -v - TemplateParams TemplateTotals";
        Logger::log("  $command");
        system($command);
        
        // Sort the parameters
        
        $command = "cd /wiki; LC_ALL=C sort -n -k 1,1 -k 2,2 TemplateParams >TemplateParams.sorted";
        Logger::log("  $command");
        system($command);
        
        // Calc the template offsets
        
        $command = "cd /wiki; ./MWDumpTemplateParser -offsets TemplateParams.sorted TemplateOffsets";
        Logger::log("  $command");
        system($command);
        
        // Move the data files to the data directory
        
        $command = "mv /wiki/TemplateParams.sorted /var/www/projects/bambots/Bambots/data/TemplateParamBot/$wiki-$date-TemplateParams";
        Logger::log("  $command");
        system($command);
        
        $command = "mv /wiki/TemplateTotals /var/www/projects/bambots/Bambots/data/TemplateParamBot/$wiki-$date-TemplateTotals";
        Logger::log("  $command");
        system($command);
        
        $command = "mv /wiki/TemplateOffsets /var/www/projects/bambots/Bambots/data/TemplateParamBot/$wiki-$date-TemplateOffsets";
        Logger::log("  $command");
        system($command);
        
        // Load the template info into the database
        
        $retval = $this->loadTotalsOffsets("/var/www/projects/Bambots/data/TemplateParamBot/$wiki-$date-TemplateTotals",
            "/var/www/projects/Bambots/data/TemplateParamBot/$wiki-$date-TemplateOffsets");
        
        if (! empty($retval)) return $retval;
        
        // Cleanup
        
        $command = "cd /wiki; rm TemplateParams; rm TemplateIds.tsv";
        system($command);
        
        $ts = $totaltimer->stop();
        $totaltime = sprintf("%d:%02d:%02d", $ts['hours'], $ts['minutes'], $ts['seconds']);
        Logger::log("Elapsed time: $totaltime");
        
        return '';
    }
    
    /**
     * Retrieve template data via api and save to file.
     * 
     * @param array $ruleconfig
     * @param string $outfile
     * @return string error message
     */
    function retrieveTemplateIds($ruleconfig, $outfile)
    {
        $mediawiki = $this->serviceMgr->getMediaWiki($ruleconfig['domain'], false);
        $templateParamConfig = new TemplateParamConfig($this->serviceMgr);
        $templateNS = $ruleconfig['templateNS'];
        $hndl = fopen($outfile, 'w');
        
        // Get TemplateData
        
        $tdparams = [
            'generator' => 'pageswithprop',
            'gpwppropname' => 'templatedata',
            'gpwpprop' => 'ids',
            'gpwplimit' => 'max'
        ];
        
        $continue = ['continue' => ''];
        
        while ($continue !== false) {
            $tdparams = array_merge($tdparams, $continue);
            
            $ret = $mediawiki->getTemplateData($tdparams);
            
            if (isset($ret['error'])) return 'retrieveTemplateIds Get TemplateData failed ' . $ret['error'];
            if (isset($ret['continue'])) $continue = $ret['continue'];
            else $continue = false;
            
            if (! empty($ret['pages'])) {
                foreach ($ret['pages'] as $templid => $pagedata) {
                    $templname = str_replace('_', ' ', $pagedata['title']);
                    if (strpos($templname, "$templateNS:") !== 0) continue;
                    $templname = str_replace("$templateNS:", '', $templname);
                    if (strpos($templname, '/doc') !== false) continue;
                    if (strpos($templname, '/sandbox') !== false) continue;
                    if (strpos($templname, '/testcases') !== false) continue;
                    if (strpos($templname, '/lua') !== false) continue;
                    
                    $templatedata = new TemplateData(null, $pagedata);
                    $templatedata->enhanceConfig($templateParamConfig->getTemplate($templname));
                    $paramdef = $templatedata->getParams();
                    
                    fwrite($hndl, "$templname\t$templid");
                    
                    foreach ($paramdef as $paramname => $config) {
                        if (isset($config['deprecated'])) $validparamname = 'D';
                        else {
                            $validparamname = 'Y';
                            if (isset($config['required'])) $validparamname = 'R';
                            elseif (isset($config['suggested'])) $validparamname = 'S';
                        }
                        
                        $aliases = '';
                        if (isset($config['aliases']) && ! empty($config['aliases'])) {
                            $aliases = '|' . implode('|', $config['aliases']);
                        }
                        
                        fwrite($hndl, "\t$paramname$aliases\t$validparamname\t");
                        
                        if ($config['type'] == 'yesno') fwrite($hndl, 'Y');
                        elseif (isset($config['regex'])) fwrite($hndl, "R\t" . $config['regex']);
                        elseif (isset($config['values'])) fwrite($hndl, "V\t" . implode(';', $config['values']));
                        else fwrite($hndl, '-');
                    }
                    
                    fwrite($hndl, "\n");
                }
            }
        }
        
        // Get template redirects
        
        $tdparams = [
            'rdnamespace' => '10',
            'rdlimit' => 'max',
            'generator' => 'pageswithprop',
            'gpwppropname' => 'templatedata',
            'gpwpprop' => 'ids',
            'gpwplimit' => 'max'
        ];
        
        $continue = ['continue' => ''];
        
        while ($continue !== false) {
            $tdparams = array_merge($tdparams, $continue);
            
            $ret = $mediawiki->getProp('redirects', $tdparams);
            
            if (isset($ret['error'])) return 'retrieveTemplateIds Get template redirects failed ' . $ret['error'];
            if (isset($ret['continue'])) $continue = $ret['continue'];
            else $continue = false;
            
            if (! empty($ret['query']['pages'])) {
                foreach ($ret['query']['pages'] as $templid => $pagedata) {
                    if (! empty($pagedata['redirects'])) {
                        foreach ($pagedata['redirects'] as $redirectdata) {
                            $templname = str_replace('_', ' ', $redirectdata['title']);
                            $templname = str_replace("$templateNS:", '', $templname);
                            fwrite($hndl, "$templname\t$templid\n");
                        }
                    }
                }
            }
        }
        
        fclose($hndl);
        
        return '';
    }
}