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

use com_brucemyers\TemplateParamBot\TemplateParamBot;
use com_brucemyers\TemplateParamBot\ServiceManager;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Config;
use UnitTestCase;

class TestProcessXMLDump extends UnitTestCase
{

	public function testLoadTotalsOffsets()
	{
		$datadir = FileCache::getCacheDir();
		$totalsfilepath = $datadir . DIRECTORY_SEPARATOR . 'enwiki-20160113-TemplateTotals';
		$offsetsfilepath = $datadir . DIRECTORY_SEPARATOR . 'enwiki-20160113-TemplateOffsets';
		$this->_createTotalsOffsetsFiles($totalsfilepath, $offsetsfilepath);

		$serviceMgr = new ServiceManager();
		$dbh_wiki = $serviceMgr->getDBConnection('enwiki');
		$dbh_tools = $serviceMgr->getDBConnection('tools');
		new CreateTables($dbh_wiki, $dbh_tools);

		$ruleconfigs = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'templateNS' => 'Template', 'lang' => 'en'));

		$templBot = new TemplateParamBot($ruleconfigs);

		$errmsg = $templBot->loadTotalsOffsets($totalsfilepath, $offsetsfilepath);
		if (! empty($errmsg)) echo "$errmsg\n";

		$this->assertEqual($errmsg, '', 'processParamDump error');
	}

	protected function _createTotalsOffsetsFiles($totalsfilepath, $offsetsfilepath)
	{
		$text = <<<EOT
T3382507	12	13
Pbirth_date	13	{{Birth date         |1976         |12         |11}}	1	{{Birth date|1976|12|10}}	2	{{Birth date|1976|12|12|df=y}}	1	{{Birth date|1976|12|1}}	1	{{Birth date|1976|12|2}}	1	{{Birth date|1976|12|3}}	1	{{Birth date|1976|12|4}}	1	{{Birth date|1976|12|5}}	1	{{Birth date|1976|12|6}}	1	{{Birth date|1976|12|7}}	1	{{Birth date|1976|12|8}}	1	{{Birth date|1976|12|9}}	1
Phonorific	12	Dr	2	Miss	1	Mr	8	Mrs	1
Ptitle	13	Person 101	1	Person 102	1	Person 103	1	Person 104	1	Person 105	1	Person 106	1	Person 107	1	Person 108	1	Person 109	1	Person 110a	1	Person 110b	1	Person 111	1	Person 112	1
T6594285	12	13
P1	13	1976	13
P2	13	12	13
P3	13	1	1	10	2	11	1	12	1	2	1	3	1	4	1	5	1	6	1	7	1	8	1	9	1
Pdf	1	y	1
EOT;

		file_put_contents($totalsfilepath, $text);

		$text = <<<EOT
3382507	0
6594285	1042
EOT;

		file_put_contents($offsetsfilepath, $text);
	}

	public function testLoadInstances()
	{
		$datadir = Config::get(TemplateParamBot::DATADIR) . DIRECTORY_SEPARATOR . 'TemplateParamBot';
	    if (! is_dir($datadir)) {
        	mkdir($datadir);
        }
		$instancefilepath = $datadir . DIRECTORY_SEPARATOR . 'enwiki-20160113-TemplateParams';
		$this->_createInstanceFile($instancefilepath);

		$serviceMgr = new ServiceManager();
		$dbh_tools = $serviceMgr->getDBConnection('tools');
		$dbh_tools->exec("INSERT INTO loads VALUES ('enwiki',3382507,'S','','2000-01-01','00:00:00')");

		$ruleconfigs = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'templateNS' => 'Template', 'lang' => 'en'));

		$templBot = new TemplateParamBot($ruleconfigs);

		$errmsg = $templBot->processLoads();
		if (! empty($errmsg)) echo "$errmsg\n";

		$this->assertEqual($errmsg, '', 'processParamDump error');
	}

	protected function _createInstanceFile($instancefilepath)
	{
		$text = <<<EOT
3382507	101	birth_date	{{Birth date|1976|12|1}}	honorific	Mr	title	Person 101
3382507	102	birth_date	{{Birth date|1976|12|2}}	honorific	Dr	title	Person 102
3382507	103	birth_date	{{Birth date|1976|12|3}}	honorific	Mrs	title	Person 103
3382507	104	birth_date	{{Birth date|1976|12|4}}	honorific	Miss	title	Person 104
3382507	105	birth_date	{{Birth date|1976|12|5}}	honorific	Mr	title	Person 105
3382507	106	birth_date	{{Birth date|1976|12|6}}	honorific	Mr	title	Person 106
3382507	107	birth_date	{{Birth date|1976|12|7}}	honorific	Mr	title	Person 107
3382507	108	birth_date	{{Birth date|1976|12|8}}	honorific	Mr	title	Person 108
3382507	109	birth_date	{{Birth date|1976|12|9}}	honorific	Mr	title	Person 109
3382507	110	birth_date	{{Birth date|1976|12|10}}	honorific	Mr	title	Person 110a
3382507	110	birth_date	{{Birth date|1976|12|10}}	honorific	Dr	title	Person 110b
3382507	111	birth_date	{{Birth date         |1976         |12         |11}}	title	Person 111
3382507	112	birth_date	{{Birth date|1976|12|12|df=y}}	honorific	Mr	title	Person 112
6594285	101	1	1976	2	12	3	1
6594285	102	1	1976	2	12	3	2
6594285	103	1	1976	2	12	3	3
6594285	104	1	1976	2	12	3	4
6594285	105	1	1976	2	12	3	5
6594285	106	1	1976	2	12	3	6
6594285	107	1	1976	2	12	3	7
6594285	108	1	1976	2	12	3	8
6594285	109	1	1976	2	12	3	9
6594285	110	1	1976	2	12	3	10
6594285	110	1	1976	2	12	3	10
6594285	111	1	1976	2	12	3	11
6594285	112	1	1976	2	12	3	12	df	y
EOT;

		file_put_contents($instancefilepath, $text);
	}

	public function notestProcessParamDump()
	{
		$datadir = FileCache::getCacheDir();
		$infilepath = $datadir . DIRECTORY_SEPARATOR . 'enwiki-20160113-TemplateParams.bz2';
		$this->_createParamDumpFile($infilepath);

		$serviceMgr = new ServiceManager();
		$dbh_wiki = $serviceMgr->getDBConnection('enwiki');
		$dbh_tools = $serviceMgr->getDBConnection('tools');
		new CreateTables($dbh_wiki, $dbh_tools);

		$ruleconfigs = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'templateNS' => 'Template', 'lang' => 'en'));

		$templBot = new TemplateParamBot($ruleconfigs);

		$errmsg = $templBot->processParamDump($infilepath, $datadir);
		if (! empty($errmsg)) echo "$errmsg\n";

		$this->assertEqual($errmsg, '', 'processParamDump error');
	}

	protected function _createParamDumpFile($filepath)
	{
		$text = <<<EOT
P101	699432101
T3	1	1976	2	12	3	1
T2	birth_date	{{Birth date|1976|12|1}}	honorific	Mr	title	Person 101
P102	699432102
T3	1	1976	2	12	3	2
T2	birth_date	{{Birth date|1976|12|2}}	honorific	Dr	title	Person 102
P103	699432103
T3	1	1976	2	12	3	3
T2	birth_date	{{Birth date|1976|12|3}}	honorific	Mrs	title	Person 103
P104	699432104
T3	1	1976	2	12	3	4
T2	birth_date	{{Birth date|1976|12|4}}	honorific	Miss	title	Person 104
P105	699432105
T3	1	1976	2	12	3	5
T2	birth_date	{{Birth date|1976|12|5}}	honorific	Mr	title	Person 105
P106	699432106
T3	1	1976	2	12	3	6
T2	birth_date	{{Birth date|1976|12|6}}	honorific	Mr	title	Person 106
P107	699432107
T3	1	1976	2	12	3	7
T2	birth_date	{{Birth date|1976|12|7}}	honorific	Mr	title	Person 107
P108	699432108
T3	1	1976	2	12	3	8
T2	birth_date	{{Birth date|1976|12|8}}	honorific	Mr	title	Person 108
P109	699432109
T3	1	1976	2	12	3	9
T2	birth_date	{{Birth date|1976|12|9}}	honorific	Mr	title	Person 109
P110	699432110
T3	1	1976	2	12	3	10
T3	1	1976	2	12	3	10
T2	birth_date	{{Birth date|1976|12|10}}	honorific	Mr	title	Person 110a
T2	birth_date	{{Birth date|1976|12|10}}	honorific	Dr	title	Person 110b
P111	699432111
T3	1	1976	2	12	3	11
T2	birth_date	{{Birth date|1976|12|11}}	title	Person 111
P112	699432112
T3	1	1976	2	12	3	12	df	y
T2	birth_date	{{Birth date|1976|12|12|df=y}}	honorific	Mr	title	Person 112
EOT;

		$text = str_replace("\t", "\v", $text);

		$bz = bzopen($filepath, 'w');
		bzwrite($bz, $text);
		bzclose($bz);
	}

    public function notestProcessXMLDump()
    {
    	$datadir = FileCache::getCacheDir();
    	$infilepath = $datadir . DIRECTORY_SEPARATOR . 'enwiki-20160113-pages-articles.xml.bz2';
    	$this->_createXMLDumpFile($infilepath);

    	$serviceMgr = new ServiceManager();
    	$dbh_wiki = $serviceMgr->getDBConnection('enwiki');
    	$dbh_tools = $serviceMgr->getDBConnection('tools');
    	new CreateTables($dbh_wiki, $dbh_tools);

    	$ruleconfigs = array('enwiki' => array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'templateNS' => 'Template', 'lang' => 'en'));

    	$templBot = new TemplateParamBot($ruleconfigs);

    	$errmsg = $templBot->processXMLDump($infilepath, $datadir);
    	if (! empty($errmsg)) echo "$errmsg\n";

    	$this->assertEqual($errmsg, '', 'processXMLDump error');
    }

    protected function _createXMLDumpFile($filepath)
    {
    	$text = <<<EOT
<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.10/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.10/ http://www.mediawiki.org/xml/export-0.10.xsd" version="0.10" xml:lang="en">
  <page>
    <title>Infobox Person</title>
    <ns>10</ns>
    <id>1</id>
    <redirect title="Template:Infobox person" />
    <revision>
      <id>627604809</id>
      <text xml:space="preserve">#REDIRECT [[Template:Infobox person]] {{R from CamelCase}}</text>
    </revision>
  </page>
  <page>
    <title>Infobox person</title>
    <ns>10</ns>
    <id>2</id>
    <revision>
      <id>699432347</id>
      <text xml:space="preserve">title={{{title}}}|birth_date={{Birth date|{{{1}}}|{{{2}}}|{{{3}}}}}</text>
    </revision>
  </page>
  <page>
    <title>Birth date</title>
    <ns>10</ns>
    <id>3</id>
    <revision>
      <id>699432547</id>
      <text xml:space="preserve">year={{{0}}}</text>
    </revision>
  </page>
  <page>
    <title>Person 101</title>
    <ns>0</ns>
    <id>101</id>
    <revision>
      <id>699432101</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 101|birth_date={{Birth date|1976|12|1}}|honorific=Mr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 102</title>
    <ns>0</ns>
    <id>102</id>
    <revision>
      <id>699432102</id>
      <text xml:space="preserve">{{Infobox person|title=Person 102|birth_date={{Birth date|1976|12|2}}|honorific=Dr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 103</title>
    <ns>0</ns>
    <id>103</id>
    <revision>
      <id>699432103</id>
      <text xml:space="preserve">{{Infobox Person|title=Person 103|birth_date={{Birth date|1976|12|3}}|honorific=Mrs}}</text>
    </revision>
  </page>
  <page>
    <title>Person 104</title>
    <ns>0</ns>
    <id>104</id>
    <revision>
      <id>699432104</id>
      <text xml:space="preserve">{{Infobox_Person|title=Person 104|birth_date={{Birth date|1976|12|4}}|honorific=Miss}}</text>
    </revision>
  </page>
  <page>
    <title>Person 105</title>
    <ns>0</ns>
    <id>105</id>
    <revision>
      <id>699432105</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 105|birth_date={{Birth date|1976|12|5}}|honorific=Mr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 106</title>
    <ns>0</ns>
    <id>106</id>
    <revision>
      <id>699432106</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 106|birth_date={{Birth date|1976|12|6}}|honorific=Mr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 107</title>
    <ns>0</ns>
    <id>107</id>
    <revision>
      <id>699432107</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 107|birth_date={{Birth date|1976|12|7}}|honorific=Mr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 108</title>
    <ns>0</ns>
    <id>108</id>
    <revision>
      <id>699432108</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 108|birth_date={{Birth date|1976|12|8}}|honorific=Mr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 109</title>
    <ns>0</ns>
    <id>109</id>
    <revision>
      <id>699432109</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 109|birth_date={{Birth date|1976|12|9}}|honorific=Mr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 110</title>
    <ns>0</ns>
    <id>110</id>
    <revision>
      <id>699432110</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 110a|birth_date={{Birth date|1976|12|10}}|honorific=Mr}}
    			{{Infobox_person|title=Person 110b|birth_date={{Birth date|1976|12|10}}|honorific=Dr}}</text>
    </revision>
  </page>
  <page>
    <title>Person 111</title>
    <ns>0</ns>
    <id>111</id>
    <revision>
      <id>699432111</id>
      <text xml:space="preserve">{{Infobox_person
    			|title=Person 111
    			|birth_date={{Birth date
    				|1976
    				|12
    				|11}}
    			|honorific=}}</text>
    </revision>
  </page>
  <page>
    <title>Person 112</title>
    <ns>0</ns>
    <id>112</id>
    <revision>
      <id>699432112</id>
      <text xml:space="preserve">{{Infobox_person|title=Person 112|birth_date={{Birth date|1976|12|12|df=y}}|honorific=Mr}}</text>
    </revision>
  </page>
</mediawiki>
EOT;

		$bz = bzopen($filepath, 'w');
		bzwrite($bz, $text);
		bzclose($bz);
    }
}