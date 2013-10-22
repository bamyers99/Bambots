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
    public function __construct(MediaWiki $mediawiki, $ruleconfigs, $earliestTimestamp, ResultWriter $resultWriter)
    {
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
        $timer = new Timer();

        // Score the pages
        foreach ($rulesets as $rulename => $ruleset) {
            $timer->start();
            $rulesetresult = array();
            $processor = new RuleSetProcessor($ruleset);

            foreach ($allpages as $newpage) {
                $data = $mediawiki->getPageWithCache($newpage['title']);
                $results = $processor->processData($data);

                $totalScore = 0;
                foreach ($results as $result) {
                    $totalScore += $result['score'];
                }

                if ($totalScore >= $ruleset->minScore) {
                    $rulesetresult[] = array('pageinfo' => $newpage, 'scoring' => $results, 'totalScore' => $totalScore);
                }
            }

            $ts = $timer->stop();
            $proctime = sprintf("%d:%02d", $ts['minutes'], $ts['seconds']);

            $resultWriter->writeResults("User:AlexNewArtBot/{$rulename}SearchResult", "User:InceptionBot/NewPageSearch/$rulename/errors",
                $rulesetresult, $ruleset, $proctime);
        }

    }
}