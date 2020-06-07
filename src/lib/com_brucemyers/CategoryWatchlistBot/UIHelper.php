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
use com_brucemyers\Util\Convert;
use com_brucemyers\Util\MySQLDate;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\HttpUtil;
use com_brucemyers\Util\L10N;
use PDO;

class UIHelper
{
	public $max_watch_days;
	protected $serviceMgr;
	protected $dbh_tools;

	public function __construct()
	{
		$this->serviceMgr = new ServiceManager();
		$this->dbh_tools = $this->serviceMgr->getDBConnection('tools');

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

		$wikis = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'lang' => 'en'),
			'commonswiki' => array('title' => 'Wikipedia Commons', 'domain' => 'commons.wikimedia.org', 'lang' => 'en')); // Want first

		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$wikiname = $row['wikiname'];
			if (! isset($wikis[$wikiname])) {
				$wikis[$wikiname] = array('title' => $row['wikititle'], 'domain' => $row['wikidomain'], 'lang' => $row['lang']);
			}
		}

		return $wikis;
	}

	/**
	 * Fetch a saved queries paramaters
	 *
	 * @param string $queryid Query hash
	 * @param string $type param type - empty or dump
	 * @return array Parameters, empty = not found
	 */
	public function fetchParams($queryid, $type = '')
	{
	    $sth = $this->dbh_tools->prepare("SELECT params FROM {$type}querys WHERE hash = ?");
	    $sth->bindParam(1, $queryid);
	    $sth->execute();

	    if ($row = $sth->fetch(PDO::FETCH_ASSOC)) return unserialize($row['params']);
	    else return [];
	}

	/**
	 * Save a query
	 *
	 * @param array $params
	 */
	public function saveQuery($params)
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
	        $wikilang = substr($wikiname, 0, -4);
	        $domain = "$wikilang.wikipedia.org";
	        $mediawiki = $this->serviceMgr->getMediaWiki($domain);

	        $querycats = new QueryCats($mediawiki, $this->dbh_tools);
	        $cats = $querycats->calcCats($params, true);
	        $catcount = $cats['catcount'];

	        $sth = $this->dbh_tools->prepare("INSERT INTO querys (wikiname,hash,params,lastaccess,lastrecalc,catcount) VALUES (?,?,?,?,?,?)");
	        $sth->bindParam(1, $wikiname);
	        $sth->bindParam(2, $hash);
	        $sth->bindParam(3, $serialized);
	        $sth->bindParam(4, $accessdate);
	        $sth->bindParam(5, $accessdate);
	        $sth->bindParam(6, $catcount);
	        $sth->execute();

	        $id = $this->dbh_tools->lastInsertId();
	        $querycats->saveCats($id, $cats['cats']);
	    }
	}

	/**
	 * Save a dump query
	 *
	 * @param array $params
	 */
	public function saveDumpQuery($params)
	{
		$serialized = serialize($params);
		$hash = md5($serialized);
		$accessdate = MySQLDate::toMySQLDate(time());

		// See if we have a query record
    	$sth = $this->dbh_tools->prepare("SELECT id FROM dumpquerys WHERE hash = ?");
    	$sth->bindParam(1, $hash);
    	$sth->execute();

    	if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    		$queryid = $row['id'];
    		$sth = $this->dbh_tools->prepare("UPDATE dumpquerys SET lastaccess = ? WHERE id = $queryid");
    		$sth->bindParam(1, $accessdate);
    		$sth->execute();
    	} else {
    		$sth = $this->dbh_tools->prepare("INSERT INTO dumpquerys (hash,params,lastaccess) VALUES (?,?,?)");
    		$sth->bindParam(1, $hash);
    		$sth->bindParam(2, $serialized);
    		$sth->bindParam(3, $accessdate);
    		$sth->execute();
    	}

    	$sth = $this->dbh_tools->prepare("INSERT IGNORE INTO dumpfiles (wikiname,filename) VALUES (?,?)");

    	for ($x=1; $x <= 10; ++$x) {
    	    if (empty($params["wn$x"]) || empty($params["fn$x"])) break;
    	    $sth->bindParam(1, $params["wn$x"]);
    	    $sth->bindParam(2, $params["fn$x"]);
    	    $sth->execute();
    	}
	}

