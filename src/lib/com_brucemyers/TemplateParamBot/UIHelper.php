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

use com_brucemyers\Util\L10N;
use com_brucemyers\Util\Config;
use PDO;

class UIHelper
{
	protected $serviceMgr;
	protected $dbh_tools;

	public function __construct()
	{
		$this->serviceMgr = new ServiceManager();
		$this->dbh_tools = $this->serviceMgr->getDBConnection('tools');
	}

	/**
	 * Get a list of wikis.
	 *
	 * @return array wikiname => array('title', 'domain')
	 */
	public function getWikis()
	{
		$sql = 'SELECT * FROM wikis ORDER BY wikititle';
		$sth = $this->dbh_tools->query($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);

		$wikis = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'lang' => 'en'));
//			'commonswiki' => array('title' => 'Wikipedia Commons', 'domain' => 'commons.wikimedia.org', 'lang' => 'en')); // Want first

		while ($row = $sth->fetch()) {
			$wikiname = $row['wikiname'];

			$wikis[$wikiname] = array('title' => $row['wikititle'], 'domain' => $row['wikidomain'], 'lang' => $row['lang'],
				'lastdumpdate' => $row['lastdumpdate']);
		}

		return $wikis;
	}

	/**
	 * Get all templates
	 *
	 * @param array $params
	 * @param int $max_rows
	 * @return array Results, keys = errors - array(), results - array()
	 */
	public function getAllTemplates($params, $max_rows)
	{
		$results = array();
		$errors = array();
		$wikiname = $params['wiki'];

		$page = $params['page'];
		$page = $page - 1;
		if ($page < 0 || $page > 1000) $page = 0;
		$offset = $page * $max_rows;

		$sql = "SELECT * FROM `{$wikiname}_templates` ORDER BY instance_count DESC LIMIT $offset,$max_rows";
		$sth = $this->dbh_tools->query($sql);

		$results = $sth->fetchAll(PDO::FETCH_ASSOC);

		return array('errors' => $errors, 'results' => $results);
	}

	/**
	 * Get template data
	 *
	 * @param array $params
	 * @return array Results, keys = errors - array(), info - array('page_count', 'instance_count', 'TemplateData', 'params' => array())
	 */
	public function getTemplate($params)
	{
		$info = array();
		$errors = array();
		$wikiname = $params['wiki'];

		$sth = $this->dbh_tools->prepare("SELECT * FROM `{$wikiname}_templates` WHERE `name` = ?");
		$sth->execute(array($params['template']));

		if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$info['page_count'] = $row['page_count'];
			$info['instance_count'] = $row['instance_count'];
			$info['file_offset'] = $row['file_offset'];
			$info['loaded'] = $row['loaded'];
			$info['tmplid'] = $row['id'];
			$info['params'] = array();

			$sql = "SELECT * FROM `{$wikiname}_totals` WHERE template_id = {$info['tmplid']} ORDER BY param_name";
			$sth = $this->dbh_tools->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);

			while ($row = $sth->fetch()) {
				$info['params'][$row['param_name']] = $row;
			}

			// Fetch the TemplateData
			$dbh_wiki = $this->serviceMgr->getDBConnection($wikiname);
			$sql = "SELECT pp_value FROM page_props WHERE pp_page = {$info['tmplid']} AND pp_propname = 'templatedata'";
			$sth = $dbh_wiki->query($sql);
			if ($row = $sth->fetch(PDO::FETCH_NUM)) {
				$info['TemplateData'] = new TemplateData($row[0]);
			} else {
				$wikis = $this->getWikis();
				$l10n = new L10N($wikis[$wikiname]['lang']);
				$errors[] = htmlentities($l10n->get('templatedatanotfound', true), ENT_COMPAT, 'UTF-8');
			}
		} else {
			$wikis = $this->getWikis();
			$l10n = new L10N($wikis[$wikiname]['lang']);
			$errors[] = htmlentities($l10n->get('templatenotfound', true), ENT_COMPAT, 'UTF-8');
		}

		return array('errors' => $errors, 'info' => $info);
	}

	/**
	 * Check parameter value load status
	 *
	 * @param array $params
	 * @param array $info Template info
	 * @return array status => S,R,E,C, progress => progress message
	 */
	public function checkLoadStatus($params, $info)
	{
		if ($info['loaded'] == 'Y') return array('status' => 'C', 'progress' => '');

		$wikiname = $params['wiki'];

		$sth = $this->dbh_tools->prepare('SELECT status, progress FROM loads WHERE wikiname = ? AND template_id = ?');
		$sth->execute(array($wikiname, $info['tmplid']));

		if ($row = $sth->fetch(PDO::FETCH_NUM)) {
			$progress = $row[1];
			$status = $row[0];
			if ($row[0] == 'S') {
				$sth = $this->dbh_tools->prepare("SELECT progress FROM loads WHERE status = 'R'");
				$sth->execute();
				if ($row = $sth->fetch(PDO::FETCH_NUM)) {
					$progress .= " ; waiting for {$row[0]}";
				}
			}
			return array('status' => $status, 'progress' => $progress);
		}

		$sth = $this->dbh_tools->prepare("INSERT INTO loads VALUES (?,?,'S','Loading data','2000-01-01','00:00:00')");
		$sth->execute(array($wikiname, $info['tmplid']));

		// Tickle the loader
		$command = Config::get(TemplateParamBot::LOAD_COMMAND);
		exec($command);

		return array('status' => 'S', 'progress' => 'Loading data');
	}

	/**
	 * Get parameter results
	 *
	 * @param string $type
	 * @param array $params
	 * @param int $max_rows
	 * @return array Results, keys = errors - array(), results - array()
	 */
	public function getPages($type, $params, $max_rows)
	{
		$results = array();
		$errors = array();
		$wikiname = $params['wiki'];

		$page = $params['page'];
		$page = $page - 1;
		if ($page < 0 || $page > 1000) $page = 0;
		$offset = $page * $max_rows;

		$sth = $this->dbh_tools->prepare("SELECT id FROM `{$wikiname}_templates` WHERE `name` = ?");
		$sth->execute(array($params['template']));

		$row = $sth->fetch(PDO::FETCH_NUM);
		$templid = $row[0];

		if ($type == 'paramlinks') {
			$where = 'pv.template_id = ? AND wp.page_id = pv.page_id AND pv.param_name = ?';
			$values = array($templid, $params['param']);
		} else { // valuelinks
			$where = 'pv.template_id = ? AND wp.page_id = pv.page_id AND pv.param_name = ? AND pv.param_value = ?';
			$values = array($templid, $params['param'], $params['value']);
		}

		$sql = "SELECT DISTINCT page_title FROM `{$wikiname}_p`.page wp, `{$wikiname}_values` pv WHERE $where ORDER BY page_title LIMIT $offset,$max_rows";
		$sth = $this->dbh_tools->prepare($sql);
		$sth->execute($values);

		$results = $sth->fetchAll(PDO::FETCH_ASSOC);

		return array('errors' => $errors, 'results' => $results);
	}

	/**
	 * Get parameter missing results
	 *
	 * @param array $params
	 * @param int $max_rows
	 * @param string $type - missing or errors
	 * @return array Results, keys = errors - array(), results - array()
	 */
	public function getMissing($params, $max_rows, $type)
	{
		$results = array();
		$errors = array();
		$wikiname = $params['wiki'];

		$page = $params['page'];
		$page = $page - 1;
		if ($page < 0 || $page > 1000) $page = 0;
		$offset = $page * $max_rows;

		$sth = $this->dbh_tools->prepare("SELECT id FROM `{$wikiname}_templates` WHERE `name` = ?");
		$sth->execute(array($params['template']));

		$row = $sth->fetch(PDO::FETCH_NUM);
		$templid = $row[0];

		if ($type == 'missing') $tablename = 'missings';
		else $tablename = 'invalids';

		$sql = "SELECT page_title FROM `{$wikiname}_$tablename` miss " .
			" STRAIGHT_JOIN `{$wikiname}_p`.page wp ON wp.page_id = miss.page_id " .
			" WHERE miss.template_id = ? AND miss.param_name = ? " .
			" ORDER BY page_title LIMIT $offset,$max_rows";

		$sth = $this->dbh_tools->prepare($sql);
		$sth->execute(array($templid, $params['param']));

		$results = $sth->fetchAll(PDO::FETCH_ASSOC);

		return array('errors' => $errors, 'results' => $results);
	}

	/**
	 * Get the TemplateParamConfig
	 *
	 * @return TemplateParamConfig
	 */
	public function getTemplateParamConfig()
	{
		return new TemplateParamConfig($this->serviceMgr);
	}
}