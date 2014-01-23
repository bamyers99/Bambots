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

namespace com_brucemyers\InceptionBot;

class ExistingResultParser
{
    protected $startTokens = array('<li>{{', '{{User:AlexNewArtBot/MaintDisplay|<li>', '*{{');
    protected $linePatterns = array(
        '!^(?:\\*|<li>)(?:\\{\\{la\\||\\[\\[)([^\\]\\}]+)[\\]\\}]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User\\||\\[\\[User:[^\\|]+\\|)([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?\\s*started on\\s*([^,]+), score: (\\d+)!',
        '!^\\{\\{User:AlexNewArtBot/MaintDisplay\\|<li>\\{\\{pagelinks\\|([^\\}]+)\\}+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User|\\[\\[User:[^\\|]+)\\{\\{\\!\\}\\}([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?\\s*started on\\s*([^,]+), score: (\\d+)!',
        '!^\\{\\{User:AlexNewArtBot/MaintDisplay\\|<li>\\[\\[:?([^\\]]+)[\\]]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User|\\[\\[User:[^\\|]+)\\{\\{\\!\\}\\}([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?\\s*started on\\s*([^,]+), score: (\\d+)!'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Parse page data into result data
     *
     * @param $pagedata string Page data to parse
     * @return array of arrays (by found day) of records of $title => array('title', 'user', 'timestamp', 'totalScore', 'type' => 'MD' or 'N')
     */
    public function parsePage(&$pagedata)
    {
        $startpos = 999999;
        foreach ($this->startTokens as $startToken) {
            $tokenpos = strpos($pagedata, $startToken);
            if ($tokenpos !== false && $tokenpos < $startpos) $startpos = $tokenpos;
        }

        if ($startpos == 999999) return array();

        $results = array(0 => array());

        $section = 0;
        $lines = explode("\n", substr($pagedata, $startpos));

        foreach ($lines as $line) {
            if ($line == '----') {
                if (! empty($results[$section])) $results[++$section] = array();
            } else {
                foreach ($this->linePatterns as $pattern) {
                    if (preg_match($pattern, $line, $matches)) {
                        if (strpos($line, 'User:AlexNewArtBot/MaintDisplay') !== false) $type = 'MD';
                        else $type = 'N';
                        $results[$section][$matches[1]] = array('title' => $matches[1], 'user' => $matches[2], 'timestamp' => $matches[3],
                                        'totalScore' => $matches[4], 'type' => $type);
                        break;
                    }
                }
            }
        }

        if (empty($results[$section])) unset($results[$section]);

        return $results;
    }
}
