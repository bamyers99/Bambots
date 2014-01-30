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
use com_brucemyers\Util\Config;

class InceptionBot
{
    const LASTRUN = 'InceptionBot.lastrun';
    const HISTORYDAYS = 'InceptionBot.historydays';
    const OUTPUTDIR = 'InceptionBot.outputdir';
    const OUTPUTTYPE = 'InceptionBot.outputtype';
    const RULETYPE = 'InceptionBot.ruletype';
    const CUSTOMRULE = 'InceptionBot.customrule';
    const ERROREMAIL = 'InceptionBot.erroremail';
    const CURRENTPROJECT = 'InceptionBot.currentproject';
    const CURRENTEND = 'InceptionBot.currentend';
    protected $mediawiki;
    protected $resultWriter;
    protected $existingResultParser;

    public function __construct(MediaWiki $mediawiki, $ruleconfigs, $earliestTimestamp, $lastrun, ResultWriter $resultWriter, $latestTimestamp)
    {
        $this->mediawiki = $mediawiki;
        $this->resultWriter = $resultWriter;
        $this->existingResultParser = new ExistingResultParser();
        $totaltimer = new Timer();
        $totaltimer->start();
        $errorrulsets = array();
        $creators =  array();
        $startProject = Config::get(self::CURRENTPROJECT);

        // Retrieve the rulesets
        $rulesets = array();
        foreach ($ruleconfigs as $rulename => $portal) {
            $rulesets[$rulename] = new RuleSet($rulename, $mediawiki->getpage('User:AlexNewArtBot/' . $rulename));
        }

        // Retrieve the new pages in namespaces: Article, Template, Category, Draft
        $lister = new NewPageLister($mediawiki, $earliestTimestamp, $latestTimestamp, '0|10|14|118');

        $temppages = array();

        while (($pages = $lister->getNextBatch()) !== false) {
            $temppages = array_merge($temppages, $pages);
        }

        // Get rid of duplicates
        $allpages = array();
        foreach ($temppages as &$newpage) {
            $allpages[$newpage['title']] = $newpage;
        }
        unset($newpage);

        unset($temppages); // Free-up the memory

        Logger::log('New page count = ' . count($allpages));

        // Rename moved pages
        $movedpagecnt = 0;
        $oldtitles = array();
        $targetns = array('0','118'); // Article, Draft
        $lister = new MovedPageLister($mediawiki, $earliestTimestamp, $latestTimestamp);

        while (($movedpages = $lister->getNextBatch()) !== false) {
        	foreach ($movedpages as &$movedpage) {
                if (! in_array($movedpage['oldns'], $targetns) || ! in_array($movedpage['newns'], $targetns)) continue;
                $oldtitle = $movedpage['oldtitle'];

                if (isset($allpages[$oldtitle])) {
                    $newtitle = $movedpage['newtitle'];
                    $temppage = $allpages[$oldtitle];
                    $temppage['title'] = $newtitle;
                    $temppage['ns'] = $movedpage['newns'];

                    if (! isset($temppage['oldtitles'])) $temppage['oldtitles'] = array();
                    $temppage['oldtitles'][] = $oldtitle;

                    unset($allpages[$oldtitle]);
                    $allpages[$newtitle] = $temppage;

                    $oldtitles[] = $oldtitle;
                    ++$movedpagecnt;
                }
        	}
        }
        unset($movedpage);

        Logger::log("Moved page count = $movedpagecnt");

        $pagenames = array();
        $newestpages = array();
        foreach ($allpages as &$newpage) {
            $creator = $newpage['user'];
            if (isset($creators[$creator])) ++$creators[$creator];
            else $creators[$creator] = 1;
            $pagenames[] = $newpage['title'];
            if (strcmp(str_replace(array('-',':','T','Z'), '', $newpage['timestamp']), $lastrun) > 0) $newestpages[] = $newpage['title'];
        }
        unset($newpage);
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
            if (! empty($startProject) && $rulename != $startProject) continue;
            $startProject = '';
            Config::set(self::CURRENTPROJECT, $rulename, true);

            $timer->start();
            $rulesetresult = array();
            $processor = new RuleSetProcessor($ruleset);
            if (count($ruleset->errors)) $errorrulsets[] = $rulename;

            // Retrieve the existing results
            $deletedexistingcnt = 0;
            $existing = $this->_getExistingResults($rulename, $pagenames, $deletedexistingcnt, $oldtitles);

            foreach ($allpages as &$newpage) {
                $title = $newpage['title'];
                if ($this->_inExisting($existing, $title)) continue;
                if (isset($newpage['oldtitles'])) {
                    foreach ($newpage['oldtitles'] as $oldtitle) {
                        if ($this->_inExisting($existing, $oldtitle)) continue 2;
                    }
                }

                if (in_array($title, $newestpages) || in_array($title, $updatedpages)) {
                    $data = $mediawiki->getPageWithCache($title);
                    $results = $processor->processData($data);

                    $totalScore = 0;
                    foreach ($results as &$result) {
                        $totalScore += $result['score'];
                    }
                    unset($result);

                    if ($totalScore >= $ruleset->minScore) {
                        $rulesetresult[] = array('pageinfo' => $newpage, 'scoring' => $results, 'totalScore' => $totalScore);
                    }
                }
            }
            unset($newpage);

            $ts = $timer->stop();
            $proctime = sprintf("%d:%02d", $ts['minutes'], $ts['seconds']);

            $this->_writeResults("User:AlexNewArtBot/{$rulename}SearchResult", "User:InceptionBot/NewPageSearch/$rulename/log",
                $existing, $rulesetresult, $ruleset, $proctime, $earliestTimestamp, $creators, $deletedexistingcnt);
        }

