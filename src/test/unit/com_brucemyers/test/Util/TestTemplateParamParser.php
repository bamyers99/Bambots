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
use com_brucemyers\Util\CommonRegex;
use UnitTestCase;

class TestTemplateParamParser extends UnitTestCase
{

    public function notestGetTemplates()
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

    function testReflist()
    {
    	$data = <<<EOT
{{sections|date=October 2014}}
{{Infobox automobile
|name=M20 Pobeda
|aka=
|image=ГАЗ М20 Победа.jpg
|manufacturer=[[GAZ]]
|assembly=[[Nizhny Novgorod|Gorky]], [[Soviet Union]]
|production=1949–1958
|predecessor=[[GAZ-M1]]
|successor= Volga [[GAZ-21]]
|class=[[Executive car]]
|body_style=4-door [[sedan (car)|sedan]] [[fastback]]/[[Cabriolet (automobile)|cabriolet]]
|engine=2.1L ''M-20'' [[straight-4 engine|I4]]
|layout={{unbulleted list|[[FR layout]]|[[F4 layout]] (GAZ-M72)}}
|length= {{convert|4665|mm|in|1|abbr=on}}{{Sfn|Bogomolov|1999}}
|wheelbase={{convert|2700|mm|in|1|abbr=on}}{{Sfn|Bogomolov|1999}}
|width= {{convert|1695|mm|in|1|abbr=on}}{{Sfn|Bogomolov|1999}}
|height= {{convert|1590|mm|in|1|abbr=on}}{{Sfn|Bogomolov|1999}}
|weight= 1460&nbsp;kg{{Sfn|Bogomolov|1999}}
|related={{unbulleted list|[[FSO Warszawa]]|GAZ-M72 (4WD, model with Pobeda body)}}
}}
[[File:М72 автомобиль.JPG|thumb|right|GAZ M72]]
The '''GAZ-M20'''  "'''Pobeda'''" ({{lang-ru|ГАЗ-М20 Победа}}; ''Победа'') was a passenger car produced in the [[Soviet Union]] by [[GAZ]] from 1946 until 1958. It was also licensed to [[Poland|Polish]] [[Fabryka Samochodów Osobowych]], as [[FSO Warszawa]]. Although usually known as the GAZ-M20, an original car's designation at that time was just M-20, for "Molotovets" (GAZ factory bore a name of [[Vyacheslav Molotov]]).{{Sfn|Dolmatovskiy|Trepenyenkov|1957|p=122}}

Originally intended to be called Rodina (Homeland), the name Pobeda (Victory) was a back-up, but was preferred by Stalin.{{Sfn|Thompson|2008|p=52}}  The first Pobeda was developed in the Soviet Union under chief engineer Andrei A. Liphart. "Pobeda" means "victory"; and the name was chosen because the works started in 1943 at Gorky Avto Zavod ([[GAZ]], "Gorky Car Plant"), when victory in [[World War II]] began to seem likely, and the car was to be a model for post-war times.  The plant was later heavily bombarded, but work was unaffected. Styling was done by "the imaginative and talented [[Veniamin Samoilov]]".{{Sfn|Thompson|2008|p=51}} It was the first Soviet passenger car not copying any foreign design, and moreover it introduced most modern [[ponton (automobile)|ponton]] styling, with slab sides, preceding many Western manufacturers.<ref name=avto23-2 /> Only a construction of [[monocoque]] body and front suspension were copied from 1938 [[Opel Kapitän]].<ref name=avto23-2/> The M20 was the first Soviet car using entirely domestic body dies;{{Sfn|Thompson|2008|p=51}} it was designed against wooden bucks,{{Sfn|Thompson|2008|p=51}} which suffered warping, requiring last-minute tuning by GAZ factory employees.{{Sfn|Thompson|2008|p=52}} The first prototype was ready on November 6, 1944 (for an anniversary of the [[October Revolution]]), and after it gained approval the first production model rolled off the assembly line on June 21, 1946. It was the first Soviet car with electric [[windshield wiper]]s (rather than mechanical- or vacuum-operated ones).{{Sfn|Thompson|2008|p=52}} It also had four-wheel hydraulic brakes.{{Sfn|Thompson|2008|p=53}}

During the design process, GAZ had to choose between a {{convert|62|hp|kW PS|abbr=on}} {{convert|2,700|cc|cuin|0|abbr=on}} [[Straight-six engine|inline six]] and a {{convert|50|hp|kW PS|abbr=on}} {{convert|2,112|cc|cuin|0|abbr=on}} [[Inline four engine|inline four]]; Stalin preferred the four, so it was used.{{Sfn|Thompson|2008|p=52}} For cost efficiency, the engine construction was partially based on that from a 1935 Dodge D5 of which the plans were purchased from Chrysler for $20 000,-.<ref name=PassengerCars /> In addition, the headlights were covered by an American patent.{{Sfn|Thompson|2008|p=53}}

Production was difficult; by the end of 1946, only twenty-three cars were completed, virtually by hand.{{Sfn|Thompson|2008|p=53}} Truly mass production had to wait until 28 April 1947, and even then, only 700 were built before October 1948.{{Sfn|Thompson|2008|p=54}} There were numerous problems. The Soviet Union was unable to produce steel sheets large enough for body panels, so strips had to be welded together, which led to countless leaks and {{convert|20|kg|lb|abbr=on}} of solder in the body, as well as an increase in weight of {{convert|200|kg|lb|abbr=on}}.{{Sfn|Thompson|2008|p=54}} Steel quality was so bad, up to 60% was rejected, and overall quality was so poor, production actually stopped, by order of the government and the company's director was fired.{{Sfn|Thompson|2008|p=54}}

After making 346 improvements, and adding two thousand new tools, the Pobeda was restored to production.{{Sfn|Thompson|2008|p=55}} It had a new carburettor, different final drive ratio (5.125:1 rather than 4.7:1), strengthened rear springs, improved heater, and the ability to run on the low-grade 66[octane] fuel typical in the Soviet Union.{{Sfn|Thompson|2008|p=55}} (Among the changes was a {{convert|5|cm|in|abbr=on}} lower rear seat, enabling Red Army officers to ride without removing their caps.){{Sfn|Thompson|2008|p=55}} The improvements enabled the new Pobeda to reach {{convert|50|km/h|mph|abbr=on}} in 12 seconds, half the previous model's time.{{Sfn|Thompson|2008|p=55}}

The improved Pobeda was placed in production 1 November 1949,{{Sfn|Thompson|2008|p=55}} and the techniques needed to develop and manufacture it effectively created the Soviet automobile industry.{{Sfn|Thompson|2008|p=56}} In 1952, improved airflow in the engine increased power from {{convert|50|hp|kW PS|lk=on|abbr=on}} to {{convert|52|hp|kW PS|lk=on|abbr=on}};{{Sfn|Thompson|2008|p=56}} it climbed to {{convert|55|hp|kW PS|lk=on|abbr=on}}, along with the new grille, upholstery, steering wheel, radio, and radiator badge, as the M20V (Russian: ''М-20В''), 1955.{{Sfn|Thompson|2008|p=57}}

A [[column shift]] [[Manual transmission#synchromesh|synchromesh]] gearbox appeared in 1950, replacing the floor-shifted "crash box".{{Sfn|Thompson|2008|p=56}} In 1949 debuted a [[cabriolet]] (without a separate designation, surviving until 1953), and a taxi M-20A, with cheaper interior (first regular taxi model in Moscow); some of the cabriolets were also used as taxis.<ref name=avto23-15/>

The car was a successful export for the USSR, and the design was licensed to the Polish [[Fabryka Samochodów Osobowych|FSO]] factory in [[Warsaw]], where it was built as the  [[FSO Warszawa]] beginning in 1951, continuing until 1973.{{Sfn|Thompson|2008|p=57}} A few were assembled in [[Pyongyang]], [[North Korea]].{{Sfn|Thompson|2008|p=53}}

Weighing {{convert|1,460|kg|lb|0|abbr=on}},{{Sfn|Thompson|2008|p=53}} the Pobeda has 2.1 litre [[Cam-in-block|sidevalve]] straight-4 engine producing {{convert|50|hp|abbr=on}} and top speed of {{convert|105|km/h|mi/h|0|abbr=on}}.

The Pobeda was the first Soviet automobile to have turn signals, two electric wipers, an electric heater, and a built-in [[AM radio]]. The car came to be a symbol of postwar Soviet life and is today a popular collector's item.

In 1949-53, 14,222 M-20s were built with 4-door convertible body (of '[[cabrio coach]]' type), but sales were poor and the GAZ never returned to the idea of mass-producing a convertible. The only reason to create a cabriolet, less practical in Soviet climate, were low production capabilities of sheet metal, due to war damage.<ref name=avto23-15 />

In 1955, the first "comfortable mass-produced" [[monocoque]] all-wheel drive vehicle appeared, the [[GAZ-72|M72]], with a four-wheel drive system adapted from the contemporary Soviet [[GAZ-69]].{{Sfn|Thompson|2008|p=57}}<ref name=M72 /><ref name="gaz20.spb.ru/" /> It was the brainchild of [[Vitaly Gracheva]], assistant to the GAZ-69's chief engineer, [[Grigory Moiseevich]].{{Sfn|Thompson|2008|p=57}} It used a standard Pobeda transmission, mated to the GAZ-69 front axle, leaf spring suspension, and [[transfer case]], with a brand-new rear axle (used on no other vehicle, a rarity for Soviet car production).{{Sfn|Thompson|2008|p=57}} The body had fourteen panels added to strengthen the floor, frame, doors, and roof.{{Sfn|Thompson|2008|p=57}} Trim and interior were otherwise the same as the M20, and in all, 4,677 were built by end of production in 1958.{{Sfn|Thompson|2008|p=57}}

A limited edition M20G for the [[KGB]] (number unknown, but very small), powered by a {{convert|3,485|cc|cuin|abbr=on}} straight six (from the [[GAZ-12 ZIM|GAZ M12 ZIM]]), was also produced, giving the Pobeda a top speed reportedly {{convert|87|mph|km/h|abbr=on}}, and {{convert|0|-|60|mph|km/h|abbr=on}} time was down to 16 seconds from the stock model's 34; handling was compromised by the extra front-end weight.{{Sfn|Thompson|2008|p=57}}

Total production of the Pobeda was 235,999, including 37,492 taxis and 14,222 cabriolets.{{Sfn|Thompson|2008|p=58}} A great number of cars was used by government organizations and government-owned corporations, including [[taxicab]] parks (there were no private taxis in the USSR). Despite its 16,000 [[ruble]] price tag, with average wage 800 ruble, the Pobeda was available to buy for ordinary citizens, and only from 1954-1955 a demand for cars in the USSR started to overgrow a production, and there appeared long queues to buy a car{{Sfn|Girshovich|2003|p=44}} It was also the first serious opportunity for the Soviet automobile industry to export cars, and "Western drivers found it to be almost indestructible".{{Sfn|Thompson|2008|p=58}}

The Pobeda was replaced by the [[GAZ-21|GAZ M21 Volga]].{{Sfn|Thompson|2008|p=60}}

A prototype [[Cab over|cab-over-engine]] (forward control, COE) vehicle, the [[GAZ 013]], was based on the Pobeda, but not built.{{Sfn|Thompson|2008|p=58}}

<gallery>
File:Pobeda-Mockup-1943-44.jpg|Clay model, 1943
File:Pobeda (2nd Pavlovsky) 1600px (1).jpg|GAZ M20V (1955-1958)
File:GAZ-M-20 "Pobeda" on CMSh in Lahti, Finland.jpg|GAZ M20 (1948-1955)
File:GAZ Pobieda in a street of Mtskheta - Georgia 2.jpg
File:Pobeda (2nd Pavlovsky) 1600px (3).jpg
</gallery>

==Notes==
{{Reflist|30em|refs=
<ref name=avto23-2>[[#DeAgostini|''GAZ-M20 «Pobeda»'']], "Avtolegendy SSSR" Nr. 23, 2009, pp.2-3</ref>
<ref name=avto23-15>[[#DeAgostini|''GAZ-M20 «Pobeda»'']], "Avtolegendy SSSR" Nr. 23, 2009, p.15</ref>

<ref name="gaz20.spb.ru/">"[[#GAZ20_M20|GAZ–M20]]" at ''gaz20.spb.ru''</ref>
<ref name=M72>"[[#GAZ20_M72|GAZ M-72]]" at ''gaz20.spb.ru''</ref>

<ref name=PassengerCars>"[[#PassengerCars|The car GAZ M-20 «Pobeda»]]", from ''Рassenger cars GAZ''</ref>
}}

==References==
*{{cite web|url=http://www.autogallery.org.ru/gaz20.htm|title=GAZ-M20|last= Bogomolov|first= Andrei|year= 1999|accessdate=2008-02-11|work=autogallery.org.ru|ref=harv}}
*{{cite book|last1= Dolmatovskiy|first1= Yu.|last2= Trepenyenkov|first2= I.|title= Traktory i avtomobili|location= Moscow, USSR|year= 1957|page= 122|language= Russian|ref=harv}}
*{{cite journal|last= Girshovich|first= Igor|title= Pochemu ya yezzhu po doverennosti|journal= Igrushki Dla Bolshyh|issue= 22/2003|year=2003|page= 44|language= Russian|ref=harv}}
*{{cite book|last= Thompson|first= Andy|title= Cars of the Soviet Union|publisher= Haynes Publishing|location= Somerset, UK|year= 2008|page= 52|ref= harv}}
*{{cite web|url= http://www.gaz20.spb.ru/|title= GAZ_M20|accessdate= 2009-01-12|work= gaz20.spb.ru|language= Russian|ref=GAZ20_M20}}
*{{cite web|url= http://gaz20.spb.ru/modif_m72.htm|title= GAZ-M-72|accessdate= 2015-03-25|work= gaz20.spb.ru|language= Russian|ref=GAZ20_M72}}
*{{cite journal|title= GAZ-M20 «Pobeda»|journal= Avtolegendy SSSR|issue= Nr. 23|publisher= DeAgostini|year= 2009|issn= 2071-095X|language= Russian|ref=DeAgostini}}
*{{cite web|url= http://gaz-avto.ucoz.ru/index/the_car_gaz_m_20_pobeda/0-11|title= The car GAZ M-20 «Pobeda».|website= Рassenger cars GAZ|ref=PassengerCars}}

==External links==
{{commons category|GAZ-M20 Pobeda}}
*[http://gaz20.spb.ru Main Russian Pobeda site] by Artem Alekseyenko {{Ru icon}}
*[http://www.volga.nl/GAZhistorieENvictory.htm Pobeda] by Jelle Jan Gerrits.
*[http://forum.automoto.ee/forumdisplay.php?fid=70 Estonian Pobeda Club Forum]
*[http://englishrussia.com/?p=1511 Pobeda the SUV-version]

{{Russian Automotive Makers}}

[[Category:Cars of Russia]]
[[Category:Soviet automobiles]]
[[Category:GAZ]]
[[Category:Rear-wheel-drive vehicles]]
[[Category:Sedans]]
[[Category:1940s automobiles]]
[[Category:1950s automobiles]]
[[Category:Vehicles introduced in 1946]]

EOT;

    	$cleandata = preg_replace(CommonRegex::REFERENCESTUB_REGEX, '', $data); // Must be first
    	$cleandata = preg_replace(array(CommonRegex::COMMENT_REGEX, CommonRegex::REFERENCE_REGEX, CommonRegex::NOWIKI_REGEX), '', $cleandata);
    	$templatedata = TemplateParamParser::getTemplates($cleandata);
    	//echo $cleandata;
    	print_r($templatedata);
    }
}