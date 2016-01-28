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

use com_brucemyers\Util\CSVString;
use com_brucemyers\Util\TemplateParamParser;
use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Properties;
use PDO;

class TemplateParamBot
{
    const ERROREMAIL = 'TemplateParamBot.erroremail';
    const MIN_PAGE_CNT = 'TemplateParamBot.min_page_cnt';
    const MAX_INSTANCE_CNT = 'TemplateParamBot.max_instance_cnt';

    const CACHE_PREFIX_RESULT = 'Result:';
    const CACHE_PREFIX_ATOM = 'Atom:';

    protected $ruleconfigs;
    protected $serviceMgr;
    protected $parserState;
    protected $templates;
    protected $tmpl_value_hndl;
    protected $highest_revision_id;

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

		    case 'createtables':
				$dbh_tools = $this->serviceMgr->getDBConnection('tools');
				new CreateTables($dbh_tools);
				$errmsg = '';
		    	break;

		    case 'dumptemplatedata':
				if ($argc < 4) {
		    		return 'No wikiname and/or output dir supplied';
		    	}

		    	$errmsg = $this->dumpTemplateData($argv[2], $argv[3]);
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
    	$wikiname = $matches[1];
    	$dumpdate = $matches[2];

		if (! isset($this->ruleconfigs[$wikiname])) {
			return "Wikiname not found = $wikiname";
		}

		if (substr($outputdir, -1) != DIRECTORY_SEPARATOR) $outputdir .= DIRECTORY_SEPARATOR;

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

    	// Open the value output file
    	$value_filepath = "$outputdir$wikiname-tmpl-param-values.csv.bz2";
    	$this->tmpl_value_hndl = bzopen($value_filepath, 'w');

    	$this->clearParserState();
    	$this->highest_revision_id = 0;
    	$this->templates = array();

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

    	bzclose($fh);
    	bzclose($this->tmpl_value_hndl);

    	// Write the template name file
    	$template_filepath = "$outputdir$wikiname-tmpl-param-templates.csv";
    	$hndl = fopen($template_filepath, 'w');
    	$min_page_cnt = (int)Config::get(self::MIN_PAGE_CNT);

    	foreach ($this->templates as $tmplname => $template) {
    		if ($template['pagecnt'] < $min_page_cnt && ! isset($template['redirect'])) continue;
    		if (! isset($template['pageid'])) continue;

			$values = array($tmplname,
					$template['pageid'],
					$template['pagecnt'],
					$template['instancecnt'],
					isset($template['redirect']) ? $template['redirect'] : ''
			);

			$line = CSVString::format($values);
			fwrite($hndl, $line);
			fwrite($hndl, "\n");
    	}

    	fclose($hndl);

    	// Write the metadata
    	$metadata_filepath = "$outputdir$wikiname-tmpl-param-metadata.properties";
    	$metadata = new Properties($metadata_filepath);
    	$wikidata = $this->ruleconfigs[$wikiname];

    	$metadata->set('wikiname', $wikiname);
    	$metadata->set('wikititle', $wikidata['title']);
    	$metadata->set('wikidomain', $wikidata['domain']);
    	$metadata->set('templateNS', $wikidata['templateNS']);
    	$metadata->set('lang', $wikidata['lang']);
    	$metadata->set('lastdumpdate', $dumpdate);
    	$metadata->set('highest_revision_id', $this->highest_revision_id, true);

