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
*{{la|Shoichiro Sakai}} by [[User:TakuyaMurata|TakuyaMurata]] (<span class="plainlinks">[[User_talk:TakuyaMurata|talk]]&nbsp;'''á'''&#32;[[Special:Contributions/TakuyaMurata|contribs]]&nbsp;'''á'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=TakuyaMurata&earliest=20131121000000 new pages &#40;14]&#41;</span>) started on 2013-11-23, score: 100
*{{la|Ikumi Hayama}} by {{User|Darkslug}} started on 2013-11-21, score: 180
*[[Madhuca markleeana]] ([[Talk:Madhuca markleeana|talk]]) by [[User:Declangi|Declangi]] started on 2013-11-16, score: 40
----
----
<ul>
<li>{{la|Basketball uniform}} by [[User:Accedie|Accedie]] (<span class="plainlinks">[[User_talk:Accedie|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Accedie|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=Accedie&days=14 new pages &#40;1&#41;]</span>) started on 2013-12-25, score: 24</li>
{{User:AlexNewArtBot/MaintDisplay|<li>{{pagelinks|Draft:DJ Many}} by [[User:Ocaasi{{!}}Ocaasi]] (<span class{{=}}"plainlinks">[[User_talk:Ocaasi{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Ocaasi{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}Ocaasi&days{{=}}14 new pages &#40;1&#41;]</span>) started on 2013-12-23, score: 25</li>|0}}
{{User:AlexNewArtBot/MaintDisplay|<li>[[Template:PitchforkSong]] ([[Template talk:PitchforkSong{{!}}talk]]) by [[User:Lu0490{{!}}Lu0490]] (<span class{{=}}"plainlinks">[[User_talk:Lu0490{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Lu0490{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}Lu0490&days{{=}}14 new pages &#40;6&#41;]</span>) started on 2013-12-27, score: 21</li>|1}}
{{User:AlexNewArtBot/MaintDisplay|<li>[[:Category:Municipal coats of arms in Romania]] by [[User:Arms_Jones{{!}}Arms Jones]] started on 2014-01-02, score: 50</li>}}
<li>[[Madhuca malvina]] ([[Talk:Madhuca malvina|talk]]) by [[User:Declangi|Declangi]] started on 2013-11-16, score: 40</li>
{{User:AlexNewArtBot/MaintDisplay|<li>{{pagelinks|Draft:M-54 and M-83 (Michigan highway)}} by [[User:Highway_231{{!}}Highway 231]] (<span class{{=}}"plainlinks">[[User_talk:Highway_231{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/Highway_231{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}Highway+231&days{{=}}14 new pages &#40;4&#41;]</span>) started on 2014-01-21, score: 34</li>}}
</ul>
----
EOT;

        $articles = array('Shoichiro Sakai', 'Ikumi Hayama', 'Madhuca markleeana', 'Basketball uniform', 'Draft:DJ Many', 'Template:PitchforkSong', 'Category:Municipal coats of arms in Romania', 'Madhuca malvina', 'Draft:M-54 and M-83 (Michigan highway)');
        $users = array('TakuyaMurata', 'Darkslug', 'Declangi', 'Accedie', 'Ocaasi', 'Lu0490', 'Arms Jones', 'Declangi', 'Highway 231');
        $timestamps = array('2013-11-23', '2013-11-21', '2013-11-16', '2013-12-25', '2013-12-23', '2013-12-27', '2014-01-02', '2013-11-16', '2014-01-21');
        $totalScores = array('100', '180', '40', '24', '25', '21', '50', '40', '34');
        $wikipediaNSs = array('1', '1', '1', '1', '0', '1', '1', '1', '1');

        $parser = new ExistingResultParser();
        $results = $parser->parsePage($data);
        //print_r($results);

        $this->assertEqual(count($results), 2, 'Wrong section count');
        $this->assertEqual(count($results[0]), 3, 'Wrong section 1 count');
        $this->assertEqual(count($results[1]), 6, 'Wrong section 2 count');

        $results = array_merge($results[0], $results[1]);

        $x = 0;
        foreach ($results as $line) {
            $this->assertEqual($line['title'], $articles[$x], 'Article mismatch #' . ($x + 1));
            $this->assertEqual($line['user'], $users[$x], 'User mismatch #' . ($x + 1));
            $this->assertEqual($line['timestamp'], $timestamps[$x], 'Timestamp mismatch #' . ($x + 1));
            $this->assertEqual($line['totalScore'], $totalScores[$x], 'totalScore mismatch #' . ($x + 1));
            $this->assertEqual($line['WikipediaNS'], $wikipediaNSs[$x], 'WikipediaNS mismatch #' . ($x + 1));
            ++$x;
        }
    }
}