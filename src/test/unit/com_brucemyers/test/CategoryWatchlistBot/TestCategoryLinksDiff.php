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

namespace com_brucemyers\test\CategoryWatchlistBot;

use com_brucemyers\CategoryWatchlistBot\CategoryLinksDiff;
use com_brucemyers\CategoryWatchlistBot\CategoryWatchlistBot;
use com_brucemyers\CategoryWatchlistBot\ServiceManager;
use com_brucemyers\test\CategoryWatchlistBot\CreateTables;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\MySQLDate;
use com_brucemyers\Util\CommonRegex;
use com_brucemyers\Util\TemplateParamParser;
use UnitTestCase;
use PDO;
use Mock;

class TestCategoryLinksDiff extends UnitTestCase
{

    public function notestDiffLoad()
    {
    	$outputdir = Config::get(CategoryWatchlistBot::OUTPUTDIR);
    	$outputdir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $outputdir);
    	$outputdir = preg_replace('!(/|\\\\)$!', '', $outputdir); // Drop trailing slash
    	$outputdir .= DIRECTORY_SEPARATOR;

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = &new \MockMediaWiki();

        $mediaWiki->returns('getRevisionsText', array(
        	'Talk:Mackinac Island' => array(1, 1, "<!-- [[Category:New pages]] -->
        		[[Category:Unassessed Michigan articles]]
        		[[category:NA-importance_Michigan_articles]]"),
        	'Lansing, Michigan' => array(0, 2, "{{WikiProject Michigan}}[[Category:Articles needing cleanup from May 2013]]",
        		3, "[[Category:Articles needing cleanup from May 2013]]"),
        	'Earth' => array(0, 4, "[[Category:Featured_articles]][[Category:Pages_with_DOIs_inactive_since_2013]]",
        		5, "[[Category:Featured articles]][[Category:Pages_with_DOIs_inactive_since_2013]][[Category:Articles_needing_cleanup_from_May_2013]]")
        ));

    	$serviceMgr = new ServiceManager();
    	$dbh_wiki = $serviceMgr->getDBConnection('enwiki');
    	$dbh_tools = $serviceMgr->getDBConnection('tools');

    	Mock::generate('com_brucemyers\\CategoryWatchlistBot\\ServiceManager', 'MockServiceManager');
    	$serviceMgr = &new \MockServiceManager();
    	$serviceMgr->returns('getMediaWiki', $mediaWiki);
    	$serviceMgr->returns('getDBConnection', $dbh_wiki, array('enwiki'));
    	$serviceMgr->returns('getDBConnection', $dbh_tools, array('tools'));

    	$asof_date = time();

    	new CreateTables($dbh_wiki, $dbh_tools);

    	$wikiname = 'enwiki';
    	$wikidata = array('title' => 'English Wikipedia', 'domain' => 'en.wikipedia.org', 'catNS' => 'Category', 'lang' => 'en');
    	$ts = MySQLDate::toMySQLDatetime($asof_date);

    	//Set up a query and querycats
    	$dbh_tools->exec("INSERT INTO querys VALUES (1,'enwiki','A','','$ts','$ts',0)");
    	$dbh_tools->exec("INSERT IGNORE INTO wikis VALUES ('ptwiki','Português Wikipedia','pt.wikipedia.org','pt')");

    	$catLinksDiff = new CategoryLinksDiff($serviceMgr, $outputdir, $asof_date);

    	$catLinksDiff->processWiki($wikiname, $wikidata);

    	// Check wikis table
        $sql = 'SELECT * FROM wikis';
    	$sth = $dbh_tools->query($sql);
    	if ($row = $sth->fetch()) {
    		$this->assertEqual($row['wikiname'], $wikiname, 'Bad wikiname');
    		$this->assertEqual($row['wikititle'], $wikidata['title'], 'Bad wikititle');
    		$this->assertEqual($row['wikidomain'], $wikidata['domain'], 'Bad wikidomain');
    		$this->assertEqual($row['lang'], $wikidata['lang'], 'Bad lang');
    	} else {
    		$this->fail('wikis table empty');
    	}

    	//Check enwiki_diffs table
    	$minuscnt = 0;
    	$pluscnt = 0;

        $sql = 'SELECT * FROM enwiki_diffs';
    	$sth = $dbh_tools->query($sql);

    	while ($row = $sth->fetch()) {
    		if ($row['plusminus'] == '-') ++$minuscnt;
    		elseif ($row['plusminus'] == '+') ++$pluscnt;
    		else $this->fail('Invalid plusminus = ' . $row['plusminus']);
    	}

    	$this->assertEqual($minuscnt, 1, 'Bad minus count');
    	$this->assertEqual($pluscnt, 3, 'Bad plus count');
    }

