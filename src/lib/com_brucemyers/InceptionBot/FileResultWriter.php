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

class FileResultWriter implements ResultWriter
{
    public function writeResults($resultpage, $logpage, $results, RuleSet $ruleset, $proctime)
    {
        $rulename = $ruleset->name;
        usort($results, function($a, $b) {
            return -strnatcmp($a['pageinfo']['timestamp'], $b['pageinfo']['timestamp']); // sort in reverse date order
        });

        // Result file
        $output = "This list was generated from [[User:AlexNewArtBot/{$rulename}|these rules]]. Questions and feedback [[User talk:Tedder|are always welcome]]! The search is being run manually, but eventually will run ~daily with the most recent ~7 days of results.

[[User:AlexNewArtBot/{$rulename}SearchResult/archive|AlexNewArtBot archives]] | [[User:TedderBot/NewPageSearch/{$rulename}/archive|TedderBot archives]] | [[User:AlexNewArtBot/{$rulename}|Rules]] | [[User:TedderBot/NewPageSearch/{$rulename}/errors|Match log and errors]]

";
        foreach ($results as $result) {
            $pageinfo = $result['pageinfo'];
            // for html htmlentities(title and user, ENT_COMPAT, 'UTF-8')
            $output .= '*{{la|' . $pageinfo['title'] . '}} by {{User|' . $pageinfo['user'] . '}} started on ' . substr($pageinfo['timestamp'], 0, 10) . ', score: ' . $result['totalScore'] . "\n";
        }

        $resultpage = str_replace(array(':','/'), '.', $resultpage);
        $resultpage = str_replace('User.', '', $resultpage);
        $filepath = '/Users/brucemyers/temp/tedderbot/' . $resultpage . '.txt';
        file_put_contents($filepath, $output);

        // Log file
        $output = '';

        $rulecnt = count($ruleset->rules);
        $errorcnt = count($ruleset->errors);
        $threshold = $ruleset->minScore;
        $output .= "Pattern count: $rulecnt Error count: $errorcnt Threshold: $threshold Processing time: $proctime\n";
        if ($errorcnt) {
        	$output .= "==Errors==\n";
        	foreach ($ruleset->errors as $error) {
        		$output .= '*' . $error . "\n";
        	}
        }

        if (empty($results)) {
        	$output .= "No pattern matches.\n";
        } else {
        	$output .= "==Scoring notes==\n";

            foreach ($results as $result) {
        	    $totscore = 0;
                foreach ($result['scoring'] as $match) {
                    $totscore += $match['score'];
                }
                $output .= '*[[' . $result['pageinfo']['title'] . "]] Total score: $totscore\n";
                foreach ($result['scoring'] as $match) {
                    $output .= "**Score: " . $match['score'] . ', pattern: <nowiki>' . $match['regex'] . "</nowiki>\n";
                }
        	}
        }

        $logpage = str_replace(array(':','/'), '.', $logpage);
        $logpage = str_replace('User.', '', $logpage);
        $filepath = '/Users/brucemyers/temp/tedderbot/' . $logpage . '.txt';
        file_put_contents($filepath, $output);
    }
}