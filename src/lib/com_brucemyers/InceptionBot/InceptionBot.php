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
use com_brucemyers\MediaWiki\ResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Logger;

class InceptionBot
{
    const LASTRUN = 'InceptionBot.lastrun';
    const HISTORYDAYS = 'InceptionBot.historydays';
    const OUTPUTDIR = 'InceptionBot.outputdir';
    const OUTPUTTYPE = 'InceptionBot.outputtype';
    const RULETYPE = 'InceptionBot.ruletype';
    const CUSTOMRULE = 'InceptionBot.customrule';
    protected $mediawiki;
    protected $resultWriter;

    public function __construct(MediaWiki $mediawiki, $ruleconfigs, $earliestTimestamp, $lastrun, ResultWriter $resultWriter, $latestTimestamp)
    {
        $this->mediawiki = $mediawiki;
        $this->resultWriter = $resultWriter;
        $totaltimer = new Timer();
        $totaltimer->start();
        $errorrulsets = array();

        // Retrieve the rulesets
        $rulesets = array();
        foreach ($ruleconfigs as $rulename => $portal) {
            $rulesets[$rulename] = new RuleSet($rulename, $mediawiki->getpage('User:AlexNewArtBot/' . $rulename));
        }

        // Retrieve the new pages
        $lister = new NewPageLister($mediawiki, $earliestTimestamp, $latestTimestamp);

        $allpages = array();

        while (($pages = $lister->getNextBatch()) !== false) {
            $allpages = array_merge($allpages, $pages);
        }
        Logger::log('New page count = ' . count($allpages));

        $pagenames = array();
        $newestpages = array();
        foreach ($allpages as $newpage) {
            $pagenames[] = $newpage['title'];
            if (strcmp(str_replace(array('-',':','T','Z'), '', $newpage['timestamp']), $lastrun) > 0) $newestpages[] = $newpage['title'];
        }
        Logger::log('Newest page count = ' . count($newestpages));

        $mediawiki->getPagesWithCache($newestpages);

        // Retrieve the pages that have changed since the last run
        $revisions = $mediawiki->getPagesLastRevision($pagenames);

        $updatedpages = array();
        foreach ($revisions as $pagename => $revision) {
            if (in_array($pagename, $newestpages)) continue;
            if (strcmp(str_replace(array('-',':','T','Z'), '', $revision['timestamp']), $lastrun) > 0) $updatedpages[] = $pagename;
        }
        Logger::log('Updated page count = ' . count($updatedpages));

        $mediawiki->getPagesWithCache($updatedpages, true);

        $timer = new Timer();

        // Score new or updated pages
        foreach ($rulesets as $rulename => $ruleset) {
            $timer->start();
            $rulesetresult = array();
            $processor = new RuleSetProcessor($ruleset);
            if (count($ruleset->errors)) $errorrulsets[] = $rulename;

            // Retrieve the existing results
            $existing = $this->_getExistingResults($rulename, $pagenames);

            foreach ($allpages as $newpage) {
                $title = $newpage['title'];
                if (isset($existing[$title])) continue;

                if (in_array($title, $newestpages) || in_array($title, $updatedpages)) {
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

            $this->_writeResults("User:AlexNewArtBot/{$rulename}SearchResult", "User:InceptionBot/NewPageSearch/$rulename/log",
                $existing, $rulesetresult, $ruleset, $proctime);
        }

        $ts = $totaltimer->stop();
        $totaltime = sprintf("%d:%02d:%02d", $ts['hours'], $ts['minutes'], $ts['seconds']);

        $this->_writeStatus(count($rulesets), $errorrulsets, $totaltime, count($allpages), count($newestpages), count($updatedpages));
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

        $dividerno = 0;
        $existing = array();
        $results = explode("\n", substr($results, $startpos));
        foreach ($results as $line) {
            if ($line == '----') {
                $existing[' ' . $dividerno++] = $line;
            } elseif (preg_match('!^\\*(?:\\{\\{la\\||\\[\\[)([^\\]\\}]+)!', $line, $matches)) { // Matches *{{la|...}} or *[[...]]
                $title = $matches[1];
                if (in_array($title, $allpages)) $existing[$title] = $line;
            }
        }

        // Pop trailing dividers
        $lastline = end($existing);
        while (count($existing) && $lastline == '----') {
            array_pop($existing);
            $lastline = end($existing);
        }

        return $existing;
    }

    /**
     * Write a rulesets results and log
     */
    protected function _writeResults($resultpage, $logpage, $existingresults, $newresults, RuleSet $ruleset, $proctime)
    {
        $rulename = $ruleset->name;
    	$errorcnt = count($ruleset->errors);
        usort($newresults, function($a, $b) {
        	return -strnatcmp($a['pageinfo']['timestamp'], $b['pageinfo']['timestamp']); // sort in reverse date order
        });

    	// Result file
    	$linecnt = 0;
    	$logerror = ($errorcnt) ? "Match log and errors" : "Match log";
    	$output = "<noinclude>{{fmbox|text= This is a new articles list for a Portal or WikiProject. See \"What links here\" for the Portal or WikiProject. See [[User:AlexNewArtBot|AlexNewArtBot]] for more information.}}</noinclude>This list was generated from [[User:AlexNewArtBot/{$rulename}|these rules]]. Questions and feedback [[User talk:Bamyers99|are always welcome]]! The search is being run daily with the most recent ~14 days of results. ''Note: Some articles may not be relevant to this project.''

[[User:AlexNewArtBot/{$rulename}|Rules]] | [[User:InceptionBot/NewPageSearch/{$rulename}/log|$logerror]] | Last updated: {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)

";
    	foreach ($newresults as $result) {
        	$pageinfo = $result['pageinfo'];
        	// for html htmlentities(title and user, ENT_COMPAT, 'UTF-8')
        	if ($linecnt > 300) $output .= '*[[' . $pageinfo['title'] . ']] ([[Talk:' . $pageinfo['title'] . '|talk]]) by [[User:' . $pageinfo['user'] . '|' . $pageinfo['user'] . ']]';
        	else $output .= '*{{la|' . $pageinfo['title'] . '}} by {{User|' . $pageinfo['user'] . '}}';
        	$output .= ' started on ' . substr($pageinfo['timestamp'], 0, 10) . ', score: ' . $result['totalScore'] . "\n";
        	++$linecnt;
    	}

    	if (! empty($newresults) && ! empty($existingresults)) $output .= "----\n";

    	foreach ($existingresults as $line) {
    	    if ($linecnt > 300) {
                if (preg_match('!^\\*(?:\\{\\{la\\||\\[\\[)([^\\]\\}]+)[\\]\\}]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by (?:\\{\\{User\\||\\[\\[User:[^\\|]+\\|)([^\\]\\}]+)[\\]\\}]+(.*)!', $line, $matches)) {
                    $title = $matches[1];
                    $user = $matches[2];
                    $rest = $matches[3];
                    $output .= '*[[' . $title . ']] ([[Talk:' . $title . '|talk]]) by [[User:' . $user . '|' . $user . ']]' . $rest . "\n";
                }
    	    } else {
    	        $output .= $line . "\n";
    	    }
        	++$linecnt;
    	}

    	$artcnt = count($newresults);
        $this->resultWriter->writeResults($resultpage, $output, "most recent results, $artcnt articles");

    	// Log file
    	$output = '';

    	$rulecnt = count($ruleset->rules);
    	$threshold = $ruleset->minScore;
    	$output .= "<noinclude>{{fmbox|text= This is a new articles log for a Portal or WikiProject. See \"What links here\" for the Portal or WikiProject. See [[User:AlexNewArtBot|AlexNewArtBot]] for more information.}}</noinclude>Pattern count: $rulecnt &mdash; Error count: $errorcnt &mdash; Threshold: $threshold &mdash; Processing time: $proctime\n";
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

    	$logsum = ($errorcnt) ? "most recent errors and scoring" : "most recent scoring";
        $this->resultWriter->writeResults($logpage, $output, "$logsum");
    }

    /**
     * Write the bot status page
     */
    protected function _writeStatus($rulesetcnt, $errorrulsets, $totaltime, $allpagecnt, $newestpagecnt, $updatedpagecnt)
    {
        $errcnt = count($errorrulsets);
        $allpagecnt = number_format($allpagecnt);
        $newestpagecnt = number_format($newestpagecnt);
        $updatedpagecnt = number_format($updatedpagecnt);
        $output = <<<EOT
'''Last run:''' {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)<br />
'''Processing time:''' $totaltime<br />
'''Project count:''' $rulesetcnt<br />
'''New pages (14 days):''' $allpagecnt<br />
'''New pages (past day):''' $newestpagecnt<br />
'''Updated new pages (past day):''' $updatedpagecnt<br />
'''Rule errors:''' $errcnt
EOT;

    	if ($errcnt) {
    	    $output .= "\n===Rule errors===\n";
    	    foreach ($errorrulsets as $rulename) {
    	        $output .= "*$rulename ([[User:AlexNewArtBot/$rulename|Rules]] | [[User:InceptionBot/NewPageSearch/$rulename/log|Log]])\n";
    	    }
    	}

        $this->resultWriter->writeResults('User:InceptionBot/Status', $output, "$errcnt errors; Total time: $totaltime");
    }
}