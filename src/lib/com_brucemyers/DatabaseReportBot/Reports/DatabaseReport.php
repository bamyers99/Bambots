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

abstract class DatabaseReport
{

	/**
	 * Initialize report.
	 *
	 * @param array $apis; keys = dbh_wiki, wiki_host, dbh_tools, tools_host, dbh_wikidata, data_host, user, pass, mediawiki, renderedwiki, datawiki
	 * @param array $params
	 * @return bool continue - Should the report generation continue?
	 */
	public function init($apis, $params)
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
	 * @param array $apis; keys = dbh_wiki, wiki_host, dbh_tools, tools_host, dbh_wikidata, data_host, user, pass, mediawiki, renderedwiki, datawiki
	 * @return array Report row data, the first column must only be a page name with optional namespace. {{dbr link}} will
	 * 		be applied to the page name. May optionally include a 'groups' key => array(Group name => group rows). May optionally
	 * 		include 'linktemplate' key to specify first column link (false = don't link).
	 */
	public abstract function getRows($apis);
}