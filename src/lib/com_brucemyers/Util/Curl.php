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

class Curl
{
	static public $lastError = '';

	/**
	 * Get a urls contents.
	 *
	 * @param string $URL
	 * @return mixed false = error, string = contents
	 */
	static public function getUrlContents($URL)
	{
		self::$lastError = '';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_USERAGENT, 'bambots');
		$contents = curl_exec($ch);
		if ($contents === false) self::$lastError = curl_error($ch);
		curl_close($ch);

		if ($contents) return $contents;
		return false;
	}
}