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

class MockCurlORES
{
	static public $lastError = '';
	static public $data = '{
  "scores": {
    "enwiki": {
      "wp10": {
        "scores": {
          "721239847": {
            "prediction": "Start",
            "probability": {
              "B": 0.41486750703606046,
              "C": 0.3950792346662636,
              "FA": 0.010657491239195961,
              "GA": 0.04659344236894418,
              "Start": 0.13097008620702175,
              "Stub": 0.0018322384825142177
            }
          },
          "721520870": {
            "prediction": "Stub",
            "probability": {
              "B": 0.1721344305840974,
              "C": 0.026526356824740183,
              "FA": 0.39995288355233344,
              "GA": 0.3924779366855128,
              "Start": 0.008908392353316804,
              "Stub": 0.0
            }
          }
        },
        "version": "0.3.1"
      }
    }
  }
}';
	static public $rows = array(
			array('Rank', 'Revision ID'),
			array('1', '721239847'),
			array('2', '721520870')
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
		$theurl = "https://ores.wmflabs.org/v2/scores/enwiki/wp10/?revids=721239847|721520870";

		if ($URL != $theurl) {
			self::$lastError = ' != ' . $theurl;
			return false;
		}

		return self::$data;
	}
}