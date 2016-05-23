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

class ToWikitable extends Transformer
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
		return 'Create Wikitable';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Put data into a Wikitable';
	}

	/**
	 * Get the component identifier.
	 *
	 * @return string ID
	 */
	public function getID()
	{
		return 'ToWiki';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
			new ComponentParameter('sortable', ComponentParameter::PARAMETER_TYPE_BOOL, 'Sortable', '',
					array('default' => 1)),
			new ComponentParameter('unsortable', ComponentParameter::PARAMETER_TYPE_STRING, 'Non-sortable columns',
				'Comma separated list of column numbers; Column numbers start at 1',
		    	array('size' => 10, 'maxlength' => 64))
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
		return false;
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
		$sortable = ($this->paramValues['sortable'] == '1') ? ' sortable' : '';
		$unsortables = array();
		if (! empty($this->paramValues['unsortable'])) $unsortables = explode(',', $this->paramValues['unsortable']);

		$nonsorts = array();
		if (! empty($sortable)) {
			foreach ($unsortables as $unsortable) {
				$colnum = (int)trim($unsortable) - 1;
				if ($colnum < 0) return "Invalid unsortable column #";
				$nonsorts[] = $colnum;
			}
		}

		$header = array(array("{| class=\"wikitable$sortable\""));
		$writer->writeRecords($header);

		while ($rows = $reader->readRecords()) {
			$lines = array();

			foreach ($rows as $key => $row) {
				if ($firstrow) {
					$firstrow = false;
					if ($this->firstRowHeaders) {
						$headers = array();
						foreach ($row as $key => $column) {
							$value = $column;
							if (in_array($key, $nonsorts)) $value = 'class="unsortable"|' . $value;
							$headers[] = $value;
						}

						$lines[] = array("|-");
						$headers = implode('!!', $headers);
						$lines[] = array("!$headers");

						continue;
					}
				}

				$lines[] = array("|-");
				$row = implode('||', $row);
				$lines[] = array("|$row");
			}

			$writer->writeRecords($lines);
		}

		$footer = array(array("|}"));
		$writer->writeRecords($footer);

		return true;
	}
}