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

namespace com_brucemyers\DataflowBot\Loaders;

use com_brucemyers\DataflowBot\io\FlowReader;
use com_brucemyers\DataflowBot\ComponentParameter;

class WikiLoader extends Loader
{
	var $paramValues;
	var $firstRowHeaders;
	var $flowID;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'MediaWiki';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Save data to a wiki';
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
				array('enum' => array('enwiki' => 'English Wikipedia'))),
			new ComponentParameter('pagename', ComponentParameter::PARAMETER_TYPE_STRING, 'Pagename', '',
				array('size' => 50, 'maxlength' => 256)),
			new ComponentParameter('header', ComponentParameter::PARAMETER_TYPE_STRING, 'Header', '',
				array('rows' => 10, 'cols' => 130, 'maxlength' => 2048)),
			new ComponentParameter('footer', ComponentParameter::PARAMETER_TYPE_STRING, 'Footer', '',
				array('rows' => 10, 'cols' => 130, 'maxlength' => 2048))
		);
	}

	/**
	 * Initialize a component.
	 *
	 * @param array $params Component specific parameters
	 * @param bool $isFirstRowHeaders Is the first row in input data headers?
	 * @param int $flowID Flow ID
	 * @return mixed true = success, string = error message
	 */
	public function init($params, $isFirstRowHeaders, $flowID)
	{
		$this->paramValues = $params;
		$this->firstRowHeaders = $isFirstRowHeaders;
		$this->flowID = $flowID;

		return true;
	}

	/**
	 * Is the first row column headers?
	 *
	 * @return bool Is the first row column headers?
	 */
	public function isFirstRowHeaders()
	{
		return $this->firstRowHeaders;
	}

	/**
	 * Load data from a reader into an external store.
	 *
	 * @param FlowReader $reader
	 * @return mixed true = success, string = error message
	 */
	public function process(FlowReader $reader)
	{
		$wiki = $this->paramValues['wiki'];
		$pagename = 'User:DataflowBot/output/' . $this->paramValues['pagename'] . ' (id-' . $this->flowID . ')';
		$header = $this->paramValues['header'];
		$footer = $this->paramValues['footer'];

		$output = $header;
		if (! empty($output)) {
			if (mb_substr($output, mb_strlen($output, 'UTF-8') - 1, 1, 'UTF-8') != "\n") $output .= "\n";
		}

		$resultWriter = $this->serviceMgr->getWikiResultWriter($wiki);

		while ($rows = $reader->readRecords()) {

			foreach ($rows as &$row) {
				$output .= implode(',', $row);
				$output .= "\n";
			}
		}

		$output .= $footer;

		$resultWriter->writeResults($pagename, $output, 'Data update');

		return true;
	}
}