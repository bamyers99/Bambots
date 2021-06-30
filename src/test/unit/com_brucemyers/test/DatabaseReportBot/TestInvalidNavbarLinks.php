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
use com_brucemyers\Util\TemplateParamParser;
use UnitTestCase;
use PDO;
use Mock;

DEFINE('ENWIKI_HOST', 'DatabaseReportBot.enwiki_host');
DEFINE('TOOLS_HOST', 'DatabaseReportBot.tools_host');
DEFINE('WIKIDATA_HOST', 'DatabaseReportBot.wikidata_host');

class TestInvalidNavbarLinks extends UnitTestCase
{

    public function testNavbox()
    {
    	$enwiki_host = Config::get(ENWIKI_HOST);
    	$user = Config::get(DatabaseReportBot::LABSDB_USERNAME);
    	$pass = Config::get(DatabaseReportBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p;charset=utf8", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$tools_host = Config::get(TOOLS_HOST);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__DatabaseReportBot;charset=utf8", $user, $pass);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$wikidata_host = Config::get(WIKIDATA_HOST);
    	$dbh_wikidata = new PDO("mysql:host=$wikidata_host;dbname=wikidatawiki_p;charset=utf8", $user, $pass);
    	$dbh_wikidata->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	$testdata = array('Template:NavboxNoNavbar' => '{{Navbox|name = badname|navbar = plain|title = test 1}}',
    		'Template:NavboxGoodName' => '{{Navbox|name = NavboxGoodName|title = test 2}}',
    		'Template:NavboxWithColumns' => '{{Navbox with columns|name = Template:NavboxWithColumns|title = test 3}}',
    		'Template:NavboxBadName' => '{{Navbox|name = Navboxbadname|title = test 4}}',
    		'Template:BS-headerBadName' => '{{BS-header|BS-header title|BS-headerbadname}}',
    		'Template:BS-mapNoTitle' => '{{BS-map|navbar=BS-mapBadTitle}}',
    		'Template:SidebarNavbarOff' => '{{Sidebar|name=SidebarBadName|navbar=off}}',
    		'Template:NavboxRedirectBad' => '{{Navbox redirect|name=NavboxRedirectbad|title = test 5}}',
    	    'Template:ModuleSidebarGoodName' => '{{#invoke:Sidebar|collapsible|name = ModuleSidebarGoodName|title = test 6}}',
    	    'Template:ModuleSidebarBadName' => '{{#invoke:sidebar|collapsible|name = ModuleSidebarbadname|title = test 7}}'
    	);

    	Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $wiki = new \MockMediaWiki();

        foreach ($testdata as $key => $value) {
        	$wiki->returns('getPageWithCache', $value, array($key));
        }

        $url = Config::get(RenderedWiki::WIKIRENDERURLKEY);
        $renderedwiki = new RenderedWiki($url);

    	new CreateTablesINL($dbh_enwiki);

    	$apis = array(
    			'dbh_wiki' => $dbh_enwiki,
    			'wiki_host' => $enwiki_host,
    			'dbh_tools' => $dbh_tools,
    			'tools_host' => $tools_host,
    			'dbh_wikidata' => $dbh_wikidata,
    			'data_host' => $wikidata_host,
    			'mediawiki' => $wiki,
    			'renderedwiki' => $renderedwiki,
    			'datawiki' => null,
    			'user' => $user,
    			'pass' => $pass
    	);

		$report = new InvalidNavbarLinks();
		$rows = $report->getRows($apis);
		$errors = $rows['groups']['{{tlp|Navbox|name&#61;}}'];

		$this->assertEqual(count($errors), 2, 'Wrong number of invalid Navbox links');

		$row = $errors[0];
		$this->assertEqual($row[0], '[[Template:NavboxBadName|NavboxBadName]]', 'Wrong Navbox template');
		$this->assertEqual($row[1], 'Navboxbadname', 'Wrong Navbox invalid name');

		$row = $errors[1];
		$this->assertEqual($row[0], '[[Template:NavboxRedirectBad|NavboxRedirectBad]]', 'Wrong Navbox template 2');
		$this->assertEqual($row[1], 'NavboxRedirectbad', 'Wrong Navbox invalid name 2');
		
		$errors = $rows['groups']['{{tlp|BS-header|2&#61;}}'];
		
		$this->assertEqual(count($errors), 1, 'Wrong number of invalid BS-header links');
		
		$row = $errors[0];
		$this->assertEqual($row[0], '[[Template:BS-headerBadName|BS-headerBadName]]', 'Wrong BS-header template');
		$this->assertEqual($row[1], 'BS-headerbadname', 'Wrong BS-header invalid name');
		
		$errors = $rows['groups']['{{tlp|Sidebar|name&#61;}}'];
		
		$this->assertEqual(count($errors), 1, 'Wrong number of invalid Sidebar links');
		
		$row = $errors[0];
		$this->assertEqual($row[0], '[[Template:ModuleSidebarBadName|ModuleSidebarBadName]]', 'Wrong Sidebar template');
		$this->assertEqual($row[1], 'ModuleSidebarbadname', 'Wrong Sidebar invalid name');
    }