    	return '';
    }

    /**
     * Process a wiki page
     */
    function processPage()
    {
    	static $instancecnt = 0;

    	$revid = (int)$this->parserState['revision_id'];
		if ($revid > $this->highest_revision_id) $this->highest_revision_id = $revid;

		// Save template redirect target and template page id
		if ($this->parserState['namespace'] == 10) {
			$tmplname = $this->parserState['page_title'];
			if (! isset($this->templates[$tmplname])) $this->templates[$tmplname] = array('pagecnt' => 0, 'instancecnt' => 0);

			$this->templates[$tmplname]['pageid'] = $this->parserState['page_id'];

			if (! empty($this->parserState['redirect_target'])) {
				$redir = $this->parserState['redirect_target'];
				$namespace = MediaWiki::getNamespaceName($redir);
				if (strlen($namespace) > 0) $redir = substr($redir, strlen($namespace) + 1); // Strip namespace + :

				$this->templates[$tmplname]['redirect'] = $redir;
			}
		}

		// Parse the templates
		$templates = TemplateParamParser::getTemplates($this->parserState['data']);
		$pagetemplates = array();

		foreach ($templates as $template) {
			$tmplname = $template['name'];
			$params = $template['params'];

			foreach ($params as $key => $value) {
				if (empty($value)) unset($params[$key]);
			}

			if (empty($params)) continue;

			if (! isset($this->templates[$tmplname])) $this->templates[$tmplname] = array('pagecnt' => 0, 'instancecnt' => 0);
			if (! isset($pagetemplates[$tmplname])) $pagetemplates[$tmplname] = 0;
			++$pagetemplates[$tmplname];

			if ($pagetemplates[$tmplname] == 1) ++$this->templates[$tmplname]['pagecnt'];
			++$this->templates[$tmplname]['instancecnt'];

			// Write a line to the value file
			$values = array($this->parserState['page_id'], $tmplname, $pagetemplates[$tmplname]);

			foreach ($params as $key => $value) {
				$value = str_replace("\n", '<cr>', $value);
				$values[] = $key;
				$values[] = $value;
			}

			$line = CSVString::format($values);
			bzwrite($this->tmpl_value_hndl, $line);
			bzwrite($this->tmpl_value_hndl, "\n");
			++$instancecnt;
			if ($instancecnt % 100000 == 0) echo "$instancecnt\n";
		}
    }

    /**
     * Clear the xml parser state
     */
    function clearParserState()
    {
    	$this->parserState = array('container' => '', 'element' => '', 'page_id' => '', 'namespace' => '', 'data' => '',
    		'page_title' => '', 'redirect_target' => '', 'revision_id' => 0);
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

    		case 'redirect':
    			if ($this->parserState['container'] == 'page') $this->parserState['redirect_target'] = $attribs['title'];
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
     * Generate template param value sql import file
     *
     * mysql --defaults-file=~/replica.my.cnf -h enwiki.labsdb s51454__TemplateParamBot_p <enwiki-tmpl-param.sql
     *
     * @param string $tmplfilepath
     * @param string $valuefilepath
     * @param string $outputdir
     * @return string Error message
     */
    public function generateSQLImport($tmplfilepath, $valuefilepath, $outputdir)
    {
    	if (! preg_match('!(\\w+)-tmpl-param-templates.csv!', $filepath, $matches)) {
    		return 'Template file path must resemble enwiki-tmpl-param-templates.csv';
    	}
    	$wikiname = $matches[1];

		if (! isset($this->ruleconfigs[$wikiname])) {
			return "Wikiname not found = $wikiname";
		}

    	$filepath = "$outputdir$wikiname-tmpl-param-metadata.properties";
    	$metadata = new Properties($filepath);

		if (substr($outputdir, -1) != DIRECTORY_SEPARATOR) $outputdir .= DIRECTORY_SEPARATOR;

    	$max_instance_cnt = Config::get(self::MAX_INSTANCE_CNT);

    	// Load the templates
    	$templates = array();

    	$ihndl = fopen($tmplfilepath, 'r');

    	while (! feof($ihndl)) {
    		$buffer = rtrim(fgets($ihndl));
    		if (empty($buffer)) continue;

    		list($tmplname,$pageid,$pagecnt,$instancecnt,$redirect) = CSVString::parse($buffer);

    		$templates[$tmplname] = array('pageid' => $pageid, 'pagecnt' => $pagecnt, 'instancecnt' => $instancecnt,
    			'redirect' => $redirect, 'paramnames' => array());
    	}

    	fclose($ihndl);

    	$filepath = "$outputdir$wikiname-tmpl-param.sql.bz2";
    	$ohndl = bzopen($filepath, 'w');

    	$this->writeSQLHeader($ohndl, $metadata);

    	$filepath = "compress.bzip2://$outputdir$wikiname-tmpl-param-values.csv.bz2";
    	$ihndl = fopen($filepath, 'r');

    	while (! feof($ihndl)) {
    		$buffer = rtrim(fgets($ihndl));
    		if (empty($buffer)) continue;

    		$fields = CSVString::parse($buffer);
    		$pageid = $fields[0];
    		$tmplname = $fields[1];
    		$instancenum = $fields[2];
    		$params = array();
    	}

    	fclose($ihndl);

    	$this->writeSQLTrailer($ohndl, $metadata, $templates);

    	bzclose($ohndl);

    	return '';
    }

    function writeSQLHeader($hndl, Properties $metadata)
    {
    	$wikiname = $metadata->get('wikiname');

    	$sql = <<<EOT
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE IF NOT EXISTS `{$wikiname}_templates` (
    	`template_id` int unsigned NOT NULL PRIMARY KEY,
    	`page_count` int unsigned NOT NULL,
    	`instance_count` int unsigned NOT NULL,
    	`last_update` datetime NOT NULL,
    	`revision_id` int unsigned NOT NULL
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$wikiname}_values` (
    	`page_id` int unsigned NOT NULL,
    	`template_id` int unsigned NOT NULL,
    	`instance_num` int unsigned NOT NULL,
    	`param_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	`param_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	KEY `template_id` (`template_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `{$wikiname}_totals` (
    	`template_id` int unsigned NOT NULL,
    	`param_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    	`value_count` int unsigned NOT NULL,
    	`unique_value_count` int unsigned NOT NULL,
    	KEY `template_id` (`template_id`)
    	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
EOT;

    	fwrite($hndl, $sql);


    	// Add the wiki table entry if needed
    	$wikidata = $this->ruleconfigs[$wikiname];
    	$sth = $dbh_tools->prepare('INSERT INTO wikis (wikiname,wikititle,wikidomain,templateNS,lang,lastdumpdate,revision_id,templatecnt,templateinstancecnt,templatevaluecnt) VALUES (?,?,?,?,?,?,0,0,0,0)');
   		$sth->execute(array($wikiname, $wikidata['title'], $wikidata['domain'], $wikidata['templateNS'], $wikidata['lang'], $dumpdate));

    	$sth = $dbh_tools->prepare('UPDATE wikis SET revision_id = ? WHERE wikiname = ?');
    	$sth->execute(array($this->highest_revision_id, $wikiname));
    }

    function writeSQLTrailer($hndl, Properties $metadata, $templates)
    {
    	$sql = <<<EOT

/*!40000 ALTER TABLE `$wikiname` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
EOT;

    	fwrite($hndl, $sql);

    }

    public function dumpTemplateData($wikiname, $outputdir)
    {
    	$filepath = "$outputdir$wikiname-tmpl-param-templatedata.csv";
    	$hndl = fopen($filepath, 'w');

    	$dbh_tools = $this->serviceMgr->getDBConnection($wikiname);

    	$sql = "SELECT p1.page_title, GROUP_CONCAT(p2.page_title SEPARATOR '|') FROM page_props
    		STRAIGHT_JOIN page p1 ON pp_page = p1.page_id
    		LEFT JOIN redirect ON rd_namespace = p1.page_namespace AND rd_title = p1.page_title
    		STRAIGHT_JOIN page p2 ON rd_from = p2.page_id
    		WHERE pp_propname = 'templatedata' AND
    			AND p1.page_namespace = 10
    		GROUP BY p1.page_title";

    	$sth = $dbh_tools->query($sql);
    	$sth->setFetchMode(PDO::FETCH_NUM);

    	while ($row = $sth->fetch()) {
    		fwrite($hndl, "{$row[0]}\n");
    	}

    	fclose($hndl);
    }
}