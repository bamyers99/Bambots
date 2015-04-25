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

    public function testDiffLoad()
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

    public function notestParseCategoriesTemplates()
    {
		$cats = array();
		$templates = array();
    	$text = <<<EOT
{{Infobox musical artist <!-- See Wikipedia:WikiProject_Musicians -->
| name                = Nicky Romero
| image               = Nicky Romero interviewed with VERY TV.JPG
| caption             = Romero interviewed with VERY TV.
| background          = non_vocal_instrumentalist
| birth_name          = Nick Rotteveel
| birth_date          = {{birth date and age|df=y|1989|1|6}}
| birth_place         = [[Amerongen]], [[Netherlands]]
| genre               = [[Electronic dance music|EDM]], [[Big room house|big room]], [[electro house]], [[progressive house]]
| occupation          = [[Music producer]], [[Disk Jockey|DJ]], [[composer]], [[remixer]], [[musician]]
| instrument          = [[Keyboard instrument|Keyboard]], [[DJ mix|mixer]], [[synthesizer]], [[Logic Pro]]
| years_active        = 2007–present
| label               = Protocol Recordings, [[Spinnin Records]], Cr2 Records, Musical Freedom, [[Fly Eye Records|Fly Eye]], Jack Back Records, Toolroom Records, [[Ultra Records]]
| associated_acts     = [[Nervo (duo)|Nervo]], [[Avicii]], [[Krewella]], [[Anouk (singer)|Anouk]], [[David Guetta]], [[Sia Furler|Sia]], [[Calvin Harris]]
| website             = {{URL|http://www.nickyromero.com/}}
| label site          = [http://www.protocolrecordings.com/ www.protocolrecordings.com]
| notable_instruments = M-Audio Keyboard <br/>A.N.A<br/>Logic Pro<br /> NI Kontakt & NI Massive<br/> Sylenth
}}
[[Image:Nicky Romero at Factory Cleanhoven.jpg|thumb|right|245px|Nicky Romero at Factory Cleanhoven]]
'''Nick Rotteveel''' (born 6 January 1989), better known by his stage name '''Nicky Romero''', is a Dutch DJ and music producer. He has worked with, and received support from DJs, such as [[Tiësto]], [[Fedde le Grand|Fedde Le Grand]], [[Sander van Doorn]], [[David Guetta]], [[Calvin Harris]], [[Armand Van Helden]], [[Avicii]] and [[Hardwell]].<ref name="musicradar1">{{cite web|author=Future Music|url=http://www.musicradar.com/news/tech/in-pictures-nicky-romero-and-his-suburban-studio-532921|title=In pictures: Nicky Romero and his suburban studio|publisher=MusicRadar|accessdate=14 November 2013}}</ref> He currently ranks #8 on [[DJ Magazine]]'s annual Top 100 DJs poll. He is known for his viral hit song "Toulouse", as well as for having a number one hit single in the UK with "[[I Could Be the One (Avicii and Nicky Romero song)|I Could Be the One]]" with [[Avicii]].

==Early life==
Nick Rotteveel van Gotum was born and raised in [[Amerongen]], the [[Netherlands]]. He moved to [[Kingston, Ontario]], [[Canada]] for a year, and later moved back to the Netherlands to continue his education where he did his final semesters in France.<ref>{{cite web|title=Info|url=http://www.nickyromero.nl/info|work=Nicky Romero|publisher=Nicky Romero|accessdate=19 August 2012|author=Nicky Romero|year=2012}}</ref> Nicky hit his first snare drum at the age of six and started playing drums in a fanfare. After playing a year or three, he was asked to play in a group who had acts on the streets and big events. At the age of twelve he got his first set of drums and after three years of playing drums every day and night, he swapped them for turntables.<ref name="Info">{{cite web|title=Info|url=http://www.ministryofsound.com/club/club-djs/300/nicky-romero/}}</ref>

==Career==
[[Image:NickyRomeroCZ.jpg|thumb|right|245px|Nicky Romero in Czech Republic]]
School was never his interest, so after graduating he worked as a bartender for a while and also started producing music. Eventually, he was signed to his first label Once Records, which went on to release the tracks "Privilege" and "Qwerty". Shortly after the track entitled "Funktion One" was released. From that moment on everything rapidly changed. Dutch DJ/producer Madskillz signed "Funktion One" to Azucar (from Madskillz and Gregor Salto) and later added "Hear My Sound". In 2009 he remixed Sidney Samson and Tony Cha Cha's track "Get On The Floor" and a bootleg for David Guetta entitled "When Love Takes Over". Both tracks got a lot of publicity where Ministry of Sound contacted him to do some remixes on their label. About a few hundred movies came up on YouTube from DJs who played the Nicky Romero bootleg When Love Takes Over. He also made a remix for the [[Dirty South (musician)|Dirty South]] track "Alamo".<ref name="Info"/>

In 2010 Nicky came up with a new track called "My Friend" (released on [[Spinnin' Records]]) which features a sample from the well known [[Groove Armada]] track [[My Friend (Groove Armada song)|of the same name]]. The combination between Groove Armada and the innovative Romero sound results in a monster track. "My Friend" has been played by Tiesto, Axwell, Fedde le Grand, Sander van Doorn and many more. This track reached #4 at Beatport worldwide overall chart, #1 at Dance-Tunes chart and several other famous DJ charts. 2011 saw Romero release plenty of remixes such as "Where Them Girls At" by David Guetta featuring Flo Rida and Nicki Minaj, "What A Feeling" by Alex Gaudino featuring Kelly Rowland, "Stronger" by Erick Morillo and Eddie Thoneick featuring Shawnee Taylor, "Where Is The Love" by Eddie Thoneick and "Rockin’ High" by Ben Liebrand.<ref name="Info"/>

===2012-2013: Mainstream success===
In 2012, Romero achieved popularity with the recording "Toulouse," which became a mainstay on the [[Beatport]] Top Ten for a significant period of time.<ref name="musicradar1"/> Recognizing his talent, [[MTV]] named him an [[electronic dance music|EDM]] artist to watch in 2012.<ref>{{cite web|last=Stewart |first=Adam|url=http://www.mtv.com/news/articles/1677064/edm-rookies-2012.jhtml|title=EDM Rookies To Watch In 2012|publisher=MTV.com|date=11 January 2012|accessdate=14 November 2013}}</ref> His popularity has risen in recent years, and he has attained a joint residency with [[David Guetta]] at party hot spot [[Ibiza]] for the summer of 2012.<ref>{{cite web|author=Martin Saavedra says|url=http://www.dancingastronaut.com/2012/03/an-afternoon-with-nicky-romero-and-mync-miami-2012-ibiza-residencies-and-more|title=An afternoon with Nicky Romero and MYNC: Miami 2012, Ibiza residencies, and more|publisher=Dancing Astronaut|date=22 March 2012|accessdate=14 November 2013}}</ref>

In October 2012, Nicky Romero received the [[DJ Magazine|DJ Mag]] 'Highest New Entry' award on DJ Mag's top 100 DJs fan poll, and with spot number 17, he is one of the highest new entries ever, together with [[Skrillex]] and [[Dash Berlin]].<ref>{{cite web|url=http://www.djmag.com/node/34707|title=Nicky Romero profile at|publisher=djmag.com|accessdate=14 November 2013}}</ref><ref>http://www.nickyromero.nl/2012/10/23/nicky-romero-reaches-17-on-dj-mag-top-100/</ref> In that same year, Romero collaborated with Swedish DJ/Producer, [[Avicii]], to produce the highly anticipated single "[[I Could Be the One (Avicii and Nicky Romero song)|I Could Be the One]]" which became a massive success across Europe, particularly in the [[United Kingdom]], where the single debuted at number one on the [[UK Singles Chart]] on 17 February 2013 ― for the week ending on 23 February 2013 ― becoming both Romero and [[Avicii]]'s first chart-topper in the UK.<ref>{{cite web|url=http://www.officialcharts.com/chart-news/avicii-and-nicky-romero-scoop-first-uk-number-1-single-1863|title=Avicii and Nicky Romero scoop first UK Number 1 single |publisher=Officialcharts.com|date=17 February 2013|accessdate=14 November 2013}}</ref> On the UK Dance Chart, "[[I Could Be the One (Avicii and Nicky Romero song)|I Could Be the One]]" debuted at number one ahead of [[Baauer]]'s "[[Harlem Shake (song)|Harlem Shake]]", which entered at number two.

Following "[[I Could Be the One (Avicii and Nicky Romero song)|I Could Be the One]]" in  2013, Romero released his long-awaited single, "Symphonica", that reached #1 on the overall [[Beatport]] Top 100 chart.<ref>{{cite web|title=ANOTHER #1 ON BEATPORT – NICKY ROMERO – SYMPHONICA|url=http://www.globalcontentprotection.com/blog/2013/04/another-1-on-beatport-nicky-romero-symphonica/|publisher=Global Content Protection|accessdate=29 November 2013}}</ref> His next release, a collaboration with [[Krewella]], "Legacy" also saw Beatport chart success hitting the #1 spot.<ref>{{cite web|title=Nicky Romero and Krewella take the #1 position with "Legacy"|url=http://news.beatport.com/blog/2013/07/19/nicky-romero-and-krewella-take-the-1-position-with-legacy/|publisher=Beatport News|accessdate=29 November 2013}}</ref> Romero then took on a collaboration with Sunnery James & Ryan Marciano, “S.O.T.U.”, that he released on [[Steve Angello]]'s Size Records.<ref>{{cite web|title=SOUNDS OF THE UNDERGROUND|url=http://www.sizerecords.com/release/sounds-of-the-underground|accessdate=29 November 2013}}</ref> Amid his 2013 releases, he performed as a resident at The Light Las Vegas and he also played numerous major festivals including [[Ultra Music Festival]],<ref>{{cite web|title=Ultra Music Festival 2013 reveals phase two lineup: The Weeknd, Sleigh Bells, Skrillex|url=http://consequenceofsound.net/2013/01/ultra-music-festival-2013-reveals-phase-two-lineup-the-weeknd-sleigh-bells-skrillex/|publisher=Consequence of Sound|accessdate=29 November 2013}}</ref> [[Coachella Valley Music and Arts Festival|Coachella]],<ref>{{cite web|title=Coachella Lineup 2013: Stone Roses, Blur, Postal Service Reunion, and More|url=http://www.spin.com/articles/coachella-lineup-2013-stone-roses-blur-phoenix-red-hot-chili-peppers-postal-service/|publisher=SPIN|accessdate=29 November 2013}}</ref> [[Electric Daisy Carnival]] Las Vegas<ref>{{cite web|title=EDC 2013 REVEALS ITS LINEUP!|url=https://www.lasvegasweekly.com/ae/music/2013/may/01/edc-2013-reveals-its-lineup/|publisher=Las Vegas Weekly|accessdate=29 November 2013}}</ref>  and Puerto Rico,<ref>{{cite web|title=Announcement: EDC Puerto Rico Lineup And Travel Packages|url=http://www.vibe.com/article/announcement-edc-puerto-rico-lineup-and-travel-packages|publisher=VIBE|accessdate=29 November 2013}}</ref> [[Sensation White]],<ref>{{cite web|title=SENSATION AMSTERDAM '13 LINE UP|url=http://www.sensation.com/netherlands/en/news/index/837/sensation-amsterdam-13-line-up|publisher=Sensation|accessdate=29 November 2013}}</ref> and [[Tomorrowland]], amongst others. One of his most notable gigs was at [[TomorrowWorld]] where he was one of the first music acts to present an interactive performance experience using [[Google Glass]].<ref>{{cite web|title=WATCH: NICKY ROMERO’S TOMORROWWORLD SET THROUGH HIS GOOGLE GLASSES|url=http://www.mixmag.net/video/blog/watch-nicky-romeros-google-glass-video-at-tomorrowworld|publisher=Mixmag|accessdate=29 November 2013}}</ref>

In October 2013, Romero ranked at number 7 on DJ Magazine's Top 100 DJs annual fan poll.<ref>{{cite web|title=Nicky Romero|url=http://www.djmag.com/content/nicky-romero|publisher=DJ Mag|accessdate=29 November 2013}}</ref> He helms his own weekly radio show, Protocol Radio and his own record label, Protocol Recordings.<ref>{{cite web|url=http://protocolrecordings.com|title=Protocol Recordings |publisher=Protocol Recordings|accessdate=14 November 2013}}</ref>

===2014-present: Upcoming debut studio album===
Romero keeps entering all lists with his music and label, and after many hours in his studio, he finally released a song called 'Feet On The Ground' with vocals by the Dutch singer Anouk. He is also working on his first studio album.

Songs from his upcoming Album :

* Nicky Romero & Anouk - "Feet On The Ground"
* Nicky Romero & Vicetone - "Let Me Feel" ft. When We Are Wild
* Nicky Romero vs. Volt & State - "Warriors"

Beside his music, he is up for different kinds of charity projects, as '10.000 Hours - People Planet Party'  which is made to help renovate playgrounds for disadvantaged kids.

''Legacy'' was used as the backing track by [[Australia]]n television network [[Channel 7 Australia|Channel Seven]] in on-air promotions for the 2014 season return of hit show ''[[Revenge (TV series)|Revenge]]''. After the advertisement's high rotation during the high rating [[2014 Australian Open]], the song charted in Australia at number 50. {{citation needed|date=February 2014}}

In August 2014, Nicky partnered with EDM lifestyle brand [http://electricfamily.com/ Electric Family] to produce a collaboration [http://electricfamily.com/collections/best-sellers/products/nicky-romero bracelet] for which 100% of the proceeds are donated to Fuck Cancer.

===Other productions===
In addition to all "Nicky Romero" singles, he also co-produced the track "[[Right Now (Rihanna song)|Right Now]]" (featuring [[David Guetta]]) from [[Rihanna]]'s seventh studio album ''[[Unapologetic]]'',<ref>{{cite web|url=http://www.nu.nl/muziek/2953928/nicky-romero-kreeg-kippenvel-van-rihanna.html|title=Nicky Romero kreeg kippenvel van Rihanna|publisher=Nu.nl|date=8 November 2012|accessdate=14 November 2013}}</ref> which was released on 19 November 2012. Before his performance at Tomorrowland,<ref>{{cite web|title=EDMTunes Submit Tune  Nicky Romero Shares TomorrowWorld Experience Through Google Glass|url=http://www.edmtunes.com/2013/09/nicky-romero-share-tomorrowworld-experience-google-glass}}</ref> he mentioned in an interview with MTV that he was also working on a new project with Rihanna.<ref>{{cite web|url=http://www.dancingastronaut.com/2013/08/nicky-romero-working-with-rihanna-on-right-now-follow-up|title=Nicky Romero working with Rihanna on ‘Right Now’ follow-up|publisher=Dancing Astronaut|date=2 August 2013|accessdate=14 November 2013}}</ref> He co-wrote and produced "It Should Be Easy" by [[Britney Spears]] for her eighth studio album ''[[Britney Jean]]''.

Romero also co-produced "Bang My Head" (featuring [[Sia_(musician)|Sia]]) and "No Money No Love" from [[David_Guetta|David Guettas]] 2014 album titled [[Listen_(David_Guetta_album)|Listen]].

==Protocol Recordings==
Romero is the founder and owner of Protocol Recordings. Protocol Recordings is a label company that helps new upcoming DJs and producers release their tracks with the guidance of Romero.

Romero also hosts a podcast show called Protocol Radio. It is an hour-long podcast released every Saturday, recorded by Romero himself, which features new tracks and a 'Weekly Top 3' from Protocol Recordings.

A new effort from the label is the 'Protocol Reboot' party, which is a concert performed only by Protocol-signed artists. The first one was in Miami during the Miami Music Week 2014. The second edition called "Protocol 305" is set to take place during Miami Music Week 2015 and will feature [[Deniz Koyu]], Ansolo ([[Ansel Elgort]]), Arno Cost and more.

;Protocol artists:
{{div col|2}}
*Aftershock
*[[Nervo (duo)|NERVO]]
*[[Blasterjaxx]]
*[[John Christian]]
*[[Vicetone]]
*Hill Cess
*Paris Blohm
*[[Tritonal]]
*[[Krewella]]
*[[Calvin Harris]]
*StadiumX
*[[John Dahlbäck]]
*Merk & Kremont
*[[Don Diablo]]
*Hard Rock Sofa
*[[R3hab]]
*Bassjackers
*Martin Volt & Quentin State
*Michael Calfan
*Kryder
*Lucky Date
*Tony Romera
*Pelari
*Vince Moogin
*ZROQ
*Amersy
*Skidka
*Vigel
*[[Bobina]]
*Sultan + Ned Shepard
*Lush & Simon
*Burgundy's
*Bobby Rock
{{div col end}}

==DJ Magazine's Top 100 DJs Rankings==
*2012: #17 (New Entry)
*2013: #7 (Up 10)
*2014: #8 (Down 1)

==Singles==

===As lead artist===
{| class="wikitable plainrowheaders" style="text-align:center;" border="1"
|+ List of singles as lead artist, with selected chart positions and certifications, showing year released and album name
! scope="col" rowspan="2" style="width:20em;"| Title
! scope="col" rowspan="2" style="width:1em;"| Year
! scope="col" colspan="10"| Peak chart positions
! scope="col" rowspan="2" style="width:14em;"| [[List of music recording certifications|Certifications]]
! scope="col" rowspan="2" style="width:18em;"| Album
|-
! scope="col" style="width:2.9em;font-size:90%;"| [[Sverigetopplistan|SWE]]
! scope="col" style="width:2.9em;font-size:90%;"| [[ARIA Charts|AUS]]
! scope="col" style="width:2.9em;font-size:90%;"| [[Ö3 Austria Top 40|AUT]]
! scope="col" style="width:2.9em;font-size:90%;"| [[Ultratop#Ultratop 50 Singles (Flemish chart)|BEL<br />(FL)]]
! scope="col" style="width:2.9em;font-size:90%;"| [[Syndicat National de l'Édition Phonographique|FRA]]
! scope="col" style="width:2.9em;font-size:90%;"| [[Media Control Charts|GER]]
! scope="col" style="width:2.9em;font-size:90%;"| [[MegaCharts|NLD]]
! scope="col" style="width:2.9em;font-size:90%;"| [[Swiss Hitparade|SWI]]
! scope="col" style="width:2.9em;font-size:90%;"| [[UK Albums Chart|UK]]
! scope="col" style="width:2.9em;font-size:90%;" | [[Billboard Hot 100|US]]
|-
! scope="row"| "Toulouse"
| rowspan="3"| 2012
| — || — || — || — || — || — || 92 || — || — || —
|
| rowspan="4"| Non-album singles
|-
! scope="row"| "Like Home"<br/><span style="font-size:90%;">(with [[Nervo (duo)|Nervo]])</span>
| 37 || 85 || — || 126 || — || — || — || — || 33 || —
|
|-
! scope="row"| "[[I Could Be the One (Avicii and Nicky Romero song)|I Could Be the One]]"<br /><span style="font-size:90%;">(vs. [[Avicii]])</span>
| 3 || 4 || 15 || 8 || 22 || 37 || 15 || 26 || 1 || 101
|
* ARIA: 3× Platinum<ref name="ARIA-sin-2013">{{cite web | url=http://www.aria.com.au/pages/httpwww.aria.com.aupagesaria-charts-accreditations-singles-2013.htm | title=ARIA Charts – Accreditations – 2013 Singles | publisher=[[Australian Recording Industry Association]] | accessdate=12 October 2013}}</ref>
* BEA: Gold<ref name="BEA-2013">{{cite certification | region=Belgium | certyear=2013 | accessdate=12 October 2013}}</ref>
* BPI: Gold
* RIAA: Gold
|-
! scope="row"| "[[Legacy (Nicky Romero song)|Legacy]]"<br/><span style="font-size:90%;">(vs. [[Krewella]])</span>
| 2013
| — || 44 || — || — || — || — || — || — || — || —
|
|-
! scope="row"| "Feet on the Ground"<br/><span style="font-size:90%;">(with [[Anouk (singer)|Anouk]])</span>
| 2014
| — || — || — || — || — || — || — || 80 || — || —
|
| rowspan="3"| TBA
|-
! scope="row"| "Let Me Feel"<br/><span style="font-size:90%;">(with Vicetone featuring When We Are Wild)</span>
| rowspan="2"| 2015
| — || — || — || — || — || — || — || — || — || —
|
|-
! scope="row"| "Warriors"<br/><span style="font-size:90%;">(vs. Volt & State)</span>
| — || — || — || — || — || — || — || — || — || —
|
|-
| colspan="14" style="font-size:90%"| "—" denotes a recording that did not chart or was not released in that territory.
|}

===As featured artist===
{| class="wikitable plainrowheaders" style="text-align:center;" border="1"
|+ List of singles as featured artist, with selected chart positions, showing year released and album name
! scope="col" rowspan="2" style="width:20em;"| Title
! scope="col" rowspan="2"| Year
! scope="col" colspan="4"| Peak chart positions
! scope="col" rowspan="2" style="width:18em;"| Album
|-
! scope="col" style="width:2.5em;font-size:90%;"| [[ARIA Charts|AUS]]
! scope="col" style="width:2.5em;font-size:90%;"| [[Ö3 Austria Top 40|AUT]]
! scope="col" style="width:2.5em;font-size:90%;"| [[GfK Entertainment|GER]]
! scope="col" style="width:2.5em;font-size:90%;"| [[UK Singles Chart|UK]]
|-
! scope="row"| "[[Wild Ones (song)|Wild One Two]]" <br /><span style="font-size:85%;">(Jack Back featuring [[David Guetta]], Nicky Romero and [[Sia Furler|Sia]])</span>
| 2012
| 52 || 43 || 65 || 171
| Non-album single
|-
| colspan="7" style="font-size:90%"| "—" denotes a recording that did not chart or was not released in that territory.
|}

===Promotional singles===
{| class="wikitable plainrowheaders" style="text-align:center;" border="1"
|+ List of promotional singles, with selected chart positions, showing year released and album name
! scope="col" rowspan="2" style="width:20em;"| Title
! scope="col" rowspan="2"| Year
! scope="col" colspan="1"| Peak chart positions
! scope="col" rowspan="2" style="width:18em;"| Album
|-
! scope="col" style="width:2.5em;font-size:90%;"| [[Syndicat National de l'Édition Phonographique|FRA]]
|-
! scope="row"| "Metropolis" <br /><span style="font-size:85%;">(with [[David Guetta]])</span>
| rowspan="2"| 2012
| 41
| ''[[Nothing but the Beat|Nothing but the Beat 2.0]]''
|-
! scope="row"| "[[Iron (Nicky Romero and Calvin Harris song)|Iron]]" <br /><span style="font-size:85%;">(with [[Calvin Harris]])</span>
| —
| ''[[18 Months]]''
|-
| colspan="4" style="font-size:90%"| "—" denotes a recording that did not chart or was not released in that territory.
|}

===Remixes===
;2008
* Prunk Le Funk - Chronology (Nicky Romero Remix)

;2009
* Mell Tierra and Sebastian D featuring Stanford - Maximize Nicky Romero Remix)
* Steff Da Campo vs. Ecoustic featuring Lady Rio - Freakybeatza (Nicky Romero & Praia Del Sol Remix)
* [[Sidney Samson]] and Tony Cha Cha - Get On The Floor (Nicky Romero Remix)
* DJ Jean - Play That Beat (Nicky Romero Mix)
* Pizetta featuring Reagadelica - Klezmer (Nicky Romero Remix)
* Quintino featuring Mitch Crown - Heaven (Nicky Romero Remix)
* Firebeatz and Apster - Skandelous (Nicky Romero Remix)
* DJ Rose - Twisted (Nicky Romero Remix)
* Quintin vs. DJ Jean - Original Dutch (Nicky Romero Remix)
* Michael Mendoza featuring I Fan - Be Without You (Nicky Romero Remix)
* [[David Guetta]] - [[When Love Takes Over]] (Nicky Romero Bootleg)
* DJ Jose - Like That (Nicky Romero Bigroom Remix)

;2010
* [[Ian Carey]] featuring Michelle Shellers - Keep On Rising (Nicky Romero Remix)
* [[Hardwell]] and Funkadelic - Get Down Girl (Nicky Romero Remix)
* Sol Noir - Superstring (Nicky Romero Remix)
* Sivana - Confusion (Nicky Romero Radio Edit)
* Grooveyard - Mary Go Wild (Nicky Romero Remix)
* Housequake - People Are People (Nicky Romero Remix)
* [[Fedde Le Grand]] featuring Mitch Crown - Rockin' High (Nicky Romero Remix)
* DJ Jesus Luz and Alexandra Prince - Dangerous (Nicky Romero Festival Mix)
* Ned Shepard - Chromatic (Nicky Romero & Nilson Remix)
* [[Green Velvet]] - Flash (Nicky Romero Remix)

;2011
* [[Taio Cruz]] - [[Dynamite (Taio Cruz song)|Dynamite]] (Nicky Romero Bootleg)
* [[Usher (entertainer)|Usher]] - [[More (Usher song)|More]] (Nicky Romero Bootleg)
* [[Daft Punk]] - Aerodynamic (Nicky Romero Bootleg)
* [[Flo Rida]] - [[Turn Around (5,4,3,2,1)]] (Nicky Romero Remix)
* [[David Guetta]] featuring Flo Rida and [[Nicki Minaj]] - [[Where Them Girls At]] (Nicky Romero Remix)
* [[Enrique Iglesias]] and [[Usher (entertainer)|Usher]] featuring [[Lil Wayne]] - [[Dirty Dancer]] (Nicky Romero Club Mix)
* [[Junkie XL]] - Molly's E (Nicky Romero Dub Remix)
* [[David Guetta]] featuring [[Usher (entertainer)|Usher]] - [[Without You (David Guetta song)|Without You]] (Nicky Romero Remix)
* [[Erick Morillo]] and Eddie Thoneick featuring Shawnee Taylor - Stronger (Nicky Romero Remix)
* [[David Guetta]] featuring [[Sia Furler|Sia]] - [[Titanium (song)|Titanium]] (Nicky Romero Remix)
* Tonite Only - Haters Gonna Hate (Nicky Romero 'Out Of Space' Remix)
* [[Kelly Clarkson]] - [[What Doesn't Kill You (Stronger)]] (Nicky Romero Radio Mix)

;2012
* [[Madonna (entertainer)|Madonna]] featuring [[Nicki Minaj]] and [[M.I.A. (artist)|M.I.A.]] - [[Give Me All Your Luvin']] (Nicky Romero Remix)
* [[Eva Simons]] - I Don't Like You (Nicky Romero Remix)
* Anakyn - Point Blank (Nicky Romero Edit)

;2013
* [[Ludacris]] featuring [[David Guetta]] and [[Usher (entertainer)|Usher]] - [[Rest of My Life (Ludacris song)|Rest Of My Life]] (Nicky Romero Remix)
* [[Calvin Harris]] featuring [[Ellie Goulding]] - I Need Your Love (Nicky Romero Remix)
* [[Zedd (producer)|Zedd]] featuring [[Hayley Williams]] - Stay The Night (Nicky Romero Remix)
* [[R3hab]] and Lucky Date - Rip It Up (Nicky Romero Edit)

;2014
* John Christian - Next Level (Nicky Romero Edit)

;2015
* [[One Direction]] - [[18 (One Direction song)|18]] (Nicky Romero Remix)

==References==
{{Reflist|2}}

==External links==
* [http://www.nickyromero.nl/ Official site]
* [http://www.protocolrecordings.com Label site]

{{Spinnin' Records}}

{{Persondata
| NAME              = Romero, Nicky
| ALTERNATIVE NAMES = Rotteveel, Nick
| SHORT DESCRIPTION = Dutch DJ
| DATE OF BIRTH     = 6 January 1988
| PLACE OF BIRTH    = Amerongen, Netherlands
| DATE OF DEATH     =
| PLACE OF DEATH    =
}}
{{DEFAULTSORT:Romero, Nicky}}
[[Category:Living people]]
[[Category:Dutch DJs]]
[[Category:1989 births]]
[[Category:People from Utrecht (province)]]
EOT;
		error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
		ini_set("display_errors", 1);
    	// Strip comments, etc
    	$cleandata = preg_replace(CommonRegex::REFERENCESTUB_REGEX, '', $text); // Must be first
    	echo ($cleandata === null) ? "isNull\n" : "notNull\n";
    	echo array_flip(get_defined_constants(true)['pcre'])[preg_last_error()] . "\n";
    	echo "'$cleandata'\n";
    	$cleandata = preg_replace(array(CommonRegex::COMMENT_REGEX, CommonRegex::REFERENCE_REGEX, CommonRegex::NOWIKI_REGEX), '', $cleandata);

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