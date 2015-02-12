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
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\CleanupWorklistBot\CreateTables;
use com_brucemyers\DataflowBot\ComponentParameter;
use PDO;

abstract class AddColumn extends Transformer
{
	var $paramValues;

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
		    new ComponentParameter('title', ComponentParameter::PARAMETER_TYPE_STRING, 'Column title', '',
		    		array('size' => 10, 'maxlength' => 64)),
			new ComponentParameter('insertpos', ComponentParameter::PARAMETER_TYPE_STRING, 'Insert at column #',
		    	'Column numbers start at 1; append = append at the end of the row',
		    	array('size' => 5, 'maxlength' => 6))
		);
	}

	/**
	 * Insert a column into a row.
	 *
	 * @param array $row Data row
	 * @param string $value New column value
	 * @return mixed true = success, string = error message;
	 */
	public function insertColumn(&$row, $value)
	{
		$colnum = $this->paramValues['insertpos'];
		if ($colnum == 'append') {
			$row[] = $value;
			return true;
		}

		$colnum = (int)$colnum - 1;
		if ($colnum < 0 || $colnum >= count($row)) return 'Invalid insert column number';

		array_splice($row, $colnum, 0, $value);

		return true;
	}
}