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

use com_brucemyers\InceptionBot\RuleSet;
use com_brucemyers\InceptionBot\RuleSetProcessor;
use UnitTestCase;

class TestRuleSetProcessor extends UnitTestCase
{
    public function testRules()
    {
        $rules = <<<'EOT'
        /Michigan/
        20 /\Wmichigan(-\w+){0,2}-stub\}\}/
        -5 /Indiana/
        $$TestTemplate$$
        7 /$SIZE>10/
        9 /$SIZE<50000/
        -50 /$SIZE<10/
        /$TITLE:National Park/
        100 /InComment/
        100 /Reference/
EOT;

        $data = <<<'EOT2'
{{Infobox|population=50<ref name="gov"/>|title=MI}}

'''Michigan''' is in the United States.<ref name="USAReference">{{Cite web
   	|url=http://usa.gov
   	|title=USA Reference}}</ref>

<!-- Shouldn't match rule InComment -->

==See also==
* [[Michigan City, Indiana]]<ref>{{Cite web|url=http://indiana.gov|title=Indiana Reference}}</ref>

{{Michigan-stub}}
EOT2;

        $ruleset = new RuleSet('test', $rules);
        $errorcnt = count($ruleset->errors);
        $this->assertEqual($ruleset->minScore, 10, 'Invalid min score');
        $this->assertEqual($errorcnt, 0, 'Parse error');
        if ($errorcnt) print_r($ruleset->errors);
        $title = 'Yellowstone National Park';

        $processor = new RuleSetProcessor($ruleset);
        $results = $processor->processData($data, $title);
        $this->assertEqual(count($results), 6, 'Mismatched rule count');

        $totalScore = 0;
        $realScore = 61; // Includes lede match
        foreach ($results as $result) {
            $totalScore += $result['score'];
        }
        $this->assertEqual($totalScore, $realScore, 'Bad score');
        if ($totalScore != $realScore) print_r($results);

        //print_r($results);
    }

    public function testInhibitors()
    {
        $rules = <<<'EOT'
        /Michigan/ <!-- 20 points -->
        -5 /Indiana/ <!-- -5 points -->
        -10 /Michigan City/, /Indiana/ <!-- Inhibited -->
        10 /United States/ , /Germany/, /Great Britian/ <!-- 20 points -->
EOT;

        $data = <<<'EOT2'
{{Infobox|title=Michigan}}
'''Michigan''' is in the United States.

==See also==
* [[Michigan City, Indiana]]

{{Michigan-stub}}
EOT2;

        $ruleset = new RuleSet('test', $rules);
        $errorcnt = count($ruleset->errors);
        $this->assertEqual($ruleset->minScore, 10, 'Invalid min score');
        $this->assertEqual($errorcnt, 0, 'Parse error');
        if ($errorcnt) print_r($ruleset->errors);
        $title = 'Yellowstone National Park';

        $processor = new RuleSetProcessor($ruleset);
        $results = $processor->processData($data, $title);
        $this->assertEqual(count($results), 3, 'Mismatched rule count');

        $totalScore = 0;
        $realScore = 35; // Includes lede match
        foreach ($results as $result) {
            $totalScore += $result['score'];
        }
        $this->assertEqual($totalScore, $realScore, 'Bad score');
        if ($totalScore != $realScore) print_r($results);

        //print_r($results);
    }

    public function testUnicode()
    {
        $curdir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $rules = file_get_contents($curdir . 'UnicodeRuleSet.txt');
        $data = file_get_contents($curdir . 'UnicodeData.txt');

        $ruleset = new RuleSet('test', $rules);
        $errorcnt = count($ruleset->errors);
        $this->assertEqual($errorcnt, 0, 'Parse error');
        if ($errorcnt) print_r($ruleset->errors);
        $title = 'Yellowstone National Park';

        //print_r($ruleset);

        $processor = new RuleSetProcessor($ruleset);
        $results = $processor->processData($data, $title);
        $this->assertEqual(count($results), 2, 'Mismatched rule count');

        $totalScore = 0;
        $realScore = 30;
        foreach ($results as $result) {
            $totalScore += $result['score'];
        }
        $this->assertEqual($totalScore, $realScore, 'Bad score');
        if ($totalScore != $realScore) print_r($results);

        //print_r($results);
    }
}
