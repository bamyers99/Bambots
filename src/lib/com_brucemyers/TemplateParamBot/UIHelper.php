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

		$wikis = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'lang' => 'en'),
			'commonswiki' => array('title' => 'Wikipedia Commons', 'domain' => 'commons.wikimedia.org', 'lang' => 'en')); // Want first

		while ($row = $sth->fetch()) {
			$wikiname = $row['wikiname'];

			$wikis[$wikiname] = array('title' => $row['wikititle'], 'domain' => $row['wikidomain'], 'lang' => $row['lang'],
			    'lastdumpdate' => $row['lastdumpdate'], 'templateNS' => $row['templateNS']);
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
	    $wikis = $this->getWikis();
	    $info = [];
		$errors = [];
		$wikiname = $params['wiki'];

		$sth = $this->dbh_tools->prepare("SELECT * FROM `{$wikiname}_templates` WHERE `name` = ?");
		$sth->execute(array($params['template']));

		if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$info['page_count'] = $row['page_count'];
			$info['instance_count'] = $row['instance_count'];
			$info['file_offset'] = $row['file_offset'];
			$info['loaded'] = $row['loaded'];
			$info['tmplid'] = $row['id'];
			$info['name'] = $row['name'];
			$info['params'] = array();

			$sql = "SELECT * FROM `{$wikiname}_totals` WHERE template_id = {$info['tmplid']} ORDER BY param_name";
			$sth = $this->dbh_tools->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);

			while ($row = $sth->fetch()) {
				$info['params'][$row['param_name']] = $row;
			}

			// Fetch the TemplateData
			$domain = $wikis[$wikiname]['domain'];
			$templateprefix = $wikis[$wikiname]['templateNS'];
			$lctemplate = lcfirst($params['template']);

			$mediawiki = $this->serviceMgr->getMediaWiki($domain, false);

			$query = '?action=templatedata&format=php&titles=' . urlencode("$templateprefix:" . $lctemplate . '|' .
			    "$templateprefix:" . $params['template']);

			$ret = $mediawiki->query($query);

			if (isset($ret['error'])) {
			    Logger::log('TemplateParamBot->UIHelper->getTemplate Error ' . $ret['error']['info']);
			    $ret = [];
			}

			if (! empty($ret) && ! empty($ret['pages'])) {
			    $info['TemplateData'] = new TemplateData(null, reset($ret['pages']));
			    $templname = str_replace("$templateprefix:", '', $info['TemplateData']->getTitle());
			    $info['name'] = $templname; // reset incase lowercase name
			} else {
				$l10n = new L10N($wikis[$wikiname]['lang']);
				$errors[] = htmlentities($l10n->get('templatedatanotfound', true), ENT_COMPAT, 'UTF-8');
			}
		} else {
			$l10n = new L10N($wikis[$wikiname]['lang']);
			$errors[] = htmlentities($l10n->get('templatenotfound', true), ENT_COMPAT, 'UTF-8');
		}

		return ['errors' => $errors, 'info' => $info];
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
	    $wikis = $this->getWikis();
	    $results = [];
		$errors = [];
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
			$where = 'template_id = ? AND param_name = ?';
			$values = array($templid, $params['param']);
		} else { // valuelinks
			$where = 'template_id = ? AND param_name = ? AND param_value = ?';
			$values = array($templid, $params['param'], $params['value']);
		}

		$sql = "SELECT DISTINCT page_id FROM `{$wikiname}_values` WHERE $where LIMIT $offset,$max_rows";
		$sth = $this->dbh_tools->prepare($sql);
		$sth->execute($values);

		$results = $sth->fetchAll(PDO::FETCH_NUM);

		if (! empty($results)) {
			$page_ids = array();
			foreach ($results as $row) {
				$page_ids[] = $row[0];
			}

			$domain = $wikis[$wikiname]['domain'];
			$mediawiki = $this->serviceMgr->getMediaWiki($domain, false);

			$ret = $mediawiki->getPagesByID($page_ids, false);

			$results = [];

			foreach ($ret as $page_title => $result) {
			    $results[] = ['page_title' => str_replace(' ', '_', $page_title)];
			}

			unset($result);
		}

		return ['errors' => $errors, 'results' => $results];
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
	    $wikis = $this->getWikis();
	    $results = [];
		$errors = [];
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

		$sql = "SELECT DISTINCT page_id FROM `{$wikiname}_$tablename` " .
			" WHERE template_id = ? AND param_name = ? " .
			" LIMIT $offset,$max_rows";

		$sth = $this->dbh_tools->prepare($sql);
		$sth->execute(array($templid, $params['param']));

		$results = $sth->fetchAll(PDO::FETCH_NUM);

		if (! empty($results)) {
			$page_ids = [];
			foreach ($results as $row) {
				$page_ids[] = $row[0];
			}

			$domain = $wikis[$wikiname]['domain'];
			$mediawiki = $this->serviceMgr->getMediaWiki($domain, false);

			$ret = $mediawiki->getPagesByID($page_ids, false);

			$results = [];

			foreach ($ret as $page_title => $result) {
			    $results[] = ['page_title' => str_replace(' ', '_', $page_title)];
			}

			unset($result);
		}

		return ['errors' => $errors, 'results' => $results];
	}

	/**
	 * Get invalid parameter pages
	 *
	 * @param array $invalid_params
	 * @param array $params
	 * @param int $max_rows
	 * @return array Results, keys = errors - array(), results - array()
	 */
	public function getInvalids($invalid_params, $params, $max_rows)
	{
	    $wikis = $this->getWikis();
	    $results = [];
		$errors = [];
		$wikiname = $params['wiki'];

		$sth = $this->dbh_tools->prepare("SELECT id FROM `{$wikiname}_templates` WHERE `name` = ?");
		$sth->execute(array($params['template']));

		$row = $sth->fetch(PDO::FETCH_NUM);
		$templid = $row[0];

		$questions = array_fill(0, count($invalid_params), '?');
		$questions = implode(',', $questions);

		$where = "template_id = ? AND param_name IN ($questions)";
		$values = array($templid);

		foreach ($invalid_params as $param) {
			$values[] = $param;
		}

		$sql = "SELECT page_id, instance_num, param_name, param_value FROM `{$wikiname}_values` WHERE $where ORDER BY page_id LIMIT $max_rows";
		$sth = $this->dbh_tools->prepare($sql);
		$sth->execute($values);

		$results = $sth->fetchAll(PDO::FETCH_ASSOC);

		if (! empty($results)) {
			$page_ids = [];
			foreach ($results as $row) {
				$page_ids[$row['page_id']] = true; // removes dups
			}

			$domain = $wikis[$wikiname]['domain'];
			$mediawiki = $this->serviceMgr->getMediaWiki($domain, false);

			$ret = $mediawiki->getPagesByID(array_keys($page_ids), false);

			$page_names = [];

			foreach ($ret as $page_title => $result) {
				$page_names[$result['pageid']] = $page_title;
			}

			foreach ($results as &$result) {
				$result['page_title'] = str_replace(' ', '_', $page_names[$result['page_id']]);
			}

			unset($result);
		}

		return ['errors' => $errors, 'results' => $results];
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