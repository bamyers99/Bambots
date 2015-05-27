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

use com_brucemyers\InceptionBot\ExistingResultParser;
use UnitTestCase;

class TestExistingRegex extends UnitTestCase
{

    public function testRegex()
    {
        $data = <<<'EOT'
*{{la|Shoichiro Sakai}} by [[User:TakuyaMurata|TakuyaMurata]] (<span class="plainlinks">[[User_talk:TakuyaMurata|talk]]&nbsp;'''�'''&#32;[[Special:Contributions/TakuyaMurata|contribs]]&nbsp;'''�'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=TakuyaMurata&earliest=20131121000000 new pages &#40;14]&#41;</span>) started on 2013-11-23, score: 100
*{{la|0 &#61; 1}} by {{User|Darkslug}} started on 2013-11-21, score: 180
*[[Madhuca markleeana]] ([[Talk:Madhuca markleeana|talk]]) by [[User:Declangi|Declangi]] started on 2013-11-16, score: 40
----
----
<ul>
<li>{{la|Basketball uniform}} by [[User:Accedie|Accedie]] (<span class="plainlinks">[[User_talk:Accedie|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Accedie|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=Accedie&days=14 new pages &#40;1&#41;]</span>) started on 2013-12-25, score: 24</li>
<li>[[Madhuca malvina]] ([[Talk:Madhuca malvina|talk]]) by [[User:Declangi|Declangi]] started on 2013-11-16, score: 40</li>
</ul>
----
<ul>
<li>{{User:AlexNewArtBot/La|Basketball uniform}} by [[User:Accedie|Accedie]] (<span class="plainlinks">[[User_talk:Accedie|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Accedie|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=Accedie&days=14 new pages &#40;1&#41;]</span>) started on 2013-12-25, score: 24</li>
{{User:AlexNewArtBot/MaintDisplay|
<li>{{pagelinks|Draft:DJ Many}} by [[User:Ocaasi{{!}}Ocaasi]] (<span class{{=}}"plainlinks">[[User_talk:Ocaasi{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Ocaasi{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}Ocaasi&days{{=}}14 new pages &#40;1&#41;]</span>) started on 2013-12-23, score: 25</li>
<li>[[:Category:Municipal coats of arms in Romania]] by [[User:Arms_Jones{{!}}Arms Jones]] started on 2014-01-02, score: 50</li>
|0}}
{{User:AlexNewArtBot/MaintDisplay|
<li>[[Template:PitchforkSong]] ([[Template talk:PitchforkSong{{!}}talk]]) by [[User:Lu0490{{!}}Lu0490]] (<span class{{=}}"plainlinks">[[User_talk:Lu0490{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Lu0490{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}Lu0490&days{{=}}14 new pages &#40;6&#41;]</span>) started on 2013-12-27, score: 21</li>
|1}}
</ul>
----
EOT;

        $articles = array('Shoichiro Sakai', '0 = 1', 'Madhuca markleeana',
        		'Basketball uniform', 'Madhuca malvina',
        		'Basketball uniform', 'Draft:DJ Many', 'Category:Municipal coats of arms in Romania', 'Template:PitchforkSong'
        );
        $users = array('TakuyaMurata', 'Darkslug', 'Declangi',
        		'Accedie', 'Declangi',
        		'Accedie', 'Ocaasi', 'Arms Jones', 'Lu0490'
        );
        $timestamps = array('2013-11-23', '2013-11-21', '2013-11-16',
        		'2013-12-25', '2013-11-16',
        		'2013-12-25', '2013-12-23', '2014-01-02', '2013-12-27'
        );
        $totalScores = array('100', '180', '40',
        		'24', '40',
        		'24', '25', '50', '21'
        );
        $wikipediaNSs = array('1', '1', '1',
        		'1', '1',
        		'1', '0', '0', '1'
        );
        $types = array('N', 'N', 'N',
        		'N', 'N',
        		'N', 'MD', 'MD', 'MD'
        );

        $parser = new ExistingResultParser();
        $results = $parser->parsePage($data);
        print_r($results);

        $this->assertEqual(count($results), 3, 'Wrong section count');
        $this->assertEqual(count($results[0]), 3, 'Wrong section 1 count');
        $this->assertEqual(count($results[1]), 2, 'Wrong section 2 count');
        $this->assertEqual(count($results[2]), 4, 'Wrong section 3 count');

        $results = array_merge($results[0], $results[1]);

        $x = 0;
        foreach ($results as $line) {
            $this->assertEqual($line['title'], $articles[$x], 'Article mismatch #' . ($x + 1));
            $this->assertEqual($line['user'], $users[$x], 'User mismatch #' . ($x + 1));
            $this->assertEqual($line['timestamp'], $timestamps[$x], 'Timestamp mismatch #' . ($x + 1));
            $this->assertEqual($line['totalScore'], $totalScores[$x], 'totalScore mismatch #' . ($x + 1));
            $this->assertEqual($line['WikipediaNS'], $wikipediaNSs[$x], 'WikipediaNS mismatch #' . ($x + 1));
            $this->assertEqual($line['type'], $types[$x], 'type mismatch #' . ($x + 1));
            ++$x;
        }
    }
}