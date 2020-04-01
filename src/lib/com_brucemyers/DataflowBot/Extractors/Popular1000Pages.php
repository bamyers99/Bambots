<?php
/**
 Copyright 2020 Myers Enterprises II

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

namespace com_brucemyers\DataflowBot\Extractors;

use com_brucemyers\DataflowBot\io\FlowWriter;
use com_brucemyers\DataflowBot\ComponentParameter;
use com_brucemyers\Util\WikitableParser;
use com_brucemyers\MediaWiki\MediaWiki;

class Popular1000Pages extends Extractor
{
	var $paramValues;

	const HEADING_POS_RANK = 0;
	const HEADING_POS_ARTICLE = 1;
	const HEADING_POS_VIEWS = 2;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Wiki Top 1000 Popular pages';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Retrieve top 1000 popular pages for wiki.';
	}

	/**
	 * Get the component identifier.
	 *
	 * @return string ID
	 */
	public function getID()
	{
		return 'Pop1000Pg';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
			new ComponentParameter('pagename', ComponentParameter::PARAMETER_TYPE_STRING, 'Page name', '',
				array('size' => 30, 'maxlength' => 256)),
		);
	}

	/**
	 * Initialize extractor.
	 *
	 * @param array $params Parameters
	 * @return mixed true = success, string = error message
	 */
	public function init($params)
	{
		$this->paramValues = $params;

		return true;
	}

	/**
	 * Is the first row column headers?
	 *
	 * @return bool Is the first row column headers?
	 */
	public function isFirstRowHeaders()
	{
		return true;
	}

	/**
	 * Extract data and write to writer.
	 *
	 * @param FlowWriter $writer
	 * @return mixed true = success, string = error message
	 */
	public function process(FlowWriter $writer)
	{
		$pagename = $this->paramValues['pagename'];

		$lastrun = strtotime('last saturday');

		$this->serviceMgr->setVar($this->getID() . '#year', date('Y', $lastrun));
		$this->serviceMgr->setVar($this->getID() . '#month', date('m', $lastrun));
		$this->serviceMgr->setVar($this->getID() . '#day', date('d', $lastrun));

		$wiki = $this->serviceMgr->getMediaWiki('enwiki');

		$poppages = $wiki->getpage($pagename);

		$tables = WikitableParser::getTables($poppages);

		$poppages = false;
		foreach ($tables as $table) {
			if (isset($table['headings'][self::HEADING_POS_RANK]) && $table['headings'][self::HEADING_POS_RANK] == 'Rank'){
				$poppages = $table;
				break;
			}
		}

		if (empty($poppages)) return "Popular pages table not found in $pagename";
		if ($table['headings'][self::HEADING_POS_ARTICLE] != 'Article') return "Article column not found in $pagename";
		if ($table['headings'][self::HEADING_POS_VIEWS] != 'Total weekly views') return "Total weekly views column not found in $pagename";

		$rows = array(array('Article', 'Views'));
		$writer->writeRecords($rows);

		foreach ($poppages['rows'] as $row) {
			$page = str_replace(' ', '_', $row[self::HEADING_POS_ARTICLE]);
			$page = preg_replace('/\\[|\\]/u', '', $page);
			$pageviews = $row[self::HEADING_POS_VIEWS];

			$ns_name = MediaWiki::getNamespaceName($page);
			if ($ns_name != '') continue;
			if ($page == 'Main_Page' || $page == 'Main_page') continue;

			$rows = array(array($page, $pageviews));
			$writer->writeRecords($rows);
		}

		return true;
	}
}