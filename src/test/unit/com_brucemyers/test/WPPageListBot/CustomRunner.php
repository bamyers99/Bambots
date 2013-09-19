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

namespace com_brucemyers\test\WPPageListBot;

use com_brucemyers\WPPageListBot\WPPageListBot;
use com_brucemyers\WPPageListBot\FileResultWriter;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use UnitTestCase;

class CustomRunner extends UnitTestCase
{
    protected $activerules = array(
        'WikiProject Oregon' => array(
                        'category' => 'WikiProject Oregon pages',
                        'articles' => 'Wikipedia:WikiProject Oregon/Admin',
                        'nonarticles' => 'Wikipedia:WikiProject Oregon/Admin2',
                        'bannertemplate' => 'Wikipedia:WikiProject Oregon/Nav'
        ),
        'WikiProject Michigan' => array(
                        'category' => 'WikiProject Michigan articles',
                        'articles' => 'Wikipedia:WikiProject Michigan/Michigan recent changes',
                        'nonarticles' => '',
                        'bannertemplate' => ''
        ),
    );

    public function testRunner()
    {
        $ruletype = 'custom'; // 'active', 'custom', 'all'

        $timer = new Timer();
        $timer->start();

        $url = Config::get(MediaWiki::WIKIURLKEY);
        $wiki = new MediaWiki($url);
        $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
        $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
        $wiki->login($username, $password);

        if ($ruletype == 'active') $rules = $this->activerules;
        elseif ($ruletype== 'custom') $rules = array('WikiProject Michigan' => $this->activerules['WikiProject Michigan']);
        else {
            $data = $wiki->getpage('User:AlexNewArtBot/Master');

            $masterconfig = new MasterRuleConfig($data);
            $rules = $masterconfig->ruleConfig;
        }


        $bot = new WPPageListBot($wiki, $rules, new FileResultWriter());

        $ts = $timer->stop();

        echo sprintf("\nElapsed Time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']) . "\n";
    }
}