<?php
/**
 Copyright 2013 Myers Enterprises II

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

namespace com_brucemyers\test\InceptionBot;

use com_brucemyers\InceptionBot\MasterRuleConfig;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\InceptionBot\InceptionBot;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use UnitTestCase;

class CustomRunner extends UnitTestCase
{
    protected $activerules = array(
        'Architecture' => 'Portal:Architecture/New article announcements',
        'Astro' => '',
        'Biomes' => '',
        'Bivalves' => '',
        'Cheshire' => '',
        'Cuisine' => '',
        'Cycling' => 'Wikipedia:WikiProject_Cycling/New_articles',
        'FoodDrink' => '',
        'Forestry' => 'Wikipedia:WikiProject Forestry',
        'Gastropods' => '',
        'HipHop' => '',
        'Japan' => '',
        'Michigan' => '',
        'Opera' => 'Wikipedia:WikiProject Opera/New article bot',
        'Oregon' => '',
        'Philately' => 'Wikipedia:WikiProject Philately/New articles',
        'Poland' => 'Portal:Poland/New article announcements',
        'Sweden' => "Wikipedia:Swedish Wikipedians' notice board/New articles"
    );

    public function testRunner()
    {
        $ruletype = Config::get(InceptionBot::RULETYPE);
        $outputtype = Config::get(InceptionBot::OUTPUTTYPE);

        $timer = new Timer();
        $timer->start();

        $url = Config::get(MediaWiki::WIKIURLKEY);
        $wiki = new MediaWiki($url);
        $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
        $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
        $wiki->login($username, $password);

        if ($ruletype == 'active') $rules = $this->activerules;
        elseif ($ruletype == 'custom') $rules = array(Config::get(InceptionBot::CUSTOMRULE) => '');
        else {
            $data = $wiki->getpage('User:AlexNewArtBot/Master');

            $masterconfig = new MasterRuleConfig($data);

            // Prioritize the active rules first
            $rules = array_merge($activerules, $masterconfig->ruleConfig);
        }

        $historydays = Config::get(InceptionBot::HISTORYDAYS);
        $earliestTimestamp = gmdate('Ymd', strtotime("-$historydays days")) . '000000';
        $lastrun = Config::get(InceptionBot::LASTRUN);
        $newlastrun = gmdate('YmdHis');

        $bot = new InceptionBot($wiki, $rules, $earliestTimestamp, $lastrun, new FileResultWriter(Config::get(InceptionBot::OUTPUTDIR)), $newlastrun);

        Config::set(InceptionBot::LASTRUN, $newlastrun);

        $ts = $timer->stop();

        echo sprintf("\nElapsed Time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']) . "\n";
    }
}