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

namespace com_brucemyers\DataflowBot\Extractors;

use com_brucemyers\DataflowBot\io\FlowWriter;
use com_brucemyers\DataflowBot\ComponentParameter;
use com_brucemyers\Util\Curl;
use com_brucemyers\MediaWiki\MediaWiki;

class TopPageViews extends Extractor
{
	var $paramValues;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Wiki Top Page Views';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Retrieve top page views for wiki.';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
			new ComponentParameter('wiki', ComponentParameter::PARAMETER_TYPE_ENUM, 'Wiki', '',
				array('enum' => array('en.wikipedia.org' => 'English Wikipedia'))),
			new ComponentParameter('date', ComponentParameter::PARAMETER_TYPE_STRING, 'Date in PHP strtotime() format', '',
				array('size' => 6, 'maxlength' => 32))
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
		$URL = "https://wikimedia.org/api/rest_v1/metrics/pageviews/top/{$this->paramValues['wiki']}/all-access/" .
			date('Y/m/d', strtotime($this->paramValues['date']));
		$curl = $this->serviceMgr->getCurl();
		$data = $curl::getUrlContents($URL);
		if ($data === false) return "Problem reading $URL (" . Curl::$lastError . ")";

		$rows = array(array('Article', 'Views'));
		$writer->writeRecords($rows);

		$data = json_decode($data, true);

		foreach ($data['items'][0]['articles'] as $article) {
			$page = $article['article'];
			$views = $article['views'];
			$ns_name = MediaWiki::getNamespaceName($page);
			if ($ns_name != '') continue;
			if ($page == 'Main_Page') continue;

			$rows = array(array($page, $views));
			$writer->writeRecords($rows);
		}

		return true;
	}
}