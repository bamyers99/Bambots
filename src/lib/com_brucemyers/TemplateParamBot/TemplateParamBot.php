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
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Convert;
use PDO;
use Exception;

class TemplateParamBot
{
    const ERROREMAIL = 'TemplateParamBot.erroremail';
    const MAX_INSTANCE_CNT = 'TemplateParamBot.max_instance_cnt';

    const CACHE_PREFIX_RESULT = 'Result:';
    const CACHE_PREFIX_ATOM = 'Atom:';

    protected $ruleconfigs;
    protected $serviceMgr;
    protected $parserState;
    protected $templates;
    protected $template_ids;
    protected $highest_revision_id;
    protected $outputdir;
    protected $wikiname;

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
		    case 'query':
		    	if ($argc < 3) {
		    		return 'No query id supplied';
		    	}

		    	$queryid = (int)$argv[2];
		    	$errmsg = $this->processQuery($queryid);
		    	break;

		    case 'processxmldump':
				if ($argc < 4) {
		    		return 'No filepath and/or output dir supplied';
		    	}

		   		$errmsg = $this->processXMLDump($argv[2], $argv[3]);
		    	break;

		    case 'gensqlimport':
				if ($argc < 5) {
		    		return 'No template filepath and/or value filepath and/or output dir supplied';
		    	}

		   		$errmsg = $this->generateSQLImport($argv[2], $argv[3], $argv[4]);
		    	break;

		    default:
		    	return 'Unknown action = ' . $action;
		    	break;
		}

		return $errmsg;
    }

    /**
     * Process a query
     *
     * @param int $queryid
     * @return string Error message
     */
    public function processQuery($queryid)
    {

    	$filepath = "compress.bzip2://$outputdir$wikiname-tmpl-param-values.csv.bz2";
    	$ihndl = fopen($filepath, 'r');

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
    		`value_count` int unsigned NOT NULL,
    		`last_update` datetime NOT NULL,
    		`revision_id` int unsigned NOT NULL,
    		UNIQUE `name` (`name`)
    		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_values` (
    		`page_id` int unsigned NOT NULL,
	    	`template_id` int unsigned NOT NULL,
    		`instance_num` int unsigned NOT NULL,
    		`param_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    		`param_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    		KEY `template_id` (`template_id`, `param_name`),
    		KEY `page_id` (`page_id`)
    		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$dbh_tools->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `{$wikiname}_totals` (
    		`template_id` int unsigned NOT NULL,
    		`param_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    		`value_count` int unsigned NOT NULL,
    		`unique_value_count` int unsigned NOT NULL,
    		`unique_values` blob NOT NULL,
    		KEY `template_id` (`template_id`)
    		) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    	$dbh_tools->exec($sql);

		$dbh_tools->exec("TRUNCATE {$wikiname}_templates");
		$dbh_tools->exec("TRUNCATE {$wikiname}_values");
		$dbh_tools->exec("TRUNCATE {$wikiname}_totals");

    	preg_match('!(\\d{4})(\\d{2})(\\d{2})!', $dumpdate, $dd);

    	$last_update = "{$dd[1]}-{$dd[2]}-{$dd[3]} 00:00:00";
    	$templatecnt = $templateinstancecnt = $templatevaluecnt = 0;

    	// Write the template info
    	foreach ($this->templates as $tmplid => &$tp) {
    		if (! $tp['valuecnt']) continue;

    		$sth = $dbh_tools->prepare("INSERT INTO {$wikiname}_templates VALUES (?,?,?,?,?,?,?)");
   			$sth->execute(array($tmplid, $tp['name'], $tp['pagecnt'], $tp['instancecnt'], $tp['valuecnt'],
   				$last_update, $this->highest_revision_id));

   			++$templatecnt;
   			$templateinstancecnt += $tp['instancecnt'];
   			$templatevaluecnt += $tp['valuecnt'];

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
    			revision_id,templatecnt,templateinstancecnt,templatevaluecnt) VALUES (?,?,?,?,?,?,?,?,?,?)');
   			$sth->execute(array($wikiname, $wikidata['title'], $wikidata['domain'], $wikidata['templateNS'], $wikidata['lang'],
   				$dumpdate, $this->highest_revision_id, $templatecnt, $templateinstancecnt, $templatevaluecnt));
   			$sth = null;
    	} else {
			$sth = $dbh_tools->prepare('UPDATE wikis SET revision_id = ?, lastdumpdate = ?, templatecnt = ?,
				templateinstancecnt = ?, templatevaluecnt = ? WHERE wikiname = ?');
    		$sth->execute(array($this->highest_revision_id, $dumpdate, $templatecnt, $templateinstancecnt, $templatevaluecnt, $wikiname));
    	}

    	$sth = null;
    	$dbh_tools = null;
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

    /**
     * Clear the xml parser state
     */
    function clearParserState()
    {
    	$this->parserState = array('container' => '', 'element' => '', 'page_id' => '', 'namespace' => '', 'data' => '',
    		'page_title' => '', 'revision_id' => 0);
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
    				case 'title':
    					$this->parserState['page_title'] .= $data;
    					break;

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
    		$this->templates[$templid] = array('name' => $templname,'pagecnt' => 0, 'instancecnt' => 0, 'valuecnt' => 0,
    				'values' => array());

    		foreach ($redirtmpls as $templname) {
    			$templname = str_replace('_', ' ', $templname);
    			$this->template_ids[$templname] = $templid;
    		}
    	}

    	$sth = null;
    	$dbh_tools = null;
    }
}