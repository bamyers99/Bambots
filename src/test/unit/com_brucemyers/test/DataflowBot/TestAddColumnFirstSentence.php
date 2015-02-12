<?php
/**
 Copyright 2015 Myers Enterprises II

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

namespace com_brucemyers\test\DataflowBot;

use UnitTestCase;
use com_brucemyers\DataflowBot\Transformers\AddColumnFirstSentence;
use com_brucemyers\DataflowBot\ServiceManager;
use Mock;

class TestAddColumnFirstSentence extends UnitTestCase
{
	static $data = array(
			array('Rank', 'Article'),
			array('1', 'Apple'),
			array('2', "Helen D'Arcy Stewart")
	);

    public function testFirstSentence()
    {
    	$serviceMgr = new ServiceManager();

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', self::$data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
        $rows = self::$data;
        $rows[0][] = 'Abstract';
        $rows[1][] = "The '''apple''' is a fruit in the [[Malus]] family.";
        $rows[2][] = "'''Helen D'Arcy Stewart''' (born 1934) is an [[artist]] who is {{convert|5|ft}} tall.";
        $flowWriter->expectAt(0, 'writeRecords', array($rows));
        $flowWriter->expectCallCount('writeRecords', 1);

        Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
        $mediaWiki = &new \MockMediaWiki();
        $mediaWiki->returnsAt(0, 'getPageWithCache', "[[File:apple.jpg|100px|[[Jonathan]] [[apple]]]]
        	{{hatnote|needs references}}
        	The '''apple''' is a fruit in the [[Malus]] family.
        	");

        $mediaWiki->returnsAt(1, 'getPageWithCache', "<!-- Afc -->
        	{{Infobox person
        	|name = Helen D'Arcy Stewart
        	|birthdate = {{birth date|1934|01|07}}
        	}}
        	'''Helen D'Arcy Stewart''' (born 1934) is an [[artist]]<ref>{{Cite web|url=}}</ref> who is {{convert|5|ft}} tall.
        	");

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
        $serviceMgr = &new \MockServiceManager();
        $serviceMgr->returns('getMediaWiki', $mediaWiki);

        $transformer = new AddColumnFirstSentence($serviceMgr);

    	$params = array(
    		'insertpos' => 'append',
    	    'lookupcol' => '2',
    		'title' => 'Abstract'
    	);

    	$result = $transformer->init($params, true);
    	$this->assertEqual($result, true, 'init failed');

    	$result = $transformer->isFirstRowHeaders();
    	$this->assertEqual($result, true, 'first row must be headers');

    	$result = $transformer->process($flowReader, $flowWriter);
    	$this->assertEqual($result, true, 'process failed');

    	$result = $transformer->terminate();
    	$this->assertEqual($result, true, 'terminate failed');
    }

    public function testSpecial()
    {
		$data = array(
				array('1', 'The SpongeBob Movie: Sponge Out of Water'),
				array('2', 'Kingsman: The Secret Service')
		);
    	$serviceMgr = new ServiceManager();

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowReader', 'MockFlowReader');
    	$flowReader = &new \MockFlowReader();
    	$flowReader->returns('readRecords', false);
    	$flowReader->returnsAt(0, 'readRecords', $data);

    	Mock::generate('com_brucemyers\\DataflowBot\\io\\FlowWriter', 'MockFlowWriter');
    	$flowWriter = &new \MockFlowWriter();
    	$rows = $data;
    	$rows[0][] = "'''''The SpongeBob Movie: Sponge Out of Water''''' is a 2015 American [[Films with live action and animation|animated/live action]] [[adventure]] [[comedy film]], based on the [[Nickelodeon]] television series ''[[SpongeBob SquarePants]]'', created by [[Stephen Hillenburg]].";
    	$rows[1][] = "'''''Kingsman: The Secret Service''''' is a 2015 [[Spy film|spy]] [[Action film#Subgenres|action comedy film]], directed by [[Matthew Vaughn]], and based on the [[comic book]] ''[[The Secret Service (comics)|The Secret Service]]'', created by [[Dave Gibbons]] and [[Mark Millar]].";
    	$flowWriter->expectAt(0, 'writeRecords', array($rows));
    	$flowWriter->expectCallCount('writeRecords', 1);

    	Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
    	$mediaWiki = &new \MockMediaWiki();

    	$pagetext = <<<EOT
{{Use mdy dates|date=December 2013}}
{{Infobox film
| name           = The SpongeBob Movie: Sponge Out of Water
| image          = SB-2 poster.jpg
| alt            =
| caption        = Theatrical release poster
| director       = {{Plainlist|
* [[Paul Tibbitt]]
* [[Mike Mitchell (director)|Mike Mitchell]]}}
| producer       = {{Plainlist|
* Paul Tibbitt
* [[Mary Parent]]}}
| story          = {{Plainlist|
* [[Stephen Hillenburg]]
* Paul Tibbitt}}
| screenplay     = [[Jonathan Aibel and Glenn Berger|Glenn Berger<br>Jonathan Aibel]]
| based on       = {{Based on|''[[SpongeBob SquarePants]]''|Stephen Hillenburg}}
| starring       = {{Plainlist|
* [[Antonio Banderas]]
* [[Tom Kenny]]
* [[Clancy Brown]]
* [[Rodger Bumpass]]
* [[Bill Fagerbakke]]
* [[Carolyn Lawrence]]
* [[Mr. Lawrence]]
* [[Matt Berry]]
}}
| music          = [[John Debney]]<ref name="johndebney">{{cite news|title=John Debney to Score Ivan Reitman's "Draft Day"|work=Film Music Reporter|url=http://filmmusicreporter.com/2013/11/25/john-debney-to-score-ivan-reitmans-draft-day-2/|accessdate=December 16, 2013|date=November 25, 2013}}</ref>
| cinematography = [[Phil Meheux]]
| editing        = [[David Ian Salter]]
| studio         = {{Plainlist|
* [[Paramount Animation]]
* [[Nickelodeon Movies]]
* [[United Plankton Pictures]]
}}
| distributor    = [[Paramount Pictures]]
| released       = {{Film date|2015|01|28|Belgium/Netherlands|2015|02|06|North America|ref2=<ref name="Feb6release">{{cite web|last1=Sneider|first1=Jeff|title=Paramount Avoids ''Fifty Shades'' by Moving Up ''Spongebob Squarepants'' Sequel|url=http://www.thewrap.com/paramount-avoids-fifty-shades-by-moving-up-spongebob-squarepants-sequel/|publisher=[[The Wrap]]|accessdate=June 7, 2014|date=June 5, 2014}}</ref>}}<!--Please do not add other nations here. Belgium and US only. See Template:Infobox film instructions.-->
| runtime        = 92 minutes<!--Theatrical runtime: 92:26--><ref>{{cite web | url=http://www.bbfc.co.uk/releases/spongebob-movie-sponge-out-water-film-0 | title=''THE SPONGEBOB MOVIE: SPONGE OUT OF WATER'' (U) | work=[[British Board of Film Classification]] | date=February 2, 2015 | accessdate=February 3, 2015}}</ref>
| country        = United States
| language       = English
| budget         = $74 million<ref name="BOM">{{cite web |url=http://www.boxofficemojo.com/movies/?id=spongebob2.htm |title=The SpongeBob Movie: Sponge Out of Water (2015) |website=Box Office Mojo |accessdate=February 8, 2015}}</ref><ref>{{cite web|url=http://variety.com/2015/film/news/spongebob-movie-box-office-jupiter-ascending-seventh-son-1201423635/|title=Box Office: ‘Spongebob’ to Top ‘Jupiter Ascending,’ ‘Seventh Son’ - Variety|work=Variety|accessdate=February 14, 2015}}</ref>
| gross          = $120.5 million<ref name="BOM" />
}}
'''''The SpongeBob Movie: Sponge Out of Water''''' is a 2015 American [[Films with live action and animation|animated/live action]] [[adventure]] [[comedy film]], based on the [[Nickelodeon]] television series ''[[SpongeBob SquarePants]]'', created by [[Stephen Hillenburg]]. Released in 2015, the film is a [[sequel]] to the 2004 animated film ''[[The SpongeBob SquarePants Movie]]''. It is directed by the show writer and executive producer [[Paul Tibbitt]], and written by Tibbitt, [[Jonathan Aibel and Glenn Berger]], and ''SpongeBob'' creator and executive producer Hillenburg. ''Sponge Out of Water'' is executive produced by Stephen Hillenburg, and co-executive produced by Cale Boyter, Nan Morales, and Craig Sost. The film stars the regular television cast ([[Tom Kenny]], [[Bill Fagerbakke]], [[Rodger Bumpass]], [[Clancy Brown]], [[Carolyn Lawrence]], and [[Mr. Lawrence]]), who returned to reprise their respective roles from the series and the previous film,<ref name="hollywoodreporter1"/> while [[Antonio Banderas]] plays a new live-action character, Burger-Beard the Pirate.<ref name=Banderas/><ref name=Slash1/> The film is produced by [[Paramount Animation]], [[Nickelodeon Movies]], and [[United Plankton Pictures]], and was distributed by [[Paramount Pictures]].
EOT;
    	$mediaWiki->returnsAt(0, 'getPageWithCache', $pagetext);

    	$pagetext = <<<EOT
{{Use British English|date=February 2015}}
{{Use dmy dates|date=January 2015}}
{{Infobox film
| name = Kingsman: The Secret Service
| image = Kingsman The Secret Service poster.jpg
| alt =
| caption = Theatrical release poster
| director = [[Matthew Vaughn]]
| producer = {{Plain list |
* Adam Bohling
* David Reid
* Matthew Vaughn
}}
| screenplay =  {{Plain list |
* [[Jane Goldman]]
* Matthew Vaughn
}}
| based on = {{Based on|''[[The Secret Service (comics)|The Secret Service]]''|[[Mark Millar]]<br />[[Dave Gibbons]]}}
| starring = {{Plain list | <!--- PER TRAILER ORDER --->
* [[Colin Firth]]
* [[Samuel L. Jackson]]
* [[Mark Strong]]
* [[Taron Egerton]]
* [[Michael Caine]]
}}
| music = {{Plain list|
* [[Henry Jackman]]
* Matthew Margeson
}}
| cinematography = George Richmond
| editing = {{Plain list|
* Eddie Hamilton
* [[Jon Harris (director)|Jon Harris]]
* [[Conrad Buff IV]]
}}
| studio = [[Marv Films]]
| distributor = [[20th Century Fox]]
| released = {{Film date|df=y|2014|12|13|[[Butt-Numb-A-Thon]]|2015|01|29|United Kingdom|2015|02|13|United States}}
| runtime = 129 minutes<!--Theatrical runtime: 128:34--><ref>{{cite web | url=http://www.bbfc.co.uk/releases/kingsman-secret-service-film | title=''Kingsman: The Secret Service'' (15) | work=[[British Board of Film Classification]] | date=19 December 2014 | accessdate=19 December 2014}}</ref>
| country = {{Plain list|
* United Kingdom
* United States<ref>{{cite web|title=Kingsman: The Secret Service (2015)|url=http://explore.bfi.org.uk/54b99c407f89b|publisher=[[British Film Institute]]|accessdate=17 January 2015}}</ref>
}}
| language = English
| budget = $81 million<ref name="BOM">{{cite web |url=http://www.boxofficemojo.com/movies/?id=secretservice.htm |title=Kingsman: The Secret Service (2015) |website=Box Office Mojo |accessdate=15 February 2015}}</ref>
| gross = $56.3 million<ref name="BOM" />
}}
'''''Kingsman: The Secret Service''''' is a 2015 [[Spy film|spy]] [[Action film#Subgenres|action comedy film]], directed by [[Matthew Vaughn]], and based on the [[comic book]] ''[[The Secret Service (comics)|The Secret Service]]'', created by [[Dave Gibbons]] and [[Mark Millar]]. The screenplay was written by Vaughn and [[Jane Goldman]]. The film stars [[Colin Firth]], [[Samuel L. Jackson]], [[Mark Strong]], [[Taron Egerton]], and [[Michael Caine]].
EOT;
    	$mediaWiki->returnsAt(1, 'getPageWithCache', $pagetext);

    	Mock::generate('com_brucemyers\\DataflowBot\\ServiceManager', 'MockServiceManager');
    	$serviceMgr = &new \MockServiceManager();
    	$serviceMgr->returns('getMediaWiki', $mediaWiki);

    	$transformer = new AddColumnFirstSentence($serviceMgr);

    	$params = array(
    			'insertpos' => 'append',
    			'lookupcol' => '2',
    			'title' => 'Abstract'
    	);

    	$result = $transformer->init($params, false);
    	$this->assertEqual($result, true, 'init failed');

    	$result = $transformer->isFirstRowHeaders();
    	$this->assertEqual($result, false, 'first row must not be headers');

    	$result = $transformer->process($flowReader, $flowWriter);
    	$this->assertEqual($result, true, 'process failed');

    	$result = $transformer->terminate();
    	$this->assertEqual($result, true, 'terminate failed');

    }
}