        Config::set(self::CURRENTPROJECT, '', true);

        $ts = $totaltimer->stop();
        $totaltime = sprintf("%d:%02d:%02d", $ts['hours'], $ts['minutes'], $ts['seconds']);

        $this->_writeStatus(count($rulesets), $errorrulsets, $totaltime, count($allpages), count($newestpages), count($updatedpages),
                        count($creators));

        $this->_writeCreators($creators, $earliestTimestamp);
    }

    /**
     * Is title in existing results
     *
     * @param $existing array of arrays of existing titles
     * @param $title string Title to check
     * @return bool Title in results
     */
    protected function _inExisting(&$existing, $title)
    {
        foreach ($existing as &$section) {
            if (isset($section[$title])) return true;
        }

        return false;
    }

    /**
     * Get the existing results page for a rule, excluding old results
     *
     * @param $rulename string Rulename
     * @param $allpages array Pagenames to keep
     * @param $deletedcnt int (write only) Deleted existing results count
     * @param $oldtitles array Old page titles
     * @return array Existing results
     */
    protected function _getExistingResults($rulename, &$allpages, &$deletedcnt, &$oldtitles)
    {
        $deletedcnt = 0;
        $results = $this->mediawiki->getpage("User:AlexNewArtBot/{$rulename}SearchResult");

        $results = $this->existingResultParser->parsePage($results);

        foreach ($results as $sectionno => $section) {
            foreach ($section as $lineno => $line) {
                $title = $line['title'];
                if (! in_array($title, $allpages) && ! in_array($title, $oldtitles)) {
                    unset($results[$sectionno][$lineno]);
                    ++$deletedcnt;
                }
            }

            // Delete an empty section
            if (empty($results[$sectionno])) unset($results[$sectionno]);
        }

        return $results;
    }

    /**
     * Write a rulesets results and log
     */
    protected function _writeResults($resultpage, $logpage, &$existingresults, $newresults, RuleSet $ruleset, $proctime, $earliestTimestamp,
       $creators, $deletedexistingcnt)
    {
        if (count($newresults) == 0) return;

        $rulename = $ruleset->name;
    	$errorcnt = count($ruleset->errors);
        usort($newresults, function($a, $b) {
            $ans = $a['pageinfo']['ns'];
            $bns = $b['pageinfo']['ns'];
            if ($ans == 118) $ans = 0; // Sort drafts with articles
            if ($bns == 118) $bns = 0;

            if ($ans < $bns) return -1;
            if ($ans > $bns) return 1;

            return -strnatcmp($a['pageinfo']['timestamp'], $b['pageinfo']['timestamp']); // sort in reverse date order
        });

    	// Result file
    	$linecnt = 0;
    	$logerror = ($errorcnt) ? "Match log and errors" : "Match log";
    	$output = "<noinclude>__NOINDEX__\n{{fmbox|text= This is a new articles list for a Portal or WikiProject. See \"What links here\" for the Portal or WikiProject. See [[User:AlexNewArtBot|AlexNewArtBot]] for more information.}}</noinclude>This list was generated from [[User:AlexNewArtBot/{$rulename}|these rules]]. Questions and feedback [[User talk:Bamyers99|are always welcome]]! The search is being run daily with the most recent ~14 days of results. ''Note: Some articles may not be relevant to this project.''

[[User:AlexNewArtBot/{$rulename}|Rules]] | [[User:InceptionBot/NewPageSearch/{$rulename}/log|$logerror]] <includeonly>| [[$resultpage|Results page]] (for watching) </includeonly>| Last updated: {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)

";

    	// Determine suppressed namespaces
    	$suppressedNS = array();
    	if (isset($ruleset->options['SuppressNS'])) {
            foreach ($ruleset->options['SuppressNS'] as $snsoption) {
                if ($snsoption == 'Category') $suppressedNS[] = '14';
                elseif (($snsoption == 'Draft')) $suppressedNS[] = '118';
                elseif (($snsoption == 'Template')) $suppressedNS[] = '10';
            }
    	}

    	if (! empty($newresults)) $output .= "<ul>\n";

    	foreach ($newresults as $result) {
        	$pageinfo = $result['pageinfo'];
        	$displayuser = $pageinfo['user'];
        	$ns = $pageinfo['ns'];
            $user = str_replace(' ', '_', $displayuser);
            $urlencodeduser = urlencode($displayuser);
            $newpagecnt = $creators[$displayuser];
        	// for html htmlentities(title and user, ENT_COMPAT, 'UTF-8')

        	if ($ns != 0) $output .= '{{User:AlexNewArtBot/MaintDisplay|<li>{{pagelinks|' . $pageinfo['title'] . "}} by [[User:$user{{!}}$displayuser]] (<span class{{=}}\"plainlinks\">[[User_talk:$user{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}$urlencodeduser&days{{=}}14 new pages &#40;$newpagecnt&#41;]</span>)";
        	elseif ($linecnt > 600) $output .= '<li>[[' . $pageinfo['title'] . ']] ([[Talk:' . $pageinfo['title'] . '|talk]]) by [[User:' . $user . '|' . $displayuser . ']]';
        	else $output .= '<li>{{la|' . $pageinfo['title'] . "}} by [[User:$user|$displayuser]] (<span class=\"plainlinks\">[[User_talk:$user|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=$urlencodeduser&days=14 new pages &#40;$newpagecnt&#41;]</span>)";

        	$output .= ' started on ' . substr($pageinfo['timestamp'], 0, 10) . ', score: ' . $result['totalScore'] . '</li>';
        	if ($ns != 0) {
        	    $wikipediaNS = '1';
        	    if (in_array($ns, $suppressedNS)) $wikipediaNS = '0';
        	    $output .= "|$wikipediaNS}}";
        	}
        	$output .= "\n";
        	++$linecnt;
    	}

    	if (! empty($newresults)) $output .= "</ul>\n";
    	if (! empty($newresults) && ! empty($existingresults)) $output .= "----\n";
    	$existingcnt = 0;
    	$sectioncnt = 0;

    	foreach ($existingresults as $section) {
    	    if ($sectioncnt++ > 0) $output .= "----\n";
    	    $output .= "<ul>\n";

    	    foreach ($section as $line) {
        	    ++$existingcnt;
                $title = $line['title'];
                $displayuser = $line['user'];
                $user = str_replace(' ', '_', $displayuser);
                $urlencodeduser = urlencode($displayuser);
                $timestamp = $line['timestamp'];
                $totalScore = $line['totalScore'];
                $wikipediaNS = $line['WikipediaNS'];
                if (isset($creators[$displayuser])) $newpagecnt = $creators[$displayuser];
                else $newpagecnt = '0'; // Moved new page, with a change of/or no creator

        	    if ($linecnt > 600 && $line['type'] != 'MD') {
                    $output .= "<li>[[$title]] ([[Talk:$title|talk]]) by [[User:$user|$displayuser]] started on $timestamp, score: $totalScore</li>\n";
        	    } elseif ($linecnt > 600 && $line['type'] == 'MD') {
                    $output .= "{{User:AlexNewArtBot/MaintDisplay|<li>[[:$title]] by [[User:$user{{!}}$displayuser]] started on $timestamp, score: $totalScore</li>|$wikipediaNS}}\n";
        	    } elseif ($line['type'] == 'MD') {
                    $output .= "{{User:AlexNewArtBot/MaintDisplay|<li>{{pagelinks|$title}} by [[User:$user{{!}}$displayuser]] (<span class{{=}}\"plainlinks\">[[User_talk:$user{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}$urlencodeduser&days{{=}}14 new pages &#40;$newpagecnt&#41;]</span>) started on $timestamp, score: $totalScore</li>|$wikipediaNS}}\n";
        	    } else {
                    $output .= "<li>{{la|$title}} by [[User:$user|$displayuser]] (<span class=\"plainlinks\">[[User_talk:$user|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=$urlencodeduser&days=14 new pages &#40;$newpagecnt&#41;]</span>) started on $timestamp, score: $totalScore</li>\n";
                }
            	++$linecnt;
    	    }

    	    $output .= "</ul>\n";
    	}

    	$artcnt = count($newresults);
    	$totalcnt = $artcnt + $existingcnt;
    	if ($artcnt > 0 && $deletedexistingcnt > 0) $msg = "added $artcnt, removed $deletedexistingcnt";
    	elseif ($artcnt > 0) $msg = "added $artcnt";
    	else $msg = "removed $deletedexistingcnt";

        $this->resultWriter->writeResults($resultpage, $output, "most recent results, $msg, total $totalcnt");

    	// Log file
    	$output = '';

    	$rulecnt = count($ruleset->rules);
    	$threshold = $ruleset->minScore;
    	$output .= "<noinclude>__NOINDEX__\n{{fmbox|text= This is a new articles log for a Portal or WikiProject. See \"What links here\" for the Portal or WikiProject. See [[User:AlexNewArtBot|AlexNewArtBot]] for more information.}}</noinclude>Pattern count: $rulecnt &mdash; Error count: $errorcnt &mdash; Threshold: $threshold &mdash; Processing time: $proctime\n";
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

        	    $ns = $result['pageinfo']['ns'];

        	    if ($ns != 0) $output .= '*[[:' . $result['pageinfo']['title'] . "]] Total score: $totscore\n";
        	    else$output .= '*[[' . $result['pageinfo']['title'] . "]] Total score: $totscore\n";

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
    protected function _writeStatus($rulesetcnt, $errorrulsets, $totaltime, $allpagecnt, $newestpagecnt, $updatedpagecnt, $creatorcnt)
    {
        $errcnt = count($errorrulsets);
        $allpagecnt = number_format($allpagecnt);
        $newestpagecnt = number_format($newestpagecnt);
        $updatedpagecnt = number_format($updatedpagecnt);
        $output = <<<EOT
<noinclude>__NOINDEX__</noinclude>
'''Last run:''' {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)<br />
'''Processing time:''' $totaltime<br />
'''Project count:''' $rulesetcnt<br />
'''New pages (14 days):''' $allpagecnt<br />
'''New pages (past day):''' $newestpagecnt<br />
'''Updated new pages (past day):''' $updatedpagecnt<br />
'''Page creators:''' [[User:InceptionBot/Creators|$creatorcnt]]<br />
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

    /**
     * Write creators page
     */
    protected function _writeCreators($creators, $earliestTimestamp)
    {
        arsort($creators);
        $creatorcnt = count($creators);
        preg_match('!^(\d{4})(\d{2})(\d{2})!', $earliestTimestamp, $matches);
        $etyear = $matches[1];
        $etmonth = $matches[2];
        $etday = $matches[3];

        $output = "<noinclude>__NOINDEX__</noinclude>\nNew page creators (10+ pages) since $etyear-$etmonth-$etday.\n\n{| class=\"wikitable sortable\"\n|-\n!User !! Page count\n";

        foreach ($creators as $displayuser => $pagecnt) {
            if ($pagecnt < 10) continue;
            $user = str_replace(' ', '_', $displayuser);
            $urlencodeduser = urlencode($displayuser);
            $output .= "|-\n|[[User:$user|$displayuser]] ([[User_talk:$user|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user|contribs]]&nbsp;'''&#183;'''&#32;<span class=\"plainlinks\">[https://tools.wmflabs.org/bambots/UserNewPages.php?user=$urlencodeduser&days=14 new pages]</span>) || $pagecnt\n";
        }

        $output .= "|}\n";

        $this->resultWriter->writeResults('User:InceptionBot/Creators', $output, "$creatorcnt creators");
    }
}