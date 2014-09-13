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

class Convert
{
	/**
	 * Convert unicode to ascii, ie. convert diacritics.
	 *
	 * @param string $s Unicode data
	 * @return string Ascii data
	 */
	public static function clearUTF($s)
	{
		setlocale(LC_ALL, 'en_US.UTF8');

		$r = '';
		$s1 = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
		$j = 0;
		for ($i = 0; $i < strlen($s1); $i++) {
			$ch1 = $s1[$i];
			$ch2 = @mb_substr($s, $j++, 1, 'UTF-8');
			if (strstr('`^~\'"', $ch1) !== false) {
				if ($ch1 <> $ch2) {
					--$j;
					continue;
				}
			}
			$r .= ($ch1=='?') ? $ch2 : $ch1;
		}

		return $r;
	}
}