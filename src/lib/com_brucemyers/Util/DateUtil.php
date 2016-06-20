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

namespace com_brucemyers\Util;

class DateUtil
{
	/**
	 * Return the ordinal suffix for a number.
	 *
	 * @param int $num
	 * @return string ordinal suffix
	 */
	static public function ordinal($num)
	{
		$num = (int)$num;
		static $ord = array('th','st','nd','rd','th','th','th','th','th','th');
		$mod100 = $num % 100;
		if ($mod100 > 10 && $mod100 < 14) return 'th';
		return $ord[$num % 10];
	}
}