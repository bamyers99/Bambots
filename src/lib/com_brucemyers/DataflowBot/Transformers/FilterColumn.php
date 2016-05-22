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

namespace com_brucemyers\DataflowBot\Transformers;

use com_brucemyers\DataflowBot\io\FlowReader;
use com_brucemyers\DataflowBot\io\FlowWriter;
use com_brucemyers\DataflowBot\ComponentParameter;

class FilterColumn extends Transformer
{
	var $paramValues;
	var $firstRowHeaders;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Filter Column';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Filter a column by regex';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
			new ComponentParameter('filtercol', ComponentParameter::PARAMETER_TYPE_STRING, 'Filter column #',
		    	'Column numbers start at 1',
		    	array('size' => 5, 'maxlength' => 6)),
			new ComponentParameter('filterregex', ComponentParameter::PARAMETER_TYPE_STRING, 'Regular expression',
		    	'! must be escaped',
		    	array('size' => 30, 'maxlength' => 256))
		);
	}

	/**
	 * Initialize transformer.
	 *
	 * @param array $params Parameters
	 * @param bool $isFirstRowHeaders Is the first row in input data headers?
	 * @return mixed true = success, string = error message
	 */
	public function init($params, $isFirstRowHeaders)
	{
		$this->paramValues = $params;
		$this->firstRowHeaders = $isFirstRowHeaders;

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
	 * Transform reader data, output to writer.
	 *
	 * @param FlowReader $reader
	 * @param FlowWriter $writer
	 * @return mixed true = success, string = error message
	 */
	public function process(FlowReader $reader, FlowWriter $writer)
	{
		$firstrow = true;
		$colnum = (int)$this->paramValues['filtercol'] - 1;
		if ($colnum < 0) return "Invalid filter column #";
		$regex = $this->paramValues['filterregex'];

		while ($rows = $reader->readRecords()) {
			foreach ($rows as $row) {
				if ($firstrow) {
					$firstrow = false;
					if ($this->isFirstRowHeaders()) {
						$rows = array($row);
						$writer->writeRecords($rows);
						continue;
					}
				}

				if ($colnum >= count($row)) return "Invalid filter column #";
				if (preg_match("!$regex!u", $row[$colnum])) {
					$rows = array($row);
					$writer->writeRecords($rows);
				}
			}
		}

		return true;
	}
}