/**
	 * Get watch list results
	 *
	 * @param array $params
	 * @param int $page
	 * @param int $max_rows
	 * @return array Results, keys = errors - array(), results - array()
	 * @see WatchResults
	 */
	public function getResults($params, $page, $max_rows)
	{
		$errors = [];
		$serialized = serialize($params);
		$hash = md5($serialized);
		$accessdate = MySQLDate::toMySQLDate(time());

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
			return ['errors' => ['Query not found'], 'results' => []];
		}

    	$watchResults = new WatchResults($this->dbh_tools);
    	$results = $watchResults->getResults($queryid, $params, $page, $max_rows);

		return ['errors' => $errors, 'results' => $results];
	}

	/**
	 * Get dump watchlist results
	 *
	 * @param array $params
	 * @return array Results, keys = errors -[], results - []
	 */
	public function getDumpResults($params)
	{
	    $errors = [];
	    $serialized = serialize($params);
	    $hash = md5($serialized);
	    $accessdate = MySQLDate::toMySQLDate(time());

	    // See if we have a query record
	    $sth = $this->dbh_tools->prepare("SELECT id FROM dumpquerys WHERE hash = ?");
	    $sth->bindParam(1, $hash);
	    $sth->execute();

	    if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	        $queryid = $row['id'];
	        $sth = $this->dbh_tools->prepare("UPDATE dumpquerys SET lastaccess = ? WHERE id = $queryid");
	        $sth->bindParam(1, $accessdate);
	        $sth->execute();
	    } else {
	        return ['errors' => ['Query not found'], 'results' => []];
	    }

	    $cachekey = WMDumpWatchlist::CACHE_PREFIX_RESULT . $queryid;

	    // Check the cache
	    $results = FileCache::getData($cachekey);
	    if (! empty($results)) {
	        $results = unserialize($results);
	        return ['errors' => $errors, 'results' => $results];
	    }

	    $results = [];

	    $sth = $this->dbh_tools->prepare("SELECT * FROM dumpfiles WHERE wikiname = ? AND filename = ?");

	    for ($x=1; $x <= 10; ++$x) {
	        if (empty($params["wn$x"]) || empty($params["fn$x"])) break;
	        $sth->bindParam(1, $params["wn$x"]);
	        $sth->bindParam(2, $params["fn$x"]);
	        $sth->execute();

	        if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	            $results["{$row['wikiname']}\t{$row['filename']}"] = $row;
	        }
	    }

	    if (count($results)) {
    	    $serialized = serialize($results);

    	    FileCache::putData($cachekey, $serialized);
	    }

	    return ['errors' => $errors, 'results' => $results];
	}

	/**
	 * Get recent results.
	 *
	 * @param string $wikiname
	 * @param int $page
	 * @param int $max_rows
	 * @return array
	 */
	public function getRecent($wikiname, $page, $max_rows)
	{
		$queryid = 0;
		$page = (int)$page - 1;
		if ($page < 0 || $page > 1000) $page = 0;
		$offset = $page * $max_rows;

		$cachekey = CategoryWatchlistBot::CACHE_PREFIX_RESULT . $queryid . '_'. $wikiname . '_' . $page;

		// Check the cache
		$results = FileCache::getData($cachekey);
		if (! empty($results)) {
			$results = unserialize($results);
			return $results;
		}

		// Get the updated pages
		$sth = $this->dbh_tools->prepare("SELECT * FROM `{$wikiname}_diffs` " .
		" ORDER BY id DESC " .
		" LIMIT $offset,$max_rows");
		$sth->execute();
		$sth->setFetchMode(PDO::FETCH_ASSOC);

		$results = array();

		while ($row = $sth->fetch()) {
			$results[] = $row;
		}

		$sth->closeCursor();

		if (! count($results)) return $results;

		$serialized = serialize($results);

		FileCache::putData($cachekey, $serialized);

		return $results;
	}
	/**
	 * Get query subcategories
	 *
	 * @param string $query Query hash
	 * @return array Subcategories
	 */
	public function getSubcats($query)
	{
		// See if we have a query record
    	$sth = $this->dbh_tools->prepare("SELECT id FROM querys WHERE hash = ?");
    	$sth->bindParam(1, $query);
    	$sth->execute();

    	if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
    		$queryid = $row['id'];
		} else {
			return [];
		}

    	$sth = $this->dbh_tools->prepare("SELECT category FROM querycats WHERE queryid = $queryid ORDER BY category");
    	$sth->execute();
		$sth->setFetchMode(PDO::FETCH_NUM);

		$results = [];

		while ($row = $sth->fetch()) {
			$results[] = $row[0];
		}

		$sth->closeCursor();

		return $results;
	}

	/**
	 * Get statistics.
	 * Total events
	 * Top 10 of each event type
	 *
	 * @param string $wiki Wiki
	 * @return array Statistics, keys = totalEvents => (int), C+, C-, T+, T- => array('category', 'catcount')
	 */
	public function getStatistics($wiki)
	{
		$cachekey = CategoryWatchlistBot::CACHE_PREFIX_RESULT . 'stats_'. $wiki;

		// Check the cache
		$results = FileCache::getData($cachekey);
		if (! empty($results)) {
			$results = unserialize($results);
			return $results;
		}

		$stats = [];

		// totalEvents
		$sql = "SELECT COUNT(*) FROM {$wiki}_diffs";
		$sth = $this->dbh_tools->prepare($sql);
		$sth->execute();
		$row = $sth->fetch(PDO::FETCH_NUM);
		$sth->closeCursor();
		$stats['totalEvents'] = $row[0];

		$top10s = array('C+', 'C-', 'T+', 'T-');
		foreach ($top10s as $top10) {
			$CT = $top10[0];
			$PM = $top10[1];

			$sql = "SELECT category, count(*) AS catcount FROM {$wiki}_diffs " .
				" WHERE cat_template = '$CT' AND plusminus = '$PM' " .
				" GROUP BY category " .
				" ORDER BY catcount DESC " .
				" LIMIT 10";

			$sth = $this->dbh_tools->prepare($sql);
			$sth->execute();

			$stats[$top10] = $sth->fetchAll(PDO::FETCH_ASSOC);
		}

		$serialized = serialize($stats);

		FileCache::putData($cachekey, $serialized);

		return $stats;
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
	    $results = $this->getResults($params, 1, 100);

	    $host  = $_SERVER['HTTP_HOST'];
	    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	    $extra = "CategoryWatchlist.php?action=atom&amp;query=$query";
	    $protocol = HttpUtil::getProtocol();
	    $wikis = $this->getWikis();
	    $domain = $wikis[$params['wiki']]['domain'];
	    $wikiprefix = "$protocol://$domain/wiki/";
	    $l10n = new L10N($wikis[$params['wiki']]['lang']);

	    $updated = gmdate("Y-m-d\TH:i:s\Z");

	    if (empty($params['title'])) {
	        $title = $params['cn1'];
	        if (! empty($params['cn2'])) $title = ', ...';
	    } else {
	        $title = $params['title'];
	    }

	    $title = htmlentities($title, ENT_QUOTES, 'UTF-8');
	    $title2 = htmlentities(htmlentities($l10n->get('watchlisttitle'), ENT_COMPAT, 'UTF-8') . " : $title", ENT_COMPAT, 'UTF-8');

	    $feed = "<?xml version=\"1.0\"?>\n<feed xmlns=\"http://www.w3.org/2005/Atom\" xml:lang=\"en\">\n";
	    $feed .= "<id>$protocol://$host$uri/$extra</id>\n";
	    $feed .= "<title type=\"html\">$title2</title>\n";
	    $feed .= "<link rel=\"self\" type=\"application/atom+xml\" href=\"$protocol://$host$uri/$extra\" />\n";
	    $feed .= "<link rel=\"alternate\" type=\"text/html\" href=\"$protocol://$host$uri/CategoryWatchlist.php?query=$query\" />\n";
	    $feed .= "<updated>$updated</updated>\n";

	    // Sort by date, namespace, title
	    $dategroups = [];
	    foreach ($results['results'] as &$result) {
	        $date = $result['diffdate'];
	        unset($result['diffdate']);
	        if (! isset($dategroups[$date])) $dategroups[$date] = array();
	        $dategroups[$date][] = $result;
	    }
	    unset($result);

	    $redirectmsg = htmlentities(' (' . $l10n->get('redirect') . ')', ENT_COMPAT, 'UTF-8');

	    $summary = htmlentities($l10n->get('mostrecentresults'), ENT_COMPAT, 'UTF-8') . "<table><thead><tr><th>" .
	   	    htmlentities($l10n->get('page', true), ENT_COMPAT, 'UTF-8') . "</th><th>+/&ndash;</th><th>" .
	   	    htmlentities($l10n->get('category', true), ENT_COMPAT, 'UTF-8') . " / " .
	   	    htmlentities($l10n->get('template', true), ENT_COMPAT, 'UTF-8') . "</th></tr></thead><tbody>\n";

	   	    foreach ($dategroups as $date => &$dategroup) {
	   	        usort($dategroup, array($this, 'resultgroupsort'));
	   	        $displaydate = htmlentities($l10n->formatDate(MySQLDate::toPHP($date), 'datetimefmt'), ENT_COMPAT, 'UTF-8');
	   	        $summary .= "<tr><td><i>$displaydate</i></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
	   	        $x = 0;
	   	        $prevtitle = '';
	   	        $prevaction = '';

	   	        foreach ($dategroup as &$result) {
	   	            $title = $result['title'];
	   	            $action = $result['plusminus'];
	   	            $category = htmlentities($result['category'], ENT_COMPAT, 'UTF-8');
	   	            if ($result['cat_template'] == 'T') $category = '{{' . $category . '}}';
	   	            $displayaction = ($action == '-') ? '&ndash;' : $action;
	   	            $flags = $result['flags'];

	   	            if ($title == $prevtitle && $action == $prevaction) {
	   	                $summary .= "; $category";
	   	            } elseif ($title == $prevtitle) {
	   	                $summary .= "</td></tr>\n";
	   	                $summary .= "<tr><td>&nbsp;</td><td>$displayaction</td><td>$category";
	   	            } else {
	   	                if ($x++ > 0) $summary .= "</td></tr>\n";
	   	                $redirectadd = '';
	   	                if ($flags & 1) $redirectadd = $redirectmsg;

	   	                $summary .= "<tr><td><a href=\"$wikiprefix" . urlencode(str_replace(' ', '_', $title)) . "\">" .
	   	   	                htmlentities($title, ENT_COMPAT, 'UTF-8') . "</a>$redirectadd</td><td>$displayaction</td><td>$category";
	   	            }
	   	            $prevtitle = $title;
	   	            $prevaction = $action;
	   	        }

	   	        if ($x > 0) $summary .= "</td></tr>\n";
	   	    }

	   	    $summary .= "</tbody></table>\n";
	   	    $summary = htmlentities($summary, ENT_COMPAT, 'UTF-8');
	   	    unset($dategroup);
	   	    unset($result);

	   	    foreach ($dategroups as $date => &$dategroup) {
	   	        $date = MySQLDate::toPHP($date);
	   	        $humandate = htmlentities(htmlentities($l10n->formatDate($date, 'datetimefmt'), ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8');
	   	        $updated = gmdate("Y-m-d\TH:i:s\Z", $date);
	   	        $title = htmlentities(htmlentities($l10n->get('resultsfor'), ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8') . " $humandate";

	   	        $feed .= "<entry>\n";
	   	        $feed .= "<id>$protocol://$host$uri/$extra&amp;date=$date</id>\n";
	   	        $feed .= "<title type=\"html\">$title</title>\n";
	   	        $feed .= "<link rel=\"alternate\" type=\"text/html\" href=\"$protocol://$host$uri/CategoryWatchlist.php?query=$query\" />\n";
	   	        $feed .= "<updated>$updated</updated>\n";
	   	        $feed .= "<summary type=\"html\">$summary</summary>\n";
	   	        $feed .= "<author><name>CategoryWatchlistBot</name></author>\n";
	   	        $feed .= "</entry>\n";
	   	        break; // Only want one entry
	   	    }
	   	    unset($dategroup);

	   	    $feed .= '</feed>';

	   	    FileCache::putData(CategoryWatchlistBot::CACHE_PREFIX_ATOM . $query, $feed);

	   	    echo $feed;

	   	    return true;
	}

	/**
	 * Generate an dump atom feed
	 *
	 * @param string $query Query id
	 * @return boolean true - success, false - failure
	 */
	public function generateDumpAtom($query)
	{
		header('Content-Type: application/atom+xml');

		// Check the cache
		$feed = FileCache::getData(WMDumpWatchlist::CACHE_PREFIX_ATOM . $query);
		if (! empty($feed)) {
			echo $feed;
			return true;
		}

		$sth = $this->dbh_tools->prepare("SELECT * FROM dumpquerys WHERE hash = ?");
		$sth->bindParam(1, $query);
		$sth->execute();

		if (! ($row = $sth->fetch(PDO::FETCH_ASSOC))) return false;

		$params = unserialize($row['params']);
		$results = $this->getDumpResults($params);

		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = "DumpWatchlist.php?action=atom&amp;query=$query";
		$protocol = HttpUtil::getProtocol();

		$updated = $row['lastdump'];
		if (is_null($updated)) $updated = gmdate("Y-m-d\TH:i:s\Z");
		else $updated = gmdate("Y-m-d\TH:i:s\Z", MySQLDate::toPHP($updated));

		$title = 'Wikimedia Dump Watchlist';

		$feed = "<?xml version=\"1.0\"?>\n<feed xmlns=\"http://www.w3.org/2005/Atom\" xml:lang=\"en\">\n";
		$feed .= "<id>$protocol://$host$uri/$extra</id>\n";
		$feed .= "<title type=\"html\">$title</title>\n";
		$feed .= "<link rel=\"self\" type=\"application/atom+xml\" href=\"$protocol://$host$uri/$extra\" />\n";
		$feed .= "<link rel=\"alternate\" type=\"text/html\" href=\"$protocol://$host$uri/DumpWatchlist.php?query=$query\" />\n";
		$feed .= "<updated>$updated</updated>\n";

		// Reverse sort by file update time

		$results = $results['results'];
		usort($results, function ($a, $b) {
		    return -strcmp($a['lastdump'], $b['lastdump']); // reverse
		});

		$summary = "Most recent dump(s)<table><thead><tr><th>" .
			"Wiki</th><th>Filename</th><th>" .
			"Last dump</th><th>Filesize</th></tr></thead><tbody>\n";

		foreach ($results as $file) {
		    $wikiname = htmlentities($file['wikiname'], ENT_COMPAT, 'UTF-8');
		    $filename = htmlentities($file['filename'], ENT_COMPAT, 'UTF-8');
		    $lastdump = 'waiting';
		    $filesize = 'waiting';
		    if (! is_null($file['lastdump'])) $lastdump = $file['lastdump'];
		    if (! is_null($file['filesize'])) {
		        $filesize = Convert::formatBytes($file['filesize']);
		        $filesize = $filesize[0] . ' ' . $filesize[1] . 'B';
		    }

			$summary .= "<tr><td>$wikiname</td><td>$filename</td><td>$lastdump</td><td>$filesize</td></tr>\n";
		}

		$summary .= "</tbody></table>\n";
		$summary = htmlentities($summary, ENT_COMPAT, 'UTF-8');

		$title = 'Wikimedia dump completion';

		$feed .= "<entry>\n";
		$feed .= "<id>$protocol://$host$uri/$extra&amp;date=$updated</id>\n";
		$feed .= "<title type=\"html\">$title</title>\n";
		$feed .= "<link rel=\"alternate\" type=\"text/html\" href=\"$protocol://$host$uri/DumpWatchlist.php?query=$query\" />\n";
		$feed .= "<updated>$updated</updated>\n";
		$feed .= "<summary type=\"html\">$summary</summary>\n";
		$feed .= "<author><name>DumpWatchlistBot</name></author>\n";
		$feed .= "</entry>\n";

		$feed .= '</feed>';

		FileCache::putData(WMDumpWatchlist::CACHE_PREFIX_ATOM . $query, $feed);

		echo $feed;

		return true;
	}

    /**
	 * Sort a result group by namespace, title
	 *
	 * @param unknown $a
	 * @param unknown $b
	 * @return number
	 */
	function resultgroupsort($a, $b)
	{
		$ans = $a['ns'];
		$bns = $b['ns'];

		if ($ans > $bns) return 1;
		if ($ans < $bns) return -1;
		return strcmp($a['title'], $b['title']);
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

		$results = [];

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

	/**
	 * Process template redirect.
	 *
	 * @param string $wikiname
	 * @param string $templatename
	 * @return string Resolved template name
	 */
	public function processTemplateRedirect($wikiname, $templatename)
	{
	    static $mediawiki = null;
	    if (empty($mediawiki)) {
	        $wikilang = substr($wikiname, 0, -4);
	        $domain = "$wikilang.wikipedia.org";
	        $mediawiki = $this->serviceMgr->getMediaWiki($domain);
	    }

		$resolvedTitle = $mediawiki->resolvePageTitle("Template:$templatename");

		if (! empty($resolvedTitle)) {
			$templatename = substr($resolvedTitle, 9);
		}

		return $templatename;
	}
}