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

use com_brucemyers\Util\TemplateParamParser;

class TemplateParamConfig
{
	protected $templates;

	public function __construct(ServiceManager $serviceMgr)
	{
		$wiki = $serviceMgr->getMediaWiki('en.wikipedia.org');
		$text = $wiki->getpage('User:Bamyers99/TemplateParametersTool');
		$templates = TemplateParamParser::getTemplates($text);

		foreach ($templates as $template) {
			if ($template['name'] != 'tlp') continue;
			if (empty($template['params'])) continue;
			$templname = $template['params']['1'];
			$this->templates[$templname] = array();

			foreach ($template['params'] as $key => $value) {
				if ($key == '1') continue;
				$this->templates[$templname] = array_merge($this->templates[$templname], $this->parseParam($value));
			}
		}
	}

	/**
	 * Parse a parameter config
	 *
	 * @param string $value
	 * @return array parameter name => array('type' => type, 'regex' => regex, 'values' => array(), 'wikidata' => propID)
	 *
	 * {{tlp|IMDb name|regex:id{{=}}<nowiki>(nm)?\d{7}</nowiki>|values:section{{=}}<nowiki>award;awards;bio;biography</nowiki>|wikidata:id{{=}}P345}}
	 */
	protected function parseParam($value)
	{
		list($type, $rest) = explode(':', $value, 2);

		if ($type == 'yesno') return array($rest => array('type' => $type));

		list($paramName, $rest) = explode('{{=}}', $rest, 2);
		$rest = str_replace(array('<nowiki>', '</nowiki>'), '', $rest);

		switch ($type) {
			case 'regex':
				return array($paramName => array('type' => $type, 'regex' => $rest));
				break;

			case 'wikidata':
				return array($paramName => array('type' => $type, 'wikidata' => $rest));
				break;

			case 'values':
				$values = explode(';', $rest);
				return array($paramName => array('type' => $type, 'values' => $values));
				break;
		}
	}

	public function getTemplate($name)
	{
		if (isset($this->templates[$name])) return $this->templates[$name];
		return array();
	}
}
