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

use com_brucemyers\InceptionBot\InceptionBot;
use UnitTestCase;

class TestExistingRegex extends UnitTestCase
{

    public function testRegex()
    {
        $data = <<<'EOT'
*{{la|Shoichiro Sakai}} by [[User:TakuyaMurata|TakuyaMurata]] (<span class="plainlinks">[[User_talk:TakuyaMurata|talk]]&nbsp;'''á'''&#32;[[Special:Contributions/TakuyaMurata|contribs]]&nbsp;'''á'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=TakuyaMurata&earliest=20131121000000 new pages &#40;14]&#41;</span>) started on 2013-11-23, score: 100
*{{la|Ikumi Hayama}} by {{User|Darkslug}} started on 2013-11-21, score: 180
*[[Madhuca markleeana]] ([[Talk:Madhuca markleeana|talk]]) by [[User:Declangi|Declangi]] started on 2013-11-16, score: 40
EOT;

        $articles = array('Shoichiro Sakai', 'Ikumi Hayama', 'Madhuca markleeana');
        $users = array('TakuyaMurata', 'Darkslug', 'Declangi');

        $lines = explode("\n", $data);
        $this->assertEqual(count($lines), 3, 'Wrong line count');

        $x = 0;
        foreach ($lines as $line) {
            $result = preg_match(InceptionBot::EXISTINGREGEX, $line, $matches);
            $this->assertEqual($result, 1, 'Regex mismatch #' . ($x + 1));
            if ($result) {
                $this->assertEqual($matches[1], $articles[$x], 'Article mismatch #' . ($x + 1));
                $this->assertEqual($matches[2], $users[$x], 'User mismatch #' . ($x + 1));
                $this->assertEqual(strpos($matches[3], ' started on '), 0, 'Other mismatch #' . ($x + 1));
            }
            ++$x;
        }
    }
}