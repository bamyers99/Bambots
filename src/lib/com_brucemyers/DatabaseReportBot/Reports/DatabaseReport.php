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

namespace com_brucemyers\DatabaseReportBot\Reports;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\RenderedWiki\RenderedWiki;
use PDO;

abstract class DatabaseReport
{

	/**
	 * Initialize report.
	 *
	 * @param PDO $dbh_wiki
	 * @param PDF $dbh_tools
	 * @param MediaWiki $mediawiki
	 * @param PDO $dbh_wikidata
	 * @param array $params
	 * @return bool continue - Should the report generation continue?
	 */
	public function init(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, $params, PDO $dbh_wikidata)
	{
		return true;
	}

	/**
	 * Get usage info.
	 *
	 * @return string
	 */
	public function getUsage()
	{
		return '';
	}

	/**
	 * Get the report title.
	 *
	 * @return string Report title
	 */
	public abstract function getTitle();

	/**
	 * Get the report introduction.
	 *
	 * @return string Introduction, %s will get substituted with the run date.
	 */
	public abstract function getIntro();

	/**
	 * Get the report headings.
	 *
	 * @return array Report row headings
	 */
	public abstract function getHeadings();

	/**
	 * Get the report rows.
	 *
	 * @param PDO $dbh_wiki
	 * @param PDF $dbh_tools
	 * @param MediaWiki $mediawiki
	 * @param RenderedWiki $renderedwiki
	 * @param PDO $dbh_wikidata
	 * @return array Report row data, the first column must only be a page name with optional namespace. {{dbr link}} will
	 * 		be applied to the page name. May optionally include a 'groups' key => array(Group name => group rows). May optionally
	 * 		include 'linktempate' key to specify first column link (false = don't link).
	 */
	public abstract function getRows(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, RenderedWiki $renderedwiki, PDO $dbh_wikidata);
}