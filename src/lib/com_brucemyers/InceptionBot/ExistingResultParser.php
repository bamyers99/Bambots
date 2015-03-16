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

use com_brucemyers\Util\Logger;

class ExistingResultParser
{
    protected $startTokens = array('<li>{{', '{{User:AlexNewArtBot/MaintDisplay|', '*{{');
    protected $linePatterns = array(
        '!^(?:\\*|<li>)(?:\\{\\{la\\||\\[\\[)([^\\]\\}]+)[\\]\\}]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User\\||\\[\\[User:[^\\|]+\\|)([^\\]\\}]+)[\\]\\}]+\\s*started on\\s*([^,]+), score: (\\d+)!',
        '!^(?:\\*|<li>)(?:\\{\\{la\\||\\[\\[)([^\\]\\}]+)[\\]\\}]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User\\||\\[\\[User:[^\\|]+\\|)([^\\]\\}]+)[\\]\\}]+\\s*\\(.*started on\\s*([^,]+), score: (\\d+)!', // Can be ) in username
    	'!^\\{\\{User:AlexNewArtBot/MaintDisplay\\|<li>\\{\\{pagelinks\\|([^\\}]+)\\}+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User|\\[\\[User:[^\\|]+)\\{\\{\\!\\}\\}([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?\\s*started on\\s*([^,]+), score: (\\d+</li>\\|?\d?)}}!',
        '!^\\{\\{User:AlexNewArtBot/MaintDisplay\\|<li>\\[\\[:?([^\\]]+)[\\]]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User|\\[\\[User:[^\\|]+)\\{\\{\\!\\}\\}([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?\\s*started on\\s*([^,]+), score: (\\d+</li>\\|?\d?)}}!'
    );
    protected $maintPatterns = array(
    		'!^<li>\\{\\{pagelinks\\|([^\\}]+)\\}+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User|\\[\\[User:[^\\|]+)\\{\\{\\!\\}\\}([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?\\s*started on\\s*([^,]+), score: (\\d+)</li>!',
    		'!^<li>\\[\\[:?([^\\]]+)[\\]]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User|\\[\\[User:[^\\|]+)\\{\\{\\!\\}\\}([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?\\s*started on\\s*([^,]+), score: (\\d+)</li>!'
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
        $inmaint = false;

        foreach ($lines as $line) {
            if ($line == '----') {
                if (! empty($results[$section])) $results[++$section] = array();
                continue;
            }

            if ($line == '{{User:AlexNewArtBot/MaintDisplay|') {
            	$inmaint = true;
            	$maintResults = array();
            	continue;
            }

            if (preg_match('/^\\|(\\d)}}$/', $line, $matches)) {
            	if (! $inmaint) {
            		Logger::log(substr($pagedata, 0, 500));
            		continue;
            	}
            	$WikipediaNS = $matches[1];

            	foreach ($maintResults as $result) {
            		$result['WikipediaNS'] = $WikipediaNS;
            		$title = $result['title'];
            		$results[$section][$title] = $result;
            	}

            	$inmaint = false;
            	continue;
            }

            if ($inmaint) {
                foreach ($this->maintPatterns as $pattern) {
	                if (preg_match($pattern, $line, $matches)) {
	                    $title = str_replace('&#61;', '=', $matches[1]);

	                    $maintResults[] = array('title' => $title, 'user' => $matches[2], 'timestamp' => $matches[3],
	                                        'totalScore' => $matches[4], 'type' => 'MD');
	                    break;
	                }
	            }

            	continue;
            }

            foreach ($this->linePatterns as $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    if (strpos($line, 'User:AlexNewArtBot/MaintDisplay') !== false) $type = 'MD';
                    else $type = 'N';

                    // Extract the score and optional Wikipedia namespace suppression
                    $WikipediaNS = '1';
                    $totalScore = $matches[4];
                    if (preg_match('!(\\d+)</li>\\|?\d?!', $totalScore, $scoreMatches)) {
                        $temp = $totalScore;
                        $totalScore = $scoreMatches[1];
                        if (strpos($temp, '|') !== false) {
                            list($dummy, $WikipediaNS) = explode('|', $temp);
                        }
                    }
                    $title = str_replace('&#61;', '=', $matches[1]);

                    $results[$section][$title] = array('title' => $title, 'user' => $matches[2], 'timestamp' => $matches[3],
                                        'totalScore' => $totalScore, 'type' => $type, 'WikipediaNS' => $WikipediaNS);
                    break;
                }
            }
        }

        if (empty($results[$section])) unset($results[$section]);

        return $results;
    }
}
