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
use com_brucemyers\Util\CSVString;

class UrlCsvExtractor extends Extractor
{
	var $paramValues;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'URL Delimited Text';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Retrieve a delimited text file from a URL.';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
		    new ComponentParameter('sep', ComponentParameter::PARAMETER_TYPE_ENUM, 'Field separator', '',
		    		array('enum' => array(',' => ',', ';' => ';', '|' => '|', 'space' => '<space>', 'tab' => '<tab>'))),
			new ComponentParameter('delim', ComponentParameter::PARAMETER_TYPE_ENUM, 'Text delimiter', '',
					array('enum' => array('"' => '"', "'" => "'", '' => '<none>'))),
			new ComponentParameter('firstrow', ComponentParameter::PARAMETER_TYPE_BOOL, 'First row contains headings', ''),
			new ComponentParameter('subdmn', ComponentParameter::PARAMETER_TYPE_STRING, 'Input file http://', '',
					array('size' => 6, 'maxlength' => 32, 'concatwithnext' => true)),
			new ComponentParameter('file', ComponentParameter::PARAMETER_TYPE_STRING, '.wmflabs.org/', '',
					array('size' => 50, 'maxlength' => 1024))
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
		return (! empty($this->paramValues['firstrow']));
	}

	/**
	 * Extract data and write to writer.
	 *
	 * @param FlowWriter $writer
	 * @return mixed true = success, string = error message
	 */
	public function process(FlowWriter $writer)
	{
		$URL = "http://{$this->paramValues['subdmn']}.wmflabs.org/{$this->paramValues['file']}";
		$curl = $this->serviceMgr->getCurl();
		$data = $curl::getUrlContents($URL);
		if ($data === false) return "Problem reading $URL (" . Curl::$lastError . ")";

		$rows = preg_split('!\r?\n!', $data, 0, PREG_SPLIT_NO_EMPTY);

		$sep = $this->paramValues['sep'];
		if ($sep == 'space') $sep = ' ';
		elseif ($sep == 'tab') $sep = "\t";

		$delim = $this->paramValues['delim'];
		if ($sep == "\t") $delim = '';

		foreach ($rows as &$row) {
			$csvdata = CSVString::parse($row, $sep, $delim);
			$csvdata = array($csvdata);
			$writer->writeRecords($csvdata);
		}

		return true;
	}
}