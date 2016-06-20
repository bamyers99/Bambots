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

namespace com_brucemyers\WikidataBot;

use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\MediaWiki\WikiResultWriter;
use com_brucemyers\MediaWiki\FileResultWriter;
use Exception;

class ServiceManager
{
	const OUTPUTTYPE = 'WikidataBot.outputtype';
	const OUTPUTDIR = 'WikidataBot.outputdir';

	protected $dbdata = array();
	protected $dbuser;
	protected $dbpass;

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Get a MediaWiki instance.
	 *
	 * @param String $wiki
	 * @return MediaWiki
	 */
	public function getMediaWiki($wiki)
	{
		if ($wiki != 'wikidatawiki') throw new Exception('ServiceManager.getMediaWiki - only wikidatawiki supported');
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
	 * @param MediaWiki $mediawiki
	 * @return ResultWriter result writer
	 */
	public function getWikiResultWriter(MediaWiki $mediawiki)
	{
		$outputtype = Config::get(self::OUTPUTTYPE);

	    if ($outputtype == 'wiki') $resultwriter = new WikiResultWriter($mediawiki);
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