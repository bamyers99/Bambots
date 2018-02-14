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
//			$json = gzdecode($json); // gzdecode not available in < php 5.4
			$json = gzinflate(substr($json,10,-8));
		}

		$this->data = json_decode($json, true);
		if ($this->data === NULL) {
			echo $json;
			echo '<br />json_decode error = ' . json_last_error() . '<br />';
			$this->data = array();
		}

		// Normalization
		if (isset($this->data['params'])) {
			// Trim the param names
			$param_names = array_keys($this->data['params']);

			foreach ($param_names as $param_name) {
				$trimmed_name = trim($param_name);
				if ($trimmed_name != $param_name) { // don't use strict compare as numeric param type != trimmed string type
					$this->data['params'][$trimmed_name] = $this->data['params'][$param_name];
					unset($this->data['params'][$param_name]);
				}
			}

			foreach ($this->data['params'] as $name => $config) {
				if (isset($config['inherits'])) {
					$this->data['params'][$name] = array_merge($this->data['params'][$config['inherits']], $config);
					if (isset($this->data['params'][$name]['aliases'])) unset($this->data['params'][$name]['aliases']);
					$config = $this->data['params'][$name];
				}

				if (isset($config['deprecated']) && ($config['deprecated'] === false || empty($config['deprecated']))) {
					unset($this->data['params'][$name]['deprecated']);
					unset($config['deprecated']);
				}
				if (isset($config['required']) && ($config['required'] === false || empty($config['required']))) {
					unset($this->data['params'][$name]['required']);
					unset($config['required']);
				}
				if (isset($config['suggested']) && ($config['suggested'] === false || empty($config['suggested']))) {
					unset($this->data['params'][$name]['suggested']);
					unset($config['suggested']);
				}

				if (isset($config['aliases'])) {
					foreach ($config['aliases'] as $offset => $alias) {
						$this->data['params'][$name]['aliases'][$offset] = trim($alias);
					}
				}
			}
		}
	}

	public function enhanceConfig($configData)
	{
		foreach ($configData as $paramName => $config) {
			if (! isset($this->data['params'][$paramName])) continue;

			switch ($config['type']) {
				case 'yesno':
					$this->data['params'][$paramName]['type'] = 'yesno';
					break;

				case 'regex':
					if (isset($this->data['params'][$paramName]['regex'])) break;
					$this->data['params'][$paramName]['regex'] = $config['regex'];
					break;

				case 'values':
					if (isset($this->data['params'][$paramName]['values'])) break;
					$this->data['params'][$paramName]['values'] = $config['values'];
					break;

				case 'wikidata':
					if (isset($this->data['maps']['wikidata'][$config['wikidata']])) break;
					if (! isset($this->data['maps'])) $this->data['maps'] = array();
					if (! isset($this->data['maps']['wikidata'])) $this->data['maps']['wikidata'] = array();
					$this->data['maps']['wikidata'][$config['wikidata']] = $paramName;
					break;
			}
		}
	}

	public function getParams()
	{
		if (isset($this->data['params'])) return $this->data['params'];
		return array();
	}
}
