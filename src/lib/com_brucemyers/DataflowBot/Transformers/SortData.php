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

class SortData extends Transformer
{
	var $paramValues;
	var $firstRowHeaders;
	var $sorts;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Sort Data';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Sort by one or two columns';
	}

	/**
	 * Get the component identifier.
	 *
	 * @return string ID
	 */
	public function getID()
	{
		return 'Sort';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
			new ComponentParameter('sortcol1', ComponentParameter::PARAMETER_TYPE_STRING, 'Sort 1 column #',
		    	'Column numbers start at 1',
		    	array('size' => 5, 'maxlength' => 6)),
			new ComponentParameter('sorttype1', ComponentParameter::PARAMETER_TYPE_ENUM, 'Sort 1 type', '',
				array('enum' => array('enum' => 'Enumeration', 'numeric' => 'Numeric', 'alpha' => 'Alphabetic'))),
			new ComponentParameter('sortdir1', ComponentParameter::PARAMETER_TYPE_ENUM, 'Sort 1 direction', '',
				array('enum' => array('asc' => 'Ascending', 'desc' => 'Descending'))),
			new ComponentParameter('sortenum1', ComponentParameter::PARAMETER_TYPE_STRING, 'Sort 1 enumeration',
		    	'Separate enums with |',
		    	array('size' => 30, 'maxlength' => 256)),

			new ComponentParameter('sortcol2', ComponentParameter::PARAMETER_TYPE_STRING, 'Sort 2 column #',
		    	'Column numbers start at 1',
		    	array('size' => 5, 'maxlength' => 6)),
			new ComponentParameter('sorttype2', ComponentParameter::PARAMETER_TYPE_ENUM, 'Sort 2 type', '',
				array('enum' => array('enum' => 'Enumeration', 'numeric' => 'Numeric'))),
			new ComponentParameter('sortdir2', ComponentParameter::PARAMETER_TYPE_ENUM, 'Sort 2 direction', '',
				array('enum' => array('asc' => 'Ascending', 'desc' => 'Descending'))),
			new ComponentParameter('sortenum2', ComponentParameter::PARAMETER_TYPE_STRING, 'Sort 2 enumeration',
		    	'Separate enums with |',
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
		$this->sorts = array();

		for ($x = 1; $x <= 5; ++$x) {
			if (! isset($this->paramValues["sortcol$x"])) break;

			$colnum = (int)$this->paramValues["sortcol$x"] - 1;
			if ($colnum < 0) return "Invalid sort $x column #";
			$sorttype = $this->paramValues["sorttype$x"];
			$sortdir = $this->paramValues["sortdir$x"];
			$sortenum = false;
			if (isset($this->paramValues["sortenum$x"])) $sortenum = array_flip(explode('|', $this->paramValues["sortenum$x"]));

			$this->sorts[] = array('colnum' => $colnum, 'type' => $sorttype, 'dir' => $sortdir, 'enum' => $sortenum);
		}

		$rows = $reader->readRecords(-1); // read all records


		if (! empty($rows)) {
			foreach ($this->sorts as $sort) {
				if ($sort['colnum'] >= count($rows[0])) return "Invalid sort " . ($sort['colnum'] + 1) . " column #";
			}
		}

		if ($this->isFirstRowHeaders() && ! empty($rows)) {
			$header = array($rows[0]);
			$writer->writeRecords($header);
			unset($rows[0]);
		}

		if (! empty($rows)) {
			usort($rows, array($this, 'sortfunc'));

			$writer->writeRecords($rows);
		}

		return true;
	}

	/**
	 * sortfunc
	 *
	 * @param array $a
	 * @param array $b
	 * @return number
	 */
	public function sortfunc($a, $b)
	{
		foreach ($this->sorts as $sort) {
			$aval = $a[$sort['colnum']];
			if ($sort['type'] == 'enum') {
				if (isset($sort['enum'][$aval])) $aval = $sort['enum'][$aval];
				else $aval = 0;
			}

			$bval = $b[$sort['colnum']];
			if ($sort['type'] == 'enum') {
				if (isset($sort['enum'][$bval])) $bval = $sort['enum'][$bval];
				else $bval = 0;
			}

			if ($sort['type'] != 'alpha') {
				$aval = (float)$aval;
				$bval = (float)$bval;
			}

			if ($sort['dir'] == 'asc') {
				if ($aval < $bval) return -1;
				if ($aval > $bval) return 1;
			} else {
				if ($aval > $bval) return -1;
				if ($aval < $bval) return 1;
			}
		}

		return 0;
	}
}