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

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Timer;

class InceptionBot
{
    const LASTRUN = 'InceptionBot.lastrun';
    protected $mediawiki;
    protected $resultWriter;

    public function __construct(MediaWiki $mediawiki, $ruleconfigs, $earliestTimestamp, $lastrun, ResultWriter $resultWriter)
    {
        $this->mediawiki = $mediawiki;
        $this->resultWriter = $resultWriter;

        // Retrieve the rulesets
        $rulesets = array();
        foreach ($ruleconfigs as $rulename => $portal) {
            $rulesets[$rulename] = new RuleSet($rulename, $mediawiki->getpage('User:AlexNewArtBot/' . $rulename));
        }

        // Retrieve the new pages
        $lister = new NewPageLister($mediawiki, $earliestTimestamp);

        $allpages = array();

        while (($pages = $lister->getNextBatch()) !== false) {
            $allpages = array_merge($allpages, $pages);
        }

        $pagenames = array();
        foreach ($allpages as $newpage) {
            $pagenames[] = $newpage['title'];
        }

        $mediawiki->getPagesWithCache($pagenames);

        // Retrieve the pages that have changed since the last run
        $revisions = $mediawiki->getPagesLastRevision($pagenames);

        $updatedpages = array();
        foreach ($revisions as $pagename => $revision) {
            if (strcmp(str_replace(array('-',':','T','Z'), '', $revision['timestamp']), $lastrun) > 0) $updatedpages[] = $pagename;
        }

        $mediawiki->getPagesWithCache($updatedpages, true);

        $timer = new Timer();

        // Score new or updated pages
        foreach ($rulesets as $rulename => $ruleset) {
            $timer->start();
            $rulesetresult = array();
            $processor = new RuleSetProcessor($ruleset);

            // Retrieve the existing results
            $existing = $this->_getExistingResults($rulename, $pagenames);

            foreach ($allpages as $newpage) {
                $title = $newpage['title'];
                if (isset($existing[$title])) continue;

                if (strcmp(str_replace(array('-',':','T','Z'), '', $newpage['timestamp']), $lastrun) > 0 || in_array($title, $updatedpages)) {
                    $data = $mediawiki->getPageWithCache($title);
                    $results = $processor->processData($data);

                    $totalScore = 0;
                    foreach ($results as $result) {
                        $totalScore += $result['score'];
                    }

                    if ($totalScore >= $ruleset->minScore) {
                        $rulesetresult[] = array('pageinfo' => $newpage, 'scoring' => $results, 'totalScore' => $totalScore);
                    }
                }
            }

            $ts = $timer->stop();
            $proctime = sprintf("%d:%02d", $ts['minutes'], $ts['seconds']);

            $this->_writeResults("User:AlexNewArtBot/{$rulename}SearchResult", "User:InceptionBot/NewPageSearch/$rulename/errors",
                $existing, $rulesetresult, $ruleset, $proctime);
        }
    }

    /**
     * Get the existing results page for a rule, excluding old results
     *
     * @param $rulename string Rulename
     * @param $allpages array Pagenames to keep
     * @return array Existing results
     */
    protected function _getExistingResults($rulename, &$allpages)
    {
        $results = $this->mediawiki->getpage("User:AlexNewArtBot/{$rulename}SearchResult");

        $startpos = strpos($results, '*{{');
        if ($startpos === false) return array();

        $existing = array();
        $results = explode("\n", substr($results, $startpos));
        foreach ($results as $line) {
            if (empty($line)) continue;
            if (preg_match('!^\\*(?:\\{\\{la\\||\\[\\[)([^\\]\\}]+)!', $line, $matches)) { // Matches *{{la|...}} or *[[...]]
                $title = $matches[1];
                if (in_array($title, $allpages)) $existing[$title] = $line;
            }
        }

        return $existing;
    }

    protected function _writeResults($resultpage, $logpage, $existingresults, $newresults, RuleSet $ruleset, $proctime)
    {
        $rulename = $ruleset->name;
        usort($newresults, function($a, $b) {
        	return -strnatcmp($a['pageinfo']['timestamp'], $b['pageinfo']['timestamp']); // sort in reverse date order
        });

    	// Result file
    	$linecnt = 0;
    	$output = "This list was generated from [[User:AlexNewArtBot/{$rulename}|these rules]]. Questions and feedback [[User talk:Tedder|are always welcome]]! The search is being run manually, but eventually will run ~daily with the most recent ~14 days of results.

[[User:AlexNewArtBot/{$rulename}SearchResult/archive|AlexNewArtBot archives]] | [[User:TedderBot/NewPageSearch/{$rulename}/archive|TedderBot archives]] | [[User:AlexNewArtBot/{$rulename}|Rules]] | [[User:TedderBot/NewPageSearch/{$rulename}/errors|Match log and errors]]

";
    	foreach ($newresults as $result) {
        	$pageinfo = $result['pageinfo'];
        	// for html htmlentities(title and user, ENT_COMPAT, 'UTF-8')
        	if ($linecnt > 200) $output .= '*[[' . $pageinfo['title'] . ']] by [[User:' . $pageinfo['user'] . ']]';
        	else $output .= '*{{la|' . $pageinfo['title'] . '}} by {{User|' . $pageinfo['user'] . '}}';
        	$output .= ' started on ' . substr($pageinfo['timestamp'], 0, 10) . ', score: ' . $result['totalScore'] . "\n";
        	++$linecnt;
    	}

    	foreach ($existingresults as $line) {
    	    if ($linecnt > 400) break;
    	    $output .= $line . "\n";
        	++$linecnt;
    	}

        $this->resultWriter->writeResults($resultpage, $output);

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

        if (empty($newresults)) {
            $output .= "No pattern matches.\n";
        } else {
        	$output .= "==Scoring notes==\n";

        	foreach ($newresults as $result) {
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

        $this->resultWriter->writeResults($logpage, $output);
    }
}