    public function notestTemplate()
    {
        $data = $this->_getTemplateData();
        $parsed_templates = TemplateParamParser::getTemplates($data);
        print_r($parsed_templates);
    }

    public function _getTemplateData()
    {
        $data = <<<EOT
{{ sidebar with collapsible lists
| name       = Jewish cuisine
| topimage   = [[File:Sabich1.png|250 px]]
| pretitle   = Part of a series on
| title      = [[Jewish cuisine]]
| class      = hlist

| list1name  = Regional cuisines
| list1title = Regional cuisines
| list1      =
; [[Jewish cuisine|Worldwide]]
* [[American Jewish cuisine|American]]
* [[Ashkenazi Jewish cuisine|Ashkenazi]]
* [[Israeli cuisine|Israeli]]
* [[Sephardi Jewish cuisine|Sephardi]]
* [[Mizrahi Jewish cuisine|Mizrahi]]
; [[Jewish cuisine|Europe]]
* [[Ashkenazi Jewish cuisine|Ashkenazi]]
* [[Bulgarian Jewish cuisine|Bulgarian Jewish]]
* [[French Jewish cuisine|French]]
* [[German Jewish cuisine|German Jewish]]
* [[Greek Jewish cuisine|Greek Jewish]]
* [[Italkim cuisine|Italkim (Italian Jewish)]]
* [[Latvian Jewish cuisine|Latvian Jewish]]
* [[Lithuanian Jewish cuisine|Litvak]]
* [[Polish Jewish cuisine|Galician]]
* [[Romanian Jewish cuisine|Romanian Jewish]]
* [[Russian Jewish cuisine|Russian Jewish]]
; [[Cuisine of the Sephardic Jews|Maghreb]]
* [[Algerian Jewish cuisine|Algerian]]
* [[Djerban Jewish cuisine|Djerban]]
* [[Libyan Jewish cuisine|Libyan]]
* [[Moroccan Jewish cuisine|Moroccan]]
* [[Tunisian Jewish cuisine|Tunisian]]
* [[Tripolitan cuisine|Tripolitan]]
; [[Mizrahi cuisine|Middle East and Central Asia]]
* [[Bukharan Jewish cuisine|Bukharan]]
* [[Iraqi Jewish cuisine|Iraqi]]
* [[Israeli cuisine|Israeli]]
* [[Persian Jewish cuisine|Persian]]
* [[Syrian Jewish cuisine|Syrian Jewish]]
; [[Africa]]
* [[Egyptian Jewish cuisine|Egyptian]]
* [[Eritrean Jewish cuisine|Eritrean]]
* [[Ethiopian Jewish cuisine|Ethiopian]]
* [[South African Jewish cuisine|South African]]
* [[Ugandan Jewish cuisine|Ugandan]]
; [[The Americas]]
* [[American Jewish cuisine|American]]
* [[Argentinian Jewish cuisine|Argentinian]]
* [[Brazilian Jewish cuisine|Brazilian]]
* [[Canadian Jewish cuisine|Canadian]]
* [[Mexican Jewish cuisine|Mexican]]
* [[Peruvian Jewish cuisine|Peruvian]]
; [[Asia-Pacific]]
* [[Afghan Jewish cuisine|Afghan]]
* [[Australian Jewish cuisine|Australian]]
* [[Chinese cuisine in Jewish culture|Chinese]]
* [[Indian Jewish cuisine|Indian]]

| list2name = Ingredients
| list2title = Ingredients
| list2 =
;Vegetables
* [[Artichoke]]
* [[Bean]]
* [[Bell pepper]]
* [[Black eyed pea]]
* [[Cabbage]]
* [[Swiss chard|Chard]]
* [[Chickpea]]
* [[Eggplant]]
* [[Leek]]
* [[Lentil]]
* [[Pomegranate]]
* [[Potato]]
* [[Split pea]]
* [[Spinach]]
* [[Tomato]]
;Herbs & Spices
* [[Poppy seed]]
* [[Sumac]]

| list3name = Breads
| list3title = Breads
| list3 =

* [[Bagel]]
* [[Challah]]


| list4name  = Beverages
| list4title = Beverages
| list4      =

* [[Seltzer]]

| list5name  = Salads
| list5title = Salads
| list5      =
*[[Matbucha]]
*[[Tabbouleh]]

| list6name = Cheeses
| list6title = Cheeses
| list6 =

* [[Cottage cheese]]

| list7name  = Dishes
| list7title = Dishes
| list7      =


| list8name  =  appetizer
| list8title =  appetizers
| list8      =


| list9name  = Holidays and festivals
| list9title = Holidays and festivals
| list9      =

* [[Rosh Hashanah]]
* [[Yom Kippur|Break fast]]
* [[Sukkot]]
* [[Chanukah]]
* [[Tu Bishvat]]
* [[Purim]]
* [[Passover]]
* [[Lag b'omer]]
* [[Shavuot]]
* [[Yom Ha'atzmaut]]

| below      =
* {{nowrap|}}
* {{nowrap|{{portal-inline|Food|size=tiny}}}}

}}<noinclude>{{documentation}}<!-- place category and language links on the /doc sub-page, not here --></noinclude>
EOT;

        return $data;
    }
}