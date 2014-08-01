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
use com_brucemyers\Util\MySQLDate;
use com_brucemyers\Util\FileCache;
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
	 * @return array wikiname => array('title', 'domain')
	 */
	public function getWikis()
	{
		$sql = 'SELECT * FROM wikis ORDER BY wikititle';
		$sth = $this->dbh_tools->query($sql);

		$wikis = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org'),
			'commonswiki' => array('title' => 'Wikipedia Commons', 'domain' => 'commons.wikimedia.org')); // Want first

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$wikiname = $row['wikiname'];
			if (! isset($wikis[$wikiname])) $wikis[$wikiname] = array('title' => $row['wikititle'], 'domain' => $row['wikidomain']);
		}

		return $wikis;
	}

	/**
	 * Fetch a saved queries paramaters
	 *
	 * @param string $queryid Query hash
	 * @return array Parameters, empty = not found
	 */
	public function fetchParams($queryid)
	{
		$sth = $this->dbh_tools->prepare('SELECT params FROM querys WHERE hash = ?');
		$sth->bindParam(1, $queryid);
		$sth->execute();

		if ($row = $sth->fetch(PDO::FETCH_ASSOC)) return unserialize($row['params']);
		else return array();
	}

	/**
	 * Save a query
	 *
	 * @param array $params
	 */
	public function saveQuery(&$params)
	{
		$serialized = serialize($params);
		$hash = md5($serialized);
		$accessdate = MySQLDate::toMySQLDate(time());
		$wikiname = $params['wiki'];

		// See if we have a query record
    	$sth = $this->dbh_tools->prepare("SELECT id FROM querys WHERE hash = ?");
    	$sth->bindParam(1, $hash);
    	$sth->execute();

    	if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    		$queryid = $row['id'];
    		$sth = $this->dbh_tools->prepare("UPDATE querys SET lastaccess = ? WHERE id = $queryid");
    		$sth->bindParam(1, $accessdate);
    		$sth->execute();
    	} else {
    		$sth = $this->dbh_tools->prepare("INSERT INTO querys (wikiname,hash,params,lastaccess,lastrecalc) VALUES (?,?,?,?,?)");
    		$sth->bindParam(1, $wikiname);
    		$sth->bindParam(2, $hash);
    		$sth->bindParam(3, $serialized);
    		$sth->bindParam(4, $accessdate);
    		$sth->bindParam(5, $accessdate);
    		$sth->execute();
		}
	}

	/**
	 * Get watch list results
	 *
	 * @param array $params
	 * @return array Results, keys = errors - array(), results - array(), catcount - int
	 * @see WatchResults
	 */
	public function getResults(&$params)
	{
		$errors = array();
		$serialized = serialize($params);
		$hash = md5($serialized);
		$accessdate = MySQLDate::toMySQLDate(time());
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
			return array('errors' => array('Query not found'), 'results' => array());
		}

		$wiki_host = $this->wiki_host;
		if (empty($wiki_host)) $wiki_host = "$wikiname.labsdb";
		$dbh_wiki = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p", $this->user, $this->pass);
		$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$results = array();

		switch ($catcount) {
		    case QueryCats::CATEGORY_COUNT_UNKNOWN:
		    case QueryCats::CATEGORY_COUNT_RECALC:
		    	$errors[] = 'Results will be ready within 24 hours';
		    	break;

		    case 0:
				$errors[] = 'No categories found';
				break;

		    case QueryCats::CATEGORY_COUNT_UNAPPROVED:
				$errors[] = 'Watchlist waiting for approval';
				break;

		    case QueryCats::CATEGORY_COUNT_DENIED:
				$errors[] = 'Watchlist denied - too many categories';
				break;

    		default:
    			$watchResults = new WatchResults($dbh_wiki, $this->dbh_tools);
    			$results = $watchResults->getResults($queryid, $params);
    			break;
		}

		return array('errors' => $errors, 'results' => $results, 'catcount' => $catcount);
	}

	/**
	 * Generate an atom feed
	 *
	 * @param string $query Query id
	 * @return boolean true - success, false - failure
	 */
	public function generateAtom($query)
	{
		header('Content-Type: application/atom+xml');

		// Check the cache
		$feed = FileCache::getData(CategoryWatchlistBot::CACHE_PREFIX_ATOM . $query);
		if (! empty($feed)) {
			echo $feed;
			return true;
		}

		$params = $this->fetchParams($query);
		$results = $this->getResults($params);

		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = "CategoryWatchlist.php?action=atom&amp;query=$query";
		$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

		$updated = gmdate("Y-m-d\TH:i:s\Z");
		$catname2 = '';
		if (! empty($params['cn2'])) $catname2 = ', ...';

		$feed = "<?xml version=\"1.0\"?>\n<feed xmlns=\"http://www.w3.org/2005/Atom\" xml:lang=\"en\">\n";
		$feed .= "<id>//$host$uri/$extra</id>\n";
		$feed .= "<title>Category Watchlist : {$params['cn1']}$catname2</title>\n";
		$feed .= "<link rel=\"self\" type=\"application/atom+xml\" href=\"$protocol://$host$uri/$extra\" />\n";
		$feed .= "<link rel=\"alternate\" type=\"text/html\" href=\"$protocol://$host$uri/CategoryWatchlist.php?query=$query\" />\n";
		$feed .= "<updated>$updated</updated>\n";

		$dategroups = array();
		foreach ($results['results'] as &$result) {
			$date = $result['diffdate'];
			unset($result['diffdate']);
			if (! isset($dategroups[$date])) $dategroups[$date] = array();
			$dategroups[$date][] = $result;
		}
		unset($result);

		foreach ($dategroups as $date => &$dategroup) {
			$date = MySQLDate::toPHP($date);
			$humandate = date('F j, Y', $date);
			$updated = gmdate("Y-m-d\TH:i:s\Z", $date);

			$feed .= "<entry>\n";
			$feed .= "<id>//$host$uri/$extra&amp;date=$humandate</id>\n";
			$feed .= "<title>Results For $humandate</title>\n";
			$feed .= "<link rel=\"alternate\" type=\"text/html\" href=\"$protocol://$host$uri/CategoryWatchlist.php?query=$query\" />\n";
			$feed .= "<updated>$updated</updated>\n";
			$feed .= "<summary type=\"html\">" . count($dategroup) . " category additions</summary>\n";
			$feed .= "<author><name>CategoryWatchlistBot</name></author>\n";
			$feed .= "</entry>\n";
		}
		unset($dategroup);

		$feed .= '</feed>';

		FileCache::putData(CategoryWatchlistBot::CACHE_PREFIX_ATOM . $query, $feed);

		echo $feed;

		return true;
	}

	/**
	 * Check an admin password
	 *
	 * @param string $pass Password
	 * @return boolean Is password ok
	 */
	public function checkPassword($pass)
	{
		$curpass = Config::get('wiki.password');
		return ($pass == $curpass);
	}

	/**
	 * Get unapproved queries
	 *
	 * @return array Unapproved queries, keys = id, hash, wikiname
	 */
	public function getUnapproveds()
	{
		$sth = $this->dbh_tools->query('SELECT id, hash, wikiname FROM querys WHERE catcount = ' . QueryCats::CATEGORY_COUNT_UNAPPROVED);
		$sth->setFetchMode(PDO::FETCH_ASSOC);

		$results = array();

		while ($row = $sth->fetch()) {
			$results[] = $row;
		}

		return $results;
	}

	/**
	 * Set a queries status
	 *
	 * @param unknown $hash
	 * @param unknown $status
	 */
	public function setQueryStatus($hash, $status)
	{
		$sth = $this->dbh_tools->prepare('UPDATE querys SET catcount = ? WHERE hash = ?');
		$sth->bindParam(1, $status);
		$sth->bindParam(2, $hash);
		$sth->execute();
	}
}