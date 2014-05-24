<?php
/**
 Copyright 2014 Myers Enterprises II

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

namespace com_brucemyers\Util;

class CSVString
{
	/**
     * Parse a csv line that has "ed fields.
     * " is escaped using \"
     *
     * @param $buffer string Buffer to parse
     * @return array Fields
     */
	static function parse(&$buffer)
	{
		$fields = array();
		$field = 0;
		$buflen = strlen($buffer);
		$fields[0] = '';

		for ($x = 0; $x < $buflen; ++$x) {
			$char = $buffer[$x];

			if ($char == '"') {
				$string = '';

				for (++$x; $x < $buflen; ++$x) {
					$char = $buffer[$x];

					if ($char == '\\') {
						$string .= $buffer[++$x];
					} elseif ($char == '"') {
						break;
					} else {
						$string .= $char;
					}
				}

				$fields[$field] = $string;
			} elseif ($char == ',') {
				++$field;
				$fields[$field] = ''; // So that last field always has a value
			} else {
				$fields[$field] .= $char;
			}
		}

		return $fields;
	}

	/**
	 * Format a csv line with "ed fields.
     * " is escaped using \"
	 *
	 * @param array $fields
	 * @return string csv data
	 */
	static function format($fields)
	{
		foreach ($fields as $key => $field) {
			$fields[$key] = str_replace('"', "\\\"", $field);
		}

		return '"' . implode('","', $fields) . '"';
	}
}