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

namespace com_brucemyers\test\DatabaseReportBot;

use com_brucemyers\DatabaseReportBot\Reports\InvalidNavbarLinks;
use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\Util\Config;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\test\DatabaseReportBot\CreateTablesINL;
use com_brucemyers\RenderedWiki\RenderedWiki;
use UnitTestCase;
use PDO;
use Mock;

DEFINE('ENWIKI_HOST', 'DatabaseReportBot.enwiki_host');
DEFINE('TOOLS_HOST', 'DatabaseReportBot.tools_host');

class TestInvalidNavbarLinks extends UnitTestCase
{

    public function testNavbox()
    {
    	$enwiki_host = Config::get(ENWIKI_HOST);
    	$tools_host =
    	$user = Config::get(DatabaseReportBot::LABSDB_USERNAME);
    	$pass = Config::get(DatabaseReportBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$tools_host = Config::get(TOOLS_HOST);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__DatabaseReportBot", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	$testdata = array('Template:NavboxNoNavbar' => '{{Navbox|name = badname|navbar = plain|title = test 1}}',
    		'Template:NavboxGoodName' => '{{Navbox|name = NavboxGoodName|title = test 2}}',
    		'Template:NavboxWithColumns' => '{{Navbox with columns|name = Template:NavboxWithColumns|title = test 3}}',
    		'Template:NavboxBadName' => '{{Navbox|name = Navboxbadname|title = test 4}}',
    		'Template:BS-headerBadName' => '{{BS-header|BS-header title|BS-headerbadname}}',
    		'Template:BS-mapNoTitle' => '{{BS-map|navbar=BS-mapBadTitle}}',
    		'Template:SidebarNavbarOff' => '{{Sidebar|name=SidebarBadName|navbar=off}}');

    	Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $wiki = &new \MockMediaWiki();

        foreach ($testdata as $key => $value) {
        	$wiki->returns('getPageWithCache', $value, array($key));
        }

        $url = Config::get(RenderedWiki::WIKIRENDERURLKEY);
        $renderedwiki = new RenderedWiki($url);

    	new CreateTablesINL($dbh_enwiki);

		$report = new InvalidNavbarLinks();
		$rows = $report->getRows($dbh_enwiki, $dbh_tools, $wiki, $renderedwiki);
		$errors = $rows['groups']['{{tlxplain|Navbox|name&#61;}}'];

		$this->assertEqual(count($errors), 1, 'Wrong number of invalid Navbox links');

		$row = $errors[0];
		$this->assertEqual($row[0], '[[Template:NavboxBadName|NavboxBadName]]', 'Wrong Navbox template');
		$this->assertEqual($row[1], 'Navboxbadname', 'Wrong Navbox invalid name');

		$errors = $rows['groups']['{{tlxplain|BS-header|2&#61;}}'];

		$this->assertEqual(count($errors), 1, 'Wrong number of invalid BS-header links');

		$row = $errors[0];
		$this->assertEqual($row[0], '[[Template:BS-headerBadName|BS-headerBadName]]', 'Wrong BS-header template');
		$this->assertEqual($row[1], 'BS-headerbadname', 'Wrong BS-header invalid name');
    }
}