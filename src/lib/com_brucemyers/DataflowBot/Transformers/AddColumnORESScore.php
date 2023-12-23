<?php
/**
 Copyright 2016 Myers Enterprises II

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

use com_brucemyers\DataflowBot\Component;
use com_brucemyers\DataflowBot\io\FlowReader;
use com_brucemyers\DataflowBot\io\FlowWriter;
use com_brucemyers\DataflowBot\ComponentParameter;
use com_brucemyers\Util\Curl;
use com_brucemyers\Util\Logger;

class AddColumnORESScore extends AddColumn
{
	var $firstRowHeaders;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Add Column Current Revision ID';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Add a new column with the current revision ID. enwiki only.';
	}

	/**
	 * Get the component identifier.
	 *
	 * @return string ID
	 */
	public function getID()
	{
		return 'ACORES';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		$basetypes = parent::getParameterTypes();
		$types = array(
		    new ComponentParameter('lookupcol', ComponentParameter::PARAMETER_TYPE_STRING, 'Article revid column #',
		    	'Column numbers start at 1',
		    	array('size' => 3, 'maxlength' => 3)),
			new ComponentParameter('wiki', ComponentParameter::PARAMETER_TYPE_ENUM, 'Wiki', '',
				array('enum' => array('enwiki' => 'English Wikipedia'))),
			new ComponentParameter('model', ComponentParameter::PARAMETER_TYPE_STRING, 'ORES model', '',
				array('size' => 6, 'maxlength' => 32))
		);

		return array_merge($basetypes, $types);
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
		$column = (int)$this->paramValues['lookupcol'] - 1;
		if ($column < 0) return "Invalid Article name column # {$this->paramValues['lookupcol']}";
		$curl = $this->serviceMgr->getCurl();
		$wiki = $this->paramValues['wiki'];
		$max_records = 1;

		while ($rows = $reader->readRecords($max_records)) {
		    $revid = false;
		    
			// Gather the pagenames
			foreach ($rows as $key => $row) {
				if ($firstrow) {
					$firstrow = false;
					if ($this->isFirstRowHeaders()) {
						$retval = $this->insertColumn($rows[$key], $this->paramValues['title']);
						if ($retval !== true) return $retval;
						continue;
					}
				}

				if ($column >= count($row)) return "Invalid Article name column # {$this->paramValues['lookupcol']}";

				$revid = $row[$column];
			}
			
			if ($revid === false) {
			    $writer->writeRecords($rows);
			    continue;
			}

			$URL = "https://api.wikimedia.org/service/lw/inference/v1/models/$wiki-articlequality:predict";
			//Logger::log($URL);

			$trys = 0;

			while ($trys++ < 5) {
			    $data = $curl::getUrlContents($URL, "{\"rev_id\": $revid}");
				if ($data === false) {
					if ($trys == 5) return "Problem reading $URL (" . Curl::$lastError . ")";
					sleep($trys * 60);
					continue;
				}

				$data = json_decode($data, true);

				if (is_null($data)) {
					if ($trys == 5) return "json_decode error for $URL";
					sleep($trys * 60);
					continue;
				}

				if (! isset($data[$wiki])) {
					if ($trys == 5) {
						Logger::log(print_r($data, true));
						return "$wiki not set for $URL";
					}
					sleep($trys * 60);
					continue;
				}

				break;
			}

			// Process each page

			foreach ($rows as $key => $row) {
				if (! isset($data[$wiki]['scores'][$revid]['articlequality']['score']['prediction'])) {
					$value = 'GA';
				} else {
				    $value = $data[$wiki]['scores'][$revid]['articlequality']['score']['prediction'];
				}

				$retval = $this->insertColumn($rows[$key], $value);
				if ($retval !== true) return $retval;
			}

			$writer->writeRecords($rows);
		}

		return true;
	}
}
