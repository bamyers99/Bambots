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

namespace com_brucemyers\test\Util;

use com_brucemyers\Util\TemplateParamParser;
use UnitTestCase;

class TestTemplateParamParser extends UnitTestCase
{

    public function testGetTemplates()
    {
    	// Test basic
    	$testname = 'Test basic';
    	$data = '{{Navbox|name=Retail|title=Retail stores}}';
    	$template_name = 'Navbox';
    	$params = array('name' => 'Retail', 'title' => 'Retail stores');
    	$expected_templates = array(array('name' => $template_name, 'params' => $params));
		$this->_performMultipleTemplateTest($testname, $data, $expected_templates);

		// Test no params
		$testname = 'Test no params';
		$data = '{{Navbox}}';
		$template_name = 'Navbox';
		$params = array();
   		$expected_templates = array(array('name' => $template_name, 'params' => $params));
		$this->_performMultipleTemplateTest($testname, $data, $expected_templates);

    	// Test complex
    	$testname = 'Test complex';
    	$data = '{{Template:navbox<!-- comment -->
    			| name	= Retail {{{year}}}
    			| title	=	[[Retail stores|Retail Stores]] {{resolve|{{{year}}}}}
    			| cost = <math>{x*2} | <i>5</i></math>
    			| {{{paramname}}} = {{{{{lefttmpl}}|{{righttmpl}}}}}
    			| brackets = {{{{{tmplname}}}|param={{{paramvalue}}}}}
    			}}
    			{{Navbox
    			| name=Retail {{{year}}}
    			| title= Second navbox
    			| table=
    			{|
    			|-
    			| a || b
    			|}
    			}}';
    	$expected_templates = array(
    		array('name' => 'Navbox',
    			'params' => array('name' => 'Retail {{{year}}}',
    				'title' => '[[Retail stores|Retail Stores]] {{resolve|year={{{year}}}}}',
    				'cost' => '<math>{x*2} | <i>5</i></math>',
    				'{{{paramname}}}' => '{{{{{lefttmpl}}|{{righttmpl}}}}}',
    				'brackets' => '{{{{{tmplname}}}|param={{{paramvalue}}}}}')),
    		array('name' => 'Resolve', 'params' => array('1' => '{{{year}}}')),
    		array('name' => 'Lefttmpl', 'params' => array()),
    		array('name' => 'Righttmpl', 'params' => array()),
    		array('name' => '{{{tmplname}}}', 'params' => array('param' => '{{{paramvalue}}}')),
    		array('name' => 'Navbox', 'params' => array('name' => 'Retail {{{year}}}', 'title' => 'Second navbox', 'table' =>
    			'{|
    			|-
    			| a || b
    			|}'))
    	);
		$this->_performMultipleTemplateTest($testname, $data, $expected_templates);

		$data = <<<EOT

EOT;
		$expected_templates = array();
		$this->_performMultipleTemplateTest('Infinite', $data, $expected_templates);
    }

    function _performMultipleTemplateTest($testname, &$data, &$expected_templates)
    {
    	$templates = TemplateParamParser::getTemplates($data);
    	echo "\n$testname\n";
    	print_r($templates);

    	$this->assertEqual(count($templates), count($expected_templates), "$testname - Template count error");

    	foreach ($expected_templates as $expected_template) {
    		$found = false;

    		// See if one of the parsed templates matches
    		foreach ($templates as $template) {
				if ($template['name'] != $expected_template['name']) continue;
				if (count($expected_template['params']) != count($template['params'])) continue;

				foreach ($expected_template['params'] as $key => $value) {
					if (! isset($template['params'][$key])) continue;
					if ($template['params'][$key] != $value) continue;
				}

				$found = true;
				break;
    		}

    		$this->assertTrue($found, "$testname - No template match found");
    		if (! $found) print_r($expected_template);
    	}
    }
}