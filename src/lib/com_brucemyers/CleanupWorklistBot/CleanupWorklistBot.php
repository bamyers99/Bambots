<?php
/**
 Copyright 2014 Myers Enterprises II

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

namespace com_brucemyers\CleanupWorklistBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\MediaWiki\ResultWriter;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\Config;

class CleanupWorklistBot
{
    const OUTPUTDIR = 'CleanupWorklistBot.outputdir';
    const OUTPUTTYPE = 'CleanupWorklistBot.outputtype';
    const RULETYPE = 'CleanupWorklistBot.ruletype';
    const CUSTOMRULE = 'CleanupWorklistBot.customrule';
    const ERROREMAIL = 'CleanupWorklistBot.erroremail';
    const CURRENTPROJECT = 'CleanupWorklistBot.currentproject';
    protected $mediawiki;
    protected $resultWriter;

    public function __construct(MediaWiki $mediawiki, $ruleconfigs, ResultWriter $resultWriter)
    {
        $this->mediawiki = $mediawiki;
        $this->resultWriter = $resultWriter;
        $totaltimer = new Timer();
        $totaltimer->start();
        $startProject = Config::get(self::CURRENTPROJECT);

    }

    /**
     * Write a rulesets results
     */
    protected function _writeResults($resultpage, RuleSet $ruleset, $proctime)
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
            if (isset($creators[$displayuser])) $newpagecnt = $creators[$displayuser];
            else $newpagecnt = '0'; // Moved new page, with a change of/or no creator
        	// for html htmlentities(title and user, ENT_COMPAT, 'UTF-8')

            $sanitized_title = str_replace('=', '&#61;', $pageinfo['title']);

        	if ($ns != 0) $output .= '{{User:AlexNewArtBot/MaintDisplay|<li>{{pagelinks|' . $sanitized_title . "}} by [[User:$user{{!}}$displayuser]] (<span class{{=}}\"plainlinks\">[[User_talk:$user{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}$urlencodeduser&days{{=}}14 new pages &#40;$newpagecnt&#41;]</span>)";
        	elseif ($linecnt > 600) $output .= '<li>[[' . $pageinfo['title'] . ']] ([[Talk:' . $pageinfo['title'] . '|talk]]) by [[User:' . $user . '|' . $displayuser . ']]';
        	else $output .= '<li>{{la|' . $sanitized_title . "}} by [[User:$user|$displayuser]] (<span class=\"plainlinks\">[[User_talk:$user|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=$urlencodeduser&days=14 new pages &#40;$newpagecnt&#41;]</span>)";

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

                $sanitized_title = str_replace('=', '&#61;', $title);

        	    if ($linecnt > 600 && $line['type'] != 'MD') {
                    $output .= "<li>[[$title]] ([[Talk:$title|talk]]) by [[User:$user|$displayuser]] started on $timestamp, score: $totalScore</li>\n";
        	    } elseif ($linecnt > 600 && $line['type'] == 'MD') {
                    $output .= "{{User:AlexNewArtBot/MaintDisplay|<li>[[:$title]] by [[User:$user{{!}}$displayuser]] started on $timestamp, score: $totalScore</li>|$wikipediaNS}}\n";
        	    } elseif ($line['type'] == 'MD') {
                    $output .= "{{User:AlexNewArtBot/MaintDisplay|<li>{{pagelinks|$sanitized_title}} by [[User:$user{{!}}$displayuser]] (<span class{{=}}\"plainlinks\">[[User_talk:$user{{!}}talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user{{!}}contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user{{=}}$urlencodeduser&days{{=}}14 new pages &#40;$newpagecnt&#41;]</span>) started on $timestamp, score: $totalScore</li>|$wikipediaNS}}\n";
        	    } else {
                    $output .= "<li>{{la|$sanitized_title}} by [[User:$user|$displayuser]] (<span class=\"plainlinks\">[[User_talk:$user|talk]]&nbsp;'''&#183;'''&#32;[[Special:Contributions/$user|contribs]]&nbsp;'''&#183;'''&#32;[https://tools.wmflabs.org/bambots/UserNewPages.php?user=$urlencodeduser&days=14 new pages &#40;$newpagecnt&#41;]</span>) started on $timestamp, score: $totalScore</li>\n";
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
    protected function _writeStatus($rulesetcnt, $totaltime)
    {
        $output = <<<EOT
<noinclude>__NOINDEX__</noinclude>
'''Last run:''' {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)<br />
'''Processing time:''' $totaltime<br />
'''Project count:''' $rulesetcnt<br />
EOT;

        $this->resultWriter->writeResults('User:CleanupWorklistBot/Status', $output, "Total time: $totaltime");
    }
}