    public function testParseCategoriesTemplates()
    {
		$cats = array();
		$templates = array();
    	$text = <<<EOT
{{Taxobox
| name = Indo-Pacific sergeant
| image = Abudefduf vaigiensis 1.jpg
| regnum = [[Animalia]]
| phylum = [[Chordata]]
| classis = [[Actinopterygii]]
| ordo = [[Perciformes]]
| familia = [[Pomacentridae]]
| genus = ''[[Abudefduf]]''
| species = '''''A. vaigiensis'''''
| binomial = ''Abudefduf vaigiensis''
| binomial_authority = (Quoy and Gaimard, 1825)
}}
The '''Indo-Pacific sergeant '''(''Abudefduf vaigiensis'') may also be known as the '''Sergeant major''' although this name is usually reserved for the closely related species ''[[Abudefduf saxatilis]]''.
==Distribution==
[[Image:Abudefduf vaigiensis 2.JPG|right|thumb|By a reef with [[fire coral]] in [[Taba, Egypt]].]]
The '''Indo-Pacific sergeant''' is found in the [[Indo-Pacific]] including the [[Red Sea]].<ref name="fishbase">{{FishBase_species|genus=Abudefduf|species=vaigiensis|year=2007|month=May}}</ref>[[Indian Ocean]] populations are found in the [[Red Sea]], the [[Gulf of Aden]], [[Arabia]], the [[Persian Gulf]], the [[Arabian Sea]], the [[Maldives]], eastern [[Africa]], [[Madagascar]], [[Seychelles]], [[Sri Lanka]], the [[Andaman Sea]], [[Indonesia]], [[Malaysia]], and [[Australia]].<ref name="fishbase"/> Populations in the [[Pacific Ocean]] are found in the [[Gulf of Thailand]], [[Malaysia]], [[Indonesia]], the [[Philippines]], [[Taiwan]], [[Japan]], the [[Yellow Sea]], the [[Great Barrier Reef]] around [[Australia]], [[New Zealand]], and Pacific islands all the way to [[Hawaii]].<ref name="fishbase"/> They are also recently found in the [[Mediterranean Sea]].<ref name="book">Siliotti, A. (2002) ''fishes of the red sea'' Verona, Geodia ISBN 88-87177-42-2</ref>
==Description==
''Abudefduf vaigiensis'' are white bluish with a yellow top. They have a black spot around their dorsal fin. It has yellow eyes. The dorsal fin on this fish has 13 dorsal spines and 11 to 14 dorsal soft rays.<ref name="fishbase"/> The anal fin on the '''Indo-Pacific sergeant''' has 2 anal spines and 11 to 13 anal soft rays.<ref name="fishbase"/> Its maximum recorded size is {{convert|20|cm|in}}.<ref name="fishbase"/> Juveniles mature at {{convert|12|cm|in}}.<ref name="fishbase"/> Males turn more blue during spawning.<ref name="fishbase"/> Many people confuse this fish for ''[[Abudefduf saxatilis]]'', a closely related species found in the [[Atlantic Ocean]].<ref name="fishbase"/>
==Ecology==
===Diet===
They feed on [[zooplankton]], [[benthic]] [[algae]], and small [[invertebrates]].<ref name="book2">Lieske, E. and Myers, R.F. (2004) ''Coral reef guide; Red Sea'' London, HarperCollins ISBN 0-00-715986-2</ref>
===Habitat===
Adults live in [[coral reef]]s, [[tide pools]], and [[reef|rocky reef]]s.<ref name="fishbase"/> Larva of this species live in the [[open sea]].<ref name="fishbase"/> It is found in tropical and subtropical waters. Depth ranges of {{convert|1 to 15|m|ft}} are where people encounter this fish.<ref name="fishbase"/>
===Behavior===
These fish form large aggregations.<ref name="fishbase"/> In the aggregations, individuals either feed in the midwater or tend their nests.<ref name="fishbase"/>
==In the aquarium==
This fish is found in the aquarium trade.
==Hazards to humans==
There have been reports of [[ciguatera]] poisoning from this fish.<ref name="fishbase"/>
==References==<!-- Pacific Science (2007), vol. 61, no. 2:211–221 -->
==Life Cycle==
===Early life===
The larva hatch and drift out in to the pelagic zone.<ref name="fishbase"/> They drift in the waves and grow up until they go to a [[reef]].<ref name="fishbase"/>
===Breeding===
Males turn more bluish during spawning.<ref name="fishbase"/> They build nests on rocks or coral ledges.<ref name="fishbase"/> Then, females lay their eggs in the nests and the male fertilizes them.<ref name=eol>[http://eol.org/pages/208627/overview "Abudefduf vaigiensis"] [[Encyclopedia of Life]] Retrieved on December 21, 2014</ref> Males guard and aerate the eggs until they hatch.<ref name=eol/>
==References==
<references/>
[[Category:Abudefduf]]
[[Category:Pomacentridae]]
[[Category:Fish of the Red Sea]]
[[Category:Fish of the Indian Ocean]]
[[Category:Fish of Hawaii]]
[[Category:Fish of Palau]]
[[Category:Animals described in 1825]]

==Further Reading==
<ref>{{cite journal|last1=Maruska|first1=Karen|last2=Peyton|first2=Kimberly|title=Interspecific Spawning between a Recent Immigrant and an Endemic Damselfish (Pisces: Pomacentridae) in the Hawaiian Islands|journal=Pacific Science|date=April 2007|volume=61|issue=2|page=211-221|doi=10.2984/15346188|url=https://vpn.lib.ucdavis.edu/,DanaInfo=ucelinks.cdlib.org,Port=8888+sfx_local?url_ver=Z39.88-2004&url_ctx_fmt=info:ofi/fmt:kev:mtx:ctx&rft_val_fmt=info:ofi/fmt:kev:mtx:journal&rft.atitle=Interspecific%20spawning%20between%20a%20recent%20immigrant%20and%20an%20endemic%20damselfish%20%28Pisces%20%3A%20Pomacentridae%29%20in%20the%20Hawaiian%20Islands&rft.aufirst=Karen%20P%2E&rft.aulast=Maruska&rft.date=2007&rft.epage=221&rft.genre=article&rft.issn=0030-8870&rft.issue=2&rft.jtitle=Pacific%20Science&rft.pages=211-221&rft.spage=211&rft.volume=61&rfr_id=info:sid/www.isinet.com:WoK:BIOSIS&rft.au=Peyton%2C%20Kimberly%20A%2E&rft_id=info:doi/10%2E2984%2F1534-6188%282007%2961%5B211%3AISBARI%5D2%2E0%2ECO%3B2|accessdate=28 April 2015}}</ref>
EOT;
		error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
		ini_set("display_errors", 1);
    	// Strip comments, etc
    	$cleandata = preg_replace(CommonRegex::REFERENCESTUB_REGEX, '', $text); // Must be first
    	echo ($cleandata === null) ? "isNull\n" : "notNull\n";
    	echo array_flip(get_defined_constants(true)['pcre'])[preg_last_error()] . "\n";
    	$cleandata = preg_replace(array(CommonRegex::COMMENT_REGEX, CommonRegex::REFERENCE_REGEX, CommonRegex::NOWIKI_REGEX), '', $cleandata);
    	echo "'$cleandata'\n";

    	// Get the explicit categories

    	if (preg_match_all(CommonRegex::CATEGORY_REGEX, $cleandata, $matches)) {
    		foreach ($matches[1] as $cat) {
    			list($cat) = explode('|', $cat);
    			$cat = str_replace('_', ' ', ucfirst(trim($cat)));
    			$cats[$cat] = $cat; // Removes dups
    		}
    	}

    	// Get the templates

    	$templatedata = TemplateParamParser::getTemplates($cleandata);

    	foreach ($templatedata as $template) {
    		$templatename = $template['name'];
    		$templates[$templatename] = $templatename; // Removes dups
    	}

    	print_r($templates);
    	print_r($cats);
    }
}