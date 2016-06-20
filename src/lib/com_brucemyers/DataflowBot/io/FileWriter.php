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

class FileWriter implements FlowWriter
{
	protected $hndl;

	public function __construct($hndl)
	{
		$this->hndl = $hndl;
	}

	/**
	 * Write records
	 *
	 * @param array $records Records (arrays of fields) to write
	 */
	function writeRecords(&$records)
	{
		$delims = array("\v", "\n");
		$escaped = array("#%*vt@!~", "#%*nl@!~");

		foreach ($records as $record) {
			foreach ($record as $key => $field) {
				$record[$key] = str_replace($delims, $escaped, $field);
			}

			$data = implode("\v", $record);
			fwrite($this->hndl, $data);
			fwrite($this->hndl, "\n");
		}
	}
}