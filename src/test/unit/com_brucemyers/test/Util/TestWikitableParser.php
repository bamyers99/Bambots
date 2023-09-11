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

namespace com_brucemyers\test\Util;

use com_brucemyers\Util\WikitableParser;
use com_brucemyers\MediaWiki\WikidataWiki;
use UnitTestCase;

class TestWikitableParser extends UnitTestCase
{
    
    public function testComplexTable()
    {
        $wdwiki = new WikidataWiki();
        $config = $wdwiki->getPage('Wikidata:Database reports/Gadget usage statistics/Configuration');
        
        $configtable = WikitableParser::getTables($config)[0];
        print_r($configtable);
    }

    public function testGetTables()
    {
    	// Test basic
    	$testname = 'Test basic';
		$data = <<<EOT
{| class="wikitable"
|+ Caption
|-
! Header 1
! Header 2
! Header 3
|-
| row 1, cell 1
| row 1, cell 2
| row 1, cell 3
|-
| row 2, cell 1
| row 2, cell 2
| row 2, cell 3
|}
EOT;
    	$attribs = array('class' => 'wikitable', 'caption' => 'Caption');
		$headings = array('Header 1', 'Header 2', 'Header 3');
		$rows = array(
			array('row 1, cell 1', 'row 1, cell 2', 'row 1, cell 3'),
			array('row 2, cell 1', 'row 2, cell 2', 'row 2, cell 3')
		);

    	$expected_tables = array(array('attribs' => $attribs, 'headings' => $headings, 'rows' => $rows));
		$this->_performMultipleTableTest($testname, $data, $expected_tables);

    	// Test advanced
    	$testname = 'Test advanced';
		$data = <<<EOT
:{|
! scope="col" |Header 1 !! scope="col" |Header 2 !! scope="col" |Header 3
|-
| row 1, cell 1 || row 1, cell 2 || width="20px" | row 1, cell 3<!-- the first row -->
|- style="height: 100px;"
! scope="row" | row 2, cell 1
|
row 2, cell 2
| row 2, cell 3<nowiki>||</nowiki>
|-
|}
EOT;
    	$attribs = array();
		$headings = array('Header 1', 'Header 2', 'Header 3');
		$rows = array(
			array('row 1, cell 1', 'row 1, cell 2', 'row 1, cell 3'),
			array('row 2, cell 1', 'row 2, cell 2', 'row 2, cell 3<nowiki>||</nowiki>')
		);

    	$expected_tables = array(array('attribs' => $attribs, 'headings' => $headings, 'rows' => $rows));
		$this->_performMultipleTableTest($testname, $data, $expected_tables);

    	// Test nested table
    	$testname = 'Test nested table';
		$data = <<<EOT
{|
|
{|
|cell 1
|}
|}
EOT;
    	$attribs = array();
		$headings = array();
		$rows = array(
			array("{|\n|cell 1\n|}")
		);

    	$expected_tables = array(array('attribs' => $attribs, 'headings' => $headings, 'rows' => $rows));
		$this->_performMultipleTableTest($testname, $data, $expected_tables);

    	// Test 2 non-nested tables
    	$testname = 'Test 2 non-nested tables';
		$data = <<<EOT
{|
|+ Table 1
|Cell 1a
|}

{|
|+ Table 2
|Cell 1b
|}
EOT;
    	$attribs1 = array('caption' => 'Table 1');
		$headings1 = array();
		$rows1 = array(
			array('Cell 1a')
		);

		$attribs2 = array('caption' => 'Table 2');
		$headings2 = array();
		$rows2 = array(
				array('Cell 1b')
		);

    	$expected_tables = array(
    		array('attribs' => $attribs1, 'headings' => $headings1, 'rows' => $rows1),
    		array('attribs' => $attribs2, 'headings' => $headings2, 'rows' => $rows2)
    	);
		$this->_performMultipleTableTest($testname, $data, $expected_tables);
    }

    function _performMultipleTableTest($testname, &$data, &$expected_tables)
    {
    	$tables = WikitableParser::getTables($data);
    	echo "\n$testname\n";
    	print_r($tables);

    	$this->assertEqual(count($tables), count($expected_tables), "$testname - Table count error");

    	for ($x = 0; $x < count($expected_tables); ++$x) {
    		$this->assertIdentical($expected_tables[$x]['attribs'], $tables[$x]['attribs']);
    		$this->assertIdentical($expected_tables[$x]['headings'], $tables[$x]['headings']);
    		$this->assertIdentical($expected_tables[$x]['rows'], $tables[$x]['rows']);
    	}
    }
}