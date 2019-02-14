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

use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use PDO;
use PDOException;
use com_brucemyers\Util\Logger;
use Exception;

class ServiceManager
{
	const WIKI_HOST = 'TemplateParamBot.wiki_host';
	const TOOLS_HOST = 'TemplateParamBot.tools_host';
	const LABSDB_USERNAME = 'TemplateParamBot.labsdb_username';
	const LABSDB_PASSWORD = 'TemplateParamBot.labsdb_password';

	protected $dbuser;
	protected $dbpass;
	protected $wiki_host;
	protected $tools_host;

	/**
	 * Constructor
	 */
	public function __construct()
	{
     	$this->dbuser = Config::get(self::LABSDB_USERNAME);
    	$this->dbpass = Config::get(self::LABSDB_PASSWORD);
    	$this->wiki_host = Config::get(self::WIKI_HOST);
	    $this->tools_host = Config::get(self::TOOLS_HOST);
	}

	/**
	 * Get a database connection.
	 * User is responsible for setting connection to null when done.
	 *
	 * @param string $wikiname
	 * @return PDO DB connection
	 */
	public function getDBConnection($wikiname)
	{
	    try {
    		if ($wikiname == 'tools') {
    			$dbh = new PDO("mysql:host={$this->tools_host};dbname=s51454__TemplateParamBot_p;charset=utf8", $this->dbuser, $this->dbpass);

    		} else {
        		$wiki_host = $this->wiki_host;
        		if (empty($wiki_host)) $wiki_host = "$wikiname.analytics.db.svc.eqiad.wmflabs";
        		$dbh = new PDO("mysql:host=$wiki_host;dbname={$wikiname}_p;charset=utf8", $this->dbuser, $this->dbpass);
    		}
	    } catch (PDOException $e) {
	        Logger::log($e->getMessage());
	        throw new Exception('Connection error, see log for details');
	    }

    	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $dbh;
	}

	/**
	 * Get a MediaWiki instance.
	 *
	 * @param String $domain
	 * @return MediaWiki
	 */
	public function getMediaWiki($domain)
	{
	    $url = "https://$domain/w/api.php";
	    $wiki = new MediaWiki($url);
	    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
	    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
	    $wiki->login($username, $password);

		return $wiki;
	}

	public function getToolsHost()
	{
		return $this->tools_host;
	}

	public function getUser()
	{
		return $this->dbuser;
	}

	public function getPass()
	{
		return $this->dbpass;
	}
}