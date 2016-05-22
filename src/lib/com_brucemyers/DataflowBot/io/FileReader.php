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

namespace com_brucemyers\DataflowBot\io;

class FileReader implements FlowReader
{
	protected $hndl;

	public function __construct($hndl)
	{
		$this->hndl = $hndl;
	}

	/**
	 * Read records
	 *
	 * @param int $max_records Maximum number of records to read at once (default = 100), -1 = all
	 * @return mixed false = end of records, array = records (arrays of fields)
	 */
	function readRecords($max_records = 100)
	{
		$delims = array("\v", "\n");
		$escaped = array("#%*vt@!~", "#%*nl@!~");

		$count = 0;
		$records = array();

		while (! feof($this->hndl)) {
			$buffer = fgets($this->hndl);
			if (empty($buffer)) break;
			$buffer = rtrim($buffer, "\n");

			$record = explode("\v", $buffer);
			foreach ($record as $key => $field) {
				$record[$key] = str_replace($escaped, $delims, $field);
			}

			$records[] = $record;

			++$count;
			if ($count == $max_records) break;
		}

		if (empty($records)) return false;
		return $records;
	}
}