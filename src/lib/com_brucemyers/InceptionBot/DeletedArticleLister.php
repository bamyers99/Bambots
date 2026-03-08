<?php
/**
 Copyright 2026 Myers Enterprises II

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

class DeletedArticleLister
{
    protected $mediawiki;

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki
     */
    public function __construct($mediawiki)
    {
        $this->mediawiki = $mediawiki;
    }

    /**
     * Get redlinks
     *
     * @param $project string Project
     * @param $month Month - format: YYYY-MM
     * @return array key = pagename (string), value = has draft (boolean)
     */
    public function getRedLinks($project, $month)
    {
        
        // Get the list of edits for the month
        $starttime = "$month-01T00:00:00Z";
        $enddate = date("Y-m-t", strtotime($month . '-01'));
        $endtime = "{$enddate}T23:59:59Z";
        
        $params = [
            'titles' => 'User:AlexNewArtBot/' . $project . 'SearchResult',
            'rvstart' => $starttime,
            'rvend' => $endtime,
            'rvdir' => 'newer',
            'rvlimit' => 31
        ];
        
        $revs = $this->mediawiki->getProp('revisions', $params);
        
        // Get 3 revisions that cover the month, each revision has 14 days
        $revtimes = [
            "$month-13T00:00:00Z",
            "$month-26T00:00:00Z",
            "{$enddate}T00:00:00Z" // eom
            ];
        $revids = [];
        $nextrev = 0;
        
        foreach ($revs['query']['pages'] as $page) {
            foreach ($page['revisions'] as $rev) {
                if ($rev['timestamp'] > $revtimes[$nextrev]) {
                    $revids[] = $rev['revid'];
                    ++$nextrev;
                    if ($nextrev == 3) break;
                }
            }
        }
        
        if (empty($revids) && count($revs) > 0) {
            $lastrev = 0;
            
            foreach ($revs['query']['pages'] as $page) {
                foreach ($page['revisions'] as $rev) {
                    $lastrev = $rev['revid'];
                }
            }
            
            $revids[] = $lastrev;
        }
        
        // Get the revisions text links
        $pageparser = new ExistingResultParser();
        
        $revstext = $this->mediawiki->getRevisionsText($revids);
        $pagelinks = [];
        
        foreach ($revstext as $pagerevs) {
            array_shift($pagerevs); // ns
            
            while (count($pagerevs)) {
                array_shift($pagerevs); // revid
                $revtext = array_shift($pagerevs);
                $parsedlinks = $pageparser->parsePage($revtext);
                
                foreach ($parsedlinks as $links) {
                    foreach (array_keys($links) as $title) {
                        $ns = MediaWiki::getNamespaceId(MediaWiki::getNamespaceName($title));
                        if ($ns != 0 && $ns != 118) continue;
                        $pagelinks[$title] = true; // removes dups
                    }
                }
            }
        }
        
        // Determine red links
        $pagelinks = array_keys($pagelinks);
        $nonredlinks = [];
        
        $revisions = $this->mediawiki->getPagesLastRevision($pagelinks);
        
        foreach (array_keys($revisions) as $pagetitle) {
            $nonredlinks[] = $pagetitle;
        }
        
        $redlinks = array_diff($pagelinks, $nonredlinks);
        
        // Look for drafts
        $draftlinks = [];
        
        foreach ($redlinks as $redlink) {
            if (! str_starts_with($redlink, 'Draft:')) $draftlinks[] = "Draft:$redlink";
        }
        
        $redlinkdrafts = [];
        
        $revisions = $this->mediawiki->getPagesLastRevision($draftlinks);
        
        foreach (array_keys($revisions) as $pagetitle) {
            $redlinkdrafts[] = substr($pagetitle, 6);
        }
        
        $retval = [];
        
        foreach ($redlinks as $redlink) {
            $hasdraft = false;
            if (in_array($redlink, $redlinkdrafts)) $hasdraft = true;
            $retval[$redlink] = $hasdraft;
        }
        
        return $retval;
    }
}