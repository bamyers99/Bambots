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
    const EXISTINGREGEX = '!^\\*(?:\\{\\{la\\||\\[\\[)([^\\]\\}]+)[\\]\\}]+\\s*(?:\\([^\\]]+\\]\\]\\))?\\s*by\\s*(?:\\{\\{User\\||\\[\\[User:[^\\|]+\\|)([^\\]\\}]+)[\\]\\}]+(?:\\s*\\([^\\)]+\\))?(.*)!';
    protected $mediawiki;
    protected $resultWriter;

    public function __construct(MediaWiki $mediawiki, $ruleconfigs, $earliestTimestamp, $lastrun, ResultWriter $resultWriter, $latestTimestamp)
    {
        $this->mediawiki = $mediawiki;
        $this->resultWriter = $resultWriter;
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
        foreach ($temppages as $newpage) {
            $allpages[$newpage['title']] = $newpage;
        }

        unset($temppages); // Free-up the memory

        Logger::log('New page count = ' . count($allpages));

        // Rename moved pages
        $movedpagecnt = 0;
        $oldtitles = array();
        $targetns = array('0','118'); // Article, Draft
        $lister = new MovedPageLister($mediawiki, $earliestTimestamp, $latestTimestamp);

        while (($movedpages = $lister->getNextBatch()) !== false) {
        	foreach ($movedpages as $movedpage) {
                if (! in_array($movedpage['oldns'], $targetns) || ! in_array($movedpage['newns'], $targetns)) continue;
                $oldtitle = $movedpage['oldtitle'];

                if (isset($allpages[$oldtitle])) {
                    $newtitle = $movedpage['newtitle'];
                    $temppage = $allpages[$oldtitle];
                    $temppage['title'] = $newtitle;

                    if (! isset($temppage['oldtitles'])) $temppage['oldtitles'] = array();
                    $temppage['oldtitles'][] = $oldtitle;

                    unset($allpages[$oldtitle]);
                    $allpages[$newtitle] = $temppage;

                    $oldtitles[] = $oldtitle;
                    ++$movedpagecnt;
                }
        	}
        }

        Logger::log("Moved page count = $movedpagecnt");

        $pagenames = array();
        $newestpages = array();
        foreach ($allpages as $newpage) {
            $creator = $newpage['user'];
            if (isset($creators[$creator])) ++$creators[$creator];
            else $creators[$creator] = 1;
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

            foreach ($allpages as $newpage) {
                $title = $newpage['title'];
                if (isset($existing[$title])) continue;
                if (isset($newpage['oldtitles'])) {
                    foreach ($newpage['oldtitles'] as $oldtitle) {
                        if (isset($existing[$oldtitle])) continue 2;
                    }
                }

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

        $startpos = strpos($results, '*{{');
        if ($startpos === false) return array();

        $dividerno = 0;
        $prevline = '';
        $existing = array();
        $results = explode("\n", substr($results, $startpos));
        foreach ($results as $line) {
            if ($line == '----' && $prevline != '----') {
                $existing[' ' . $dividerno++] = $line;
            } elseif (preg_match('!^(?:\\*\\{\\{la\\||\\*\\[\\[|\\{\\{User:AlexNewArtBot/MaintDisplay\\|\\*\\{\\{pagelinks\\|)([^\\]\\}]+)!', $line, $matches)) { // Matches *{{la|...}} or *[[...]] or {{User:AlexNewArtBot/MaintDisplay|*{{pagelinks|...}}
                $title = $matches[1];
                if (in_array($title, $allpages) || in_array($title, $oldtitles)) $existing[$title] = $line;
                else ++$deletedcnt;
            }
            $prevline = $line;
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
    protected function _writeResults($resultpage, $logpage, $existingresults, $newresults, RuleSet $ruleset, $proctime, $earliestTimestamp,
       &$creators, $deletedexistingcnt)
    {
        if (count($newresults) == 0) return;

        $rulename = $ruleset->name;
    	$errorcnt = count($ruleset->errors);
        usort($newresults, function($a, $b) {
        	return -strnatcmp($a['pageinfo']['timestamp'], $b['pageinfo']['timestamp']); // sort in reverse date order
        });

    	// Result file
    	$linecnt = 0;
    	$logerror = ($errorcnt) ? "Match log and errors" : "Match log";
    	$output = "<noinclude>__NOINDEX__\n{{fmbox|text= This is a new articles list for a Portal or WikiProject. See \"What links here\" for the Portal or WikiProject. See [[User:AlexNewArtBot|AlexNewArtBot]] for more information.}}</noinclude>This list was generated from [[User:AlexNewArtBot/{$rulename}|these rules]]. Questions and feedback [[User talk:Bamyers99|are always welcome]]! The search is being run daily with the most recent ~14 days of results. ''Note: Some articles may not be relevant to this project.''

[[User:AlexNewArtBot/{$rulename}|Rules]] | [[User:InceptionBot/NewPageSearch/{$rulename}/log|$logerror]] <includeonly>| [[$resultpage|Results page]] (for watching) </includeonly>| Last updated: {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)

";
    	foreach ($newresults as $result) {
        	$pageinfo = $result['pageinfo'];
        	$displayuser = $pageinfo['user'];
        	$ns = $pageinfo['ns'];
            $user = str_replace(' ', '_', $displayuser);
            $urlencodeduser = urlencode($displayuser);
            $newpagecnt = $creators[$displayuser];
        	// for html htmlentities(title and user, ENT_COMPAT, 'UTF-8')

        	if ($ns != 0) $output .= '{{User:AlexNewArtBot/MaintDisplay|*{{pagelinks|' . $pageinfo['title'] . "}} by [[User:$user{{!}}$displayuser]] (<span class{{=}}\"plainlinks\">[[User_talk:$user{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}$urlencodeduser&days{{=}}14 new pages &#40;$newpagecnt&#41;]</span>)";
        	elseif ($linecnt > 600) $output .= '*[[' . $pageinfo['title'] . ']] ([[Talk:' . $pageinfo['title'] . '|talk]]) by [[User:' . $user . '|' . $displayuser . ']]';
        	else $output .= '*{{la|' . $pageinfo['title'] . "}} by [[User:$user|$displayuser]] (<span class=\"plainlinks\">[[User_talk:$user|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=$urlencodeduser&days=14 new pages &#40;$newpagecnt&#41;]</span>)";

        	$output .= ' started on ' . substr($pageinfo['timestamp'], 0, 10) . ', score: ' . $result['totalScore'];
        	if ($ns != 0) $output .= '}}';
        	$output .= "\n";
        	++$linecnt;
    	}

    	if (! empty($newresults) && ! empty($existingresults) && reset($existingresults) != '----') $output .= "----\n";
    	$existingcnt = 0;

    	foreach ($existingresults as $line) {
    	    if ($line != '----') ++$existingcnt;
    	    if ($linecnt > 600) {
                if (preg_match(self::EXISTINGREGEX, $line, $matches)) {
                    $title = $matches[1];
                    $displayuser = $matches[2];
                    $user = str_replace(' ', '_', $displayuser);
                    $rest = $matches[3];
                    $output .= '*[[' . $title . ']] ([[Talk:' . $title . '|talk]]) by [[User:' . $user . '|' . $displayuser . ']]' . $rest . "\n";
                }
    	    } else {
    	        $output .= $line . "\n";
    	    }
        	++$linecnt;
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