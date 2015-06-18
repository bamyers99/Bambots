<?php
/**
 Copyright 2015 Myers Enterprises II

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

namespace com_brucemyers\DataflowBot;

use com_brucemyers\Util\Config;
use com_brucemyers\Util\Curl;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\MediaWiki\WikiResultWriter;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\Util\FileCache;
use PDO;
use Exception;

class ServiceManager
{
	const ENWIKI_HOST = 'DataflowBot.enwiki_host';
	const TOOLS_HOST = 'DataflowBot.tools_host';
	const WIKIDATA_HOST = 'DataflowBot.wikidata_host';
	const LABSDB_USERNAME = 'DataflowBot.labsdb_username';
	const LABSDB_PASSWORD = 'DataflowBot.labsdb_password';
	const OUTPUTTYPE = 'DataflowBot.outputtype';
	const OUTPUTDIR = 'DataflowBot.outputdir';

	protected $dbdata = array();
	protected $dbuser;
	protected $dbpass;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->dbdata['enwiki'] = array('host' => Config::get(self::ENWIKI_HOST), 'dbname' => 'enwiki_p');
		$this->dbdata['tools'] = array('host' => Config::get(self::TOOLS_HOST), 'dbname' => 'enwiki_p');
		$this->dbdata['wikidatawiki'] = array('host' => Config::get(self::WIKIDATA_HOST), 'dbname' => 'wikidatawiki_p');

     	$this->dbuser = Config::get(self::LABSDB_USERNAME);
    	$this->dbpass = Config::get(self::LABSDB_PASSWORD);
	}

	/**
	 * Get the Curl class
	 *
	 * @return Curl
	 */
	public function getCurl()
	{
		return new Curl();
	}

	/**
	 * Get a database connection.
	 * User is responsible for setting connection to null when done.
	 *
	 * @param string $wiki
	 * @return PDO DB connection
	 */
	public function getDBConnection($wiki)
	{
		if (! isset($this->dbdata[$wiki])) throw new Exception('ServiceManager.getDBConnection - unsupported wiki');
		$dbdata = $this->dbdata[$wiki];

		$dbh = new PDO("mysql:host={$dbdata['host']};dbname={$dbdata['dbname']};charset=utf8", $this->dbuser, $this->dbpass);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $dbh;
	}

	/**
	 * Get a MediaWiki instance.
	 *
	 * @param String $wiki
	 * @return MediaWiki
	 */
	public function getMediaWiki($wiki)
	{
		if ($wiki != 'enwiki') throw new Exception('ServiceManager.getMediaWiki - only enwiki supported');
		$url = Config::get(MediaWiki::WIKIURLKEY);
		$wiki = new MediaWiki($url);
		$username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
		$password = Config::get(MediaWiki::WIKIPASSWORDKEY);
		$wiki->login($username, $password);

		return $wiki;
	}

	/**
	 * Get a wiki result writer.
	 *
	 * @param string $wiki
	 * @return ResultWriter result writer
	 */
	public function getWikiResultWriter($wiki)
	{
		$outputtype = Config::get(self::OUTPUTTYPE);

	    if ($outputtype == 'wiki') $resultwriter = new WikiResultWriter($this->getMediaWiki($wiki));
    	else {
        	$outputDir = Config::get(self::OUTPUTDIR);
        	$outputDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputDir);
        	$outputDir = preg_replace('!(/|\\\\)$!', '', $outputDir); // Drop trailing slash
        	$outputDir .= DIRECTORY_SEPARATOR;
        	$resultwriter = new FileResultWriter($outputDir);
    	}

    	return $resultwriter;
	}
}