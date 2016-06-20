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

namespace com_brucemyers\test\DataflowBot;

class MockCurl
{
	static public $lastError = '';
	static public $rows = array(
	    array('column 1', 'column 2', 'column 3'),
		array('11', '12', '13'),
		array('21', '22', '23')
	);

	/**
	 * Get a urls contents.
	 *
	 * @param string $URL
	 * @return mixed false = error, string = contents
	 */
	static public function getUrlContents($URL)
	{
		self::$lastError = '';
		$data = '';

		foreach (self::$rows as $row) {
			$row = implode("\t", $row) . "\n";
			$data .= $row;
		}

		return $data;
	}
}