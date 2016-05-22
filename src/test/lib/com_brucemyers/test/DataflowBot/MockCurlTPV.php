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

class MockCurlTPV
{
	static public $lastError = '';
	static public $data = '{"items":[{"project":"en.wikipedia","access":"all-access","year":"2016","month":"05","day":"15","articles":[{"article":"Main_Page","views":19054328,"rank":1},{"article":"Special:Search","views":1657012,"rank":2},{"article":"Paris: France","views":55,"rank":3},{"article":"Rome","views":27,"rank":4}]}]}';
	static public $rows = array(
		array('Article', 'Views'),
		array('Paris: France', 55),
		array('Rome', 27)
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
		$theurl = "https://wikimedia.org/api/rest_v1/metrics/pageviews/top/en.wikipedia.org/all-access/" .
			date('Y/m/d', strtotime('-5 days'));

		if ($URL != $theurl) {
			self::$lastError = ' != ' . $theurl;
			return false;
		}

		return self::$data;
	}
}