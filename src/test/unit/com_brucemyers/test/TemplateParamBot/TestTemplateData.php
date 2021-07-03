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

namespace com_brucemyers\test\TemplateParamBot;

use com_brucemyers\TemplateParamBot\TemplateData;
use com_brucemyers\TemplateParamBot\ServiceManager;
use UnitTestCase;

class TestTemplateData extends UnitTestCase
{

	public function testInitTemplateData()
	{
	    $serviceMgr = new ServiceManager();
	    $dbh_wiki = $serviceMgr->getDBConnection('enwiki');
	    $dbh_tools = $serviceMgr->getDBConnection('tools');
	    new CreateTables($dbh_wiki, $dbh_tools);
	    
	    $json = <<<END
{
	"description": "This template displays a wikilinked flag of the named parameter in 'icon' size, currently 23×15 pixels (defined in Template:Flagicon/core) plus a one-pixel border.",
	"params": {
		"1": {
			"label": "Name",
			"description": "Name of the country, region, city, etc.; full name is recommended for countries",
			"type": "string",
			"required": true
		},
		"variant": {
			"label": "Variant",
			"description": "Identifies a flag variant to be used instead of the standard flag, e.g. 1815",
			"type": "string",
			"required": false,
			"aliases": [
				" 2"
			]
		},
		" size": {
			"label": "Maximum dimension",
			"description": "The maximum width or height, specified via standard 'extended image syntax' (e.g. x30px)",
			"type": "string",
			"required": false
		}
	}
}
END;

		$tp = new TemplateData($json);

		$params = $tp->getParams();

		$param_names = array('1', 'variant', 'size');

		foreach ($param_names as $param_name) {
			$this->assertTrue(isset($params[$param_name]), 'missing param: ' . $param_name);
		}

		$this->assertEqual(count($params['variant']['aliases']), 1, 'must be one alias for "variant"');
		$this->assertTrue($params['variant']['aliases'][0] == '2', 'missing alias for "variant"');
	}
}