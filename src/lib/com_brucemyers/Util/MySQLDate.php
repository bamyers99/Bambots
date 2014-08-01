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

class MySQLDate
{
	/**
	 * Convert a MySQL date/time to PHP timestamp
	 *
	 * @param strint $value
	 * @return number Timestamp
	 */
	static public function toPHP($value)
	{
		return strtotime($value);
	}

	/**
	 * Convert a PHP timestamp to a MySQL date
	 *
	 * @param number $value Timestamp
	 * @return string date
	 */
	static public function toMySQLDate($value)
	{
		return date('Y-m-d', $value);
	}

	/**
	 * Convert a PHP timestamp to a MySQL datetime/timestamp
	 *
	 * @param number $value Timestamp
	 * @return string datetime/timestamp
	 */
	static public function toMySQLDatetime($value)
	{
		return date('Y-m-d H:i:s', $value);
	}
}