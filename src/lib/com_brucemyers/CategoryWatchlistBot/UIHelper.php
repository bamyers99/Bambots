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

use com_brucemyers\Util\Config;
use PDO;

class UIHelper
{
	public $max_watch_days;
	protected $dbh_tools;
	protected $user;
	protected $pass;
	protected $wiki_host;

	public function __construct()
	{
		$tools_host = Config::get(CategoryWatchlistBot::TOOLS_HOST);
		$this->user = Config::get(CategoryWatchlistBot::LABSDB_USERNAME);
		$this->pass = Config::get(CategoryWatchlistBot::LABSDB_PASSWORD);
		$this->wiki_host = Config::get(CategoryWatchlistBot::WIKI_HOST);

		$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CategoryWatchlistBot", $this->user, $this->pass);
		$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->dbh_tools = $dbh_tools;

		$this->max_watch_days = Config::get(CategoryWatchlistBot::MAX_WATCH_DAYS);
	}

	/**
	 * Get a list of wikis.
	 *
	 * @return array key=wikiname, value=wikititle
	 */
	public function getWikis()
	{
		$sql = 'SELECT * FROM wikis ORDER BY wikititle';
		$sth = $this->dbh_tools->query($sql);

		$wikis = array('enwiki' => 'English Wikipedia', 'commons' => 'Wikipedia Commons'); // Want first

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$wikiname = $row['wikiname'];
			if (! isset($wikis[$wikiname])) $wikis[$wikiname] = $row['wikititle'];
		}

		return $wikis;
	}

	/**
	 * Fetch a saved queries paramaters
	 * @param string $queryid Query hash
	 * @return array Parameters, empty = not found
	 */
	public function fetchParams($queryid)
	{
		$params = array();

		return $params;
	}

	/**
	 * Get watch list results
	 *
	 * @param unknown $params
	 * @return array Results, keys = errors - array(), results - array()
	 */
	public function getResults(&$params)
	{
		$errors = array();
		$serialized = serialize($params);
		$hash = md5($serialized);
		$accessdate = getdate();
		$accessdate = sprintf('%d-%02d-%02d', $accessdate['year'], $accessdate['mon'], $accessdate['mday']);
		$wikiname = $params['wiki'];

		// See if we have a query record
    	$sth = $this->dbh_tools->prepare("SELECT id, catcount FROM querys WHERE hash = ?");
    	$sth->bindParam(1, $hash);
    	$sth->execute();

    	if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    		$queryid = $row['id'];
    		$catcount = (int)$row['catcount'];
    		$sth = $this->dbh_tools->prepare("UPDATE querys SET lastaccess = ? WHERE id = $queryid");
    		$sth->bindParam(1, $accessdate);
    		$sth->execute();
    	} else {
    		$sth = $this->dbh_tools->prepare("INSERT INTO querys (wikiname,hash,params,lastaccess) VALUES (?,?,?,?)");
    		$sth->bindParam(1, $wikiname);
    		$sth->bindParam(2, $hash);
    		$sth->bindParam(3, $serialized);
    		$sth->bindParam(4, $accessdate);
    		$sth->execute();
			$queryid = $this->dbh_tools->lastInsertId();
			$catcount = -1;
		}

		$wiki_host = $this->wiki_host;
		if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";
		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p", $this->user, $this->pass);
		$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		if ($catcount < 0) {
			$querycats = new QueryCats($dbh_wiki, $this->dbh_tools);
			$results = $querycats->calcCats($queryid, $params);
			if (! empty($results['errors'])) $errors = array_merge($errors, $results['errors']);
			$catcount = (int)$results['catcount'];
			$sth = $this->dbh_tools->prepare("UPDATE querys SET catcount = $catcount WHERE id = $queryid");
    		$sth->execute();
		}

		if (! $catcount) {
			$errors[] = 'No categories found';
			$results = array();
		}
		else {

		}

		return array('errors' => $errors, 'results' => $results);
	}
}