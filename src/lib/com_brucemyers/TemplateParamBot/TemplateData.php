<?php
/**
 Copyright 2016 Myers Enterprises II

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

namespace com_brucemyers\TemplateParamBot;

class TemplateData
{
	protected $data;

	public function __construct($json)
	{
		if (strlen($json) > 1 && substr($json, 0, 2) === "\037\213" ) {
			$json = gzdecode($json);
		}

		$this->data = json_decode($json, true);
		if ($this->data === NULL) {
			echo $json;
			echo '<br />json_decode error = ' . json_last_error() . '<br />';
			$this->data = array();
		}

		// Normalization
		if (isset($this->data['params'])) {
			foreach ($this->data['params'] as $name => $config) {
				if (isset($config['deprecated']) && $config['deprecated'] === false) unset($this->data['params'][$name]['deprecated']);
				if (isset($config['required']) && $config['required'] === false) unset($this->data['params'][$name]['required']);
				if (isset($config['suggested']) && $config['suggested'] === false) unset($this->data['params'][$name]['suggested']);

				if (isset($config['aliases'])) {
					foreach ($config['aliases'] as $alias) {
						$this->data['params'][$alias] = $config;
					}
				}

				if (isset($config['inherits'])) {
					$this->data['params'][$name] = $this->data['params'][$config['inherits']];
				}
			}
		}
	}

	public function getParams()
	{
		if (isset($this->data['params'])) return $this->data['params'];
		return array();
	}
}
