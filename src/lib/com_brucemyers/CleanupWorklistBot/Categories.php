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

use PDO;
use com_brucemyers\Util\Logger;

class Categories
{

    public static $CATEGORIES = [
        // from-monthly
        '1911 Britannica articles needing updates' => [
            'type' => 'from-monthly'
        ],
        'Accuracy disputes' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Accuracy disputes or self-published'
        ],
        'Article sections to be split' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Sections to be split'
        ],
        'Articles about possible neologisms' => [
            'type' => 'from-monthly',
            'display' => 'Possible neologisms'
        ],
        'Articles containing potentially dated statements' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Potentially dated statements'
        ],
        'Articles lacking in-text citations' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Has general references but lacks inline footnotes'
        ],
        'Articles lacking page references' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Footnotes need specific page numbers'
        ],
        'Articles lacking reliable references' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Cites unreliable sources'
        ],
        'Articles lacking sources' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Cites no sources'
        ],
        'Articles needing additional categories' => [
            'type' => 'from-monthly',
            'display' => 'Categories needed'
        ],
        'Articles needing additional references' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Unsourced passages need footnotes {{More citations needed}}'
        ],
        'Articles needing cleanup' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Cleanup needed'
        ],
        'Articles needing expert attention' => [
            'type' => 'from-monthly',
            'group' => 'Clarity',
            'display' => 'Expert attention needed'
        ],
        'Articles with bare URLs for citations' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'Link rot cleanup'
        ],
        'Articles needing more viewpoints' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Viewpoints needed'
        ],
        'Articles needing sections' => [
            'type' => 'from-monthly',
            'display' => 'Sections needed'
        ],
        'Articles needing the year an event occurred' => [
            'type' => 'from-monthly',
            'display' => 'Year an event occurred needed'
        ],
        'Articles requiring tables' => [
            'type' => 'from-monthly',
            'display' => 'Tables needed'
        ],
        'Articles slanted towards recent events' => [
            'type' => 'from-monthly',
            'display' => 'Slanted towards recent events'
        ],
        'Articles sourced by IMDb' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'IMDb sourced'
        ],
        'Articles sourced only by IMDb' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'IMDb only sourced'
        ],
        'Articles that may be too long' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Too long'
        ],
        'Articles that may contain original research' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Original research'
        ],
        'Articles that need to differentiate between fact and fiction' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Fact and fiction differentiation'
        ],
        'Articles to be expanded' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Expansion needed'
        ],
        'Articles to be merged' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Merge needed'
        ],
        'Articles to be split' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Split needed'
        ],
        'Articles with a promotional tone' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Promotional tone'
        ],
        'Articles with broken or outdated citations' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Broken or outdated citations'
        ],
        'Articles with close paraphrasing' => [
            'type' => 'from-monthly',
            'display' => 'Close paraphrasing'
        ],
        'Articles with close paraphrasing of public domain sources' => [
            'type' => 'from-monthly',
            'display' => 'Close paraphrasing of public domain sources'
        ],
        'Articles with dead external links' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'Dead external links {{dead link}}'
        ],
        'Articles with disputed statements' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Disputed statements'
        ],
        'Articles with excessive see also sections' => [
            'type' => 'from-monthly',
            'display' => 'Excessive see also sections'
        ],
        'Articles with failed verification' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Failed verification'
        ],
        'Articles with improper non-free content' => [
            'type' => 'from-monthly',
            'display' => 'Improper non-free content'
        ],
        'Articles with incomplete citations' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Incomplete citations'
        ],
        'Articles with limited geographic scope' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Limited geographic scope'
        ],
        'Articles with links needing disambiguation' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'Links needing disambiguation'
        ],
        'Articles with minor POV problems' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Minor POV problems'
        ],
        'Articles with obsolete information' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Obsolete information'
        ],
        'Articles with peacock terms' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Peacock terms'
        ],
        'Articles with sections that need to be turned into prose' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Prose needed'
        ],
        'Articles with self-published sources' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Accuracy disputes or self-published'
        ],
        'Articles with specifically marked weasel-worded phrases' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Weasel-worded phrases'
        ],
        'Articles with too few wikilinks' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'Wikilinks needed'
        ],
        'Articles with topics of unclear notability' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Notability unclear'
        ],
        'Articles with trivia sections' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Trivia sections'
        ],
        'Articles with unsourced statements' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Unsourced passages need footnotes {{citation needed}}'
        ],
        'Articles with weasel words' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Weasel words'
        ],
        'Autobiographical articles' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality'
        ],
        'BLP articles lacking sources' => [
            'type' => 'from-monthly',
            'group' => 'References'
        ],
        'Copied and pasted articles and sections' => [
            'type' => 'from-monthly'
        ],
        'Copied and pasted articles and sections with url provided' => [
            'type' => 'from-monthly'
        ],
        'Dead-end pages' => [
            'type' => 'from-monthly',
            'group' => 'Links'
        ],
        'Disambiguation pages in need of cleanup' => [
            'type' => 'from-monthly'
        ],
        'Incomplete disambiguation' => [
            'type' => 'from-monthly'
        ],
        'Incomplete lists' => [
            'type' => 'from-monthly',
            'group' => 'Content'
        ],
        'Wikipedia neutral point of view disputes' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality'
        ],
        'Orphaned articles' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'Orphaned'
        ],
        'Pages with excessive dablinks' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'Dablinks excessive'
        ],
        'Recently revised' => [
            'type' => 'from-monthly'
        ],
        'Self-contradictory articles' => [
            'type' => 'from-monthly',
            'group' => 'Clarity'
        ],
        'Uncategorized' => [
            'type' => 'from-monthly'
        ],
        'Unreferenced BLPs' => [
            'type' => 'from-monthly',
            'group' => 'References'
        ],
        'Vague or ambiguous geographic scope' => [
            'type' => 'from-monthly',
            'group' => 'Clarity'
        ],
        'Vague or ambiguous time' => [
            'type' => 'from-monthly',
            'group' => 'Clarity'
        ],
        'Wikipedia articles in need of updating' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Update needed'
        ],
        'Wikipedia articles needing clarification' => [
            'type' => 'from-monthly',
            'group' => 'Clarity',
            'display' => 'Clarification needed'
        ],
        'Wikipedia articles needing context' => [
            'type' => 'from-monthly',
            'group' => 'Clarity',
            'display' => 'Context needed'
        ],
        'Wikipedia articles needing copy edit' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Copy edit needed'
        ],
        'Wikipedia articles needing factual verification' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Factual verification needed'
        ],
        'Wikipedia articles needing page number citations' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Page number citations needed'
        ],
        'Wikipedia articles needing reorganization' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Reorganization needed'
        ],
        'Wikipedia articles needing rewrite' => [
            'type' => 'from-monthly',
            'group' => 'Clarity',
            'display' => 'Rewrite needed'
        ],
        'Wikipedia articles with style issues' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Style editing needed'
        ],
        'Wikipedia articles that are too technical' => [
            'type' => 'from-monthly',
            'group' => 'Clarity',
            'display' => 'Too technical'
        ],
        'Wikipedia articles that may have off-topic sections' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Off-topic sections'
        ],
        'Wikipedia articles with plot summary needing attention' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Plot summary needs attention'
        ],
        'Wikipedia articles with possible conflicts of interest' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Conflict of interest'
        ],
        'Wikipedia external links cleanup' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'External link cleanup {{external links}}'
        ],
        'Wikipedia introduction cleanup' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Introduction cleanup'
        ],
        'Wikipedia list cleanup' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'List cleanup'
        ],
        'Wikipedia pages needing cleanup' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Cleanup needed'
        ],
        'Wikipedia references cleanup' => [
            'type' => 'from-monthly',
            'group' => 'References',
            'display' => 'Reference cleanup'
        ],
        'Wikipedia spam cleanup' => [
            'type' => 'from-monthly',
            'group' => 'Neutrality',
            'display' => 'Spam cleanup'
        ],
        'Wikipedia articles containing buzzwords' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Buzzword cleanup'
        ],
        'Wikipedia articles without plot summaries' => [
            'type' => 'from-monthly',
            'group' => 'Content',
            'display' => 'Plot summary needed'
        ],
        'Wikipedia red link cleanup' => [
            'type' => 'from-monthly',
            'group' => 'Links',
            'display' => 'Red link cleanup'
        ],

        // no-date
        'Accessibility issue tracking categories' => [
            'type' => 'no-date',
            'subcats' => 'only',
            'group' => 'Accessibility'
        ],
        'All articles needing coordinates' => [
            'type' => 'no-date',
            'group' => 'Content',
            'display' => 'Coordinates needed'
        ],
        'All articles needing expert attention' => [
            'type' => 'no-date',
            'group' => 'Clarity',
            'display' => 'Expert attention needed'
        ],
        'Animal articles needing cleanup' => [
            'type' => 'no-date',
            'group' => 'Content'
        ],
        'Articles needing more detailed references' => [
            'type' => 'no-date',
            'group' => 'References',
            'display' => 'Detailed references needed'
        ],
        'Articles with incorrect citation syntax' => [
            'type' => 'no-date',
            'subcats' => 'only',
            'group' => 'References',
            'display' => 'Citation syntax incorrect'
        ],
        'CS1 errors' => [
            'type' => 'no-date',
            'subcats' => 'only',
            'group' => 'References'
        ],
/*        'CS1 maintenance' => [
            'type' => 'no-date',
            'subcats' => 'only',
            'group' => 'References'
        ],
*/
        'Harv and Sfn multiple-target errors' => [
            'type' => 'no-date',
            'group' => 'References'
        ],
        'Harv and Sfn no-target errors' => [
            'type' => 'no-date',
            'group' => 'References'
        ],
        'Invalid conservation status' => [
            'type' => 'no-date'
        ],
        'Missing taxobox' => [
            'type' => 'no-date'
        ],
        'Pages using duplicate arguments in template calls' => [
            'type' => 'no-date',
            'display' => 'Template call duplicate arguments'
        ],
        'Pages with broken anchors' => [
            'type' => 'no-date',
            'group' => 'Links',
            'display' => 'Pages with broken anchors (see article talk page, remove when fixed)'
        ],
        'Pages with reference errors' => [ // Tracks subcats of Pages with citation errors
            'type' => 'no-date',
            'group' => 'References',
            'display' => 'Reference errors'
        ],
        'Plant articles needing a taxobox' => [
            'type' => 'no-date'
        ],
        'Taxoboxes needing a status system parameter' => [
            'type' => 'no-date'
        ],
        'Taxoboxes with an unrecognised status system' => [
            'type' => 'no-date'
        ],
        'Tree of Life cleanup' => [
            'type' => 'no-date',
            'group' => 'Content'
        ],
        'Wikipedia articles needing cleanup after translation' => [
            'type' => 'no-date',
            'group' => 'Content',
            'display' => 'Translation cleanup needed'
        ]

        // since-yearly
        // 'Pages with DOIs inactive' => [
        // 'type' => 'since-yearly',
        // 'group' => 'Links',
        // 'display' => 'DOIs inactive'
        // ]
    ];

    // Short category names for CS1 errors subcats
    public static $SHORTCATS = [
        'Pages using citations with accessdate and no URL' => 'Citation with accessdate and no URL',
        'Pages with archiveurl citation errors' => 'Archiveurl citation error',
        'Pages containing cite templates with deprecated parameters' => 'Cite template with deprecated parameters',
        'Pages using citations with old-style implicit et al. in editors' => 'Citation with old-style implicit et al. in editors',
        'Pages with empty citations' => 'Empty citation',
        'Pages using citations with format and no URL' => 'Citation with format and no URL',
        'Pages with citations using conflicting page specifications' => 'Citation using conflicting page specification',
        'Pages with citations having redundant parameters' => 'Citation has redundant parameters',
        'Pages with citations lacking titles' => 'Citation lacking title',
        'Pages using web citations with no URL' => 'Web citation with no URL',
        'Pages with citations having bare URLs' => 'Citation with bare URL',
        'Pages with citations using unnamed parameters' => 'Citation using unnamed parameter',
        'Pages with citations using unsupported parameters' => 'Citation using unsupported parameter',
        'Pages with URL errors' => 'URL error'
    ];

    static $parentCats = [];

    var $tools_host;

    var $mediawiki;

    var $user;

    var $pass;

    public $categories = [];

    // Storing in memory because SQL join is hanging.

    /**
     * Constructor
     *
     * @param string $enwiki_host
     * @param string $user
     * @param string $pass
     * @param string $tools_host
     */
    public function __construct($mediawiki, $user, $pass, $tools_host)
    {
        $this->mediawiki = $mediawiki;
        $this->user = $user;
        $this->pass = $pass;
        $this->tools_host = $tools_host;
    }

    /**
     * Load the articles in the above categories.
     *
     * @param bool $skipCatLoad
     *            Skip the category load, only load parent cats
     * @return int Category count
     */
    public function load($skipCatLoad)
    {
        $months = array_flip(ReportGenerator::$MONTHS);
        $dbh_tools = new PDO("mysql:host={$this->tools_host};dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $this->user, $this->pass);
        $dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $count = 0;
        if (! $skipCatLoad) {
            $dbh_tools->exec('TRUNCATE category');
            $dbh_tools->exec('TRUNCATE categorylinks');
        }

        $dbh_tools = null;

        foreach (self::$CATEGORIES as $cat => $attribs) {
            $dbh_tools = new PDO("mysql:host={$this->tools_host};dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $this->user, $this->pass);
            $dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $isth = $dbh_tools->prepare('INSERT INTO category VALUES (?,?,?,?)');
            $cattype = $attribs['type'];

            $subcatsonly = isset($attribs['subcats']);
            if ($skipCatLoad && ! $subcatsonly)
                continue;
            $cats = [];

            switch ($cattype) {
                case 'from-monthly':
                    $cats[] = [
                        'type' => 'from-monthly',
                        'params' => [
                            'generator' => 'allpages',
                            'gapprefix' => "$cat from ",
                            'gapnamespace' => 14,
                            'gaplimit' => 'max'
                        ]
                    ];

                    $cats[] = [
                        'type' => 'no-date',
                        'from-monthly-no-date' => true,
                        'params' => [
                            'titles' => "Category:$cat"
                        ]
                    ];
                    break;

                case 'since-yearly': // now 'as of'
                    $cats[] = [
                        'type' => 'since-yearly',
                        'params' => [
                            'generator' => 'allpages',
                            'gapprefix' => "$cat as of ",
                            'gapnamespace' => 14,
                            'gaplimit' => 'max'
                        ]
                    ];
                    break;

                case 'no-date':
                    if ($subcatsonly) {
                        $cats[] = [
                            'type' => 'no-date',
                            'params' => [
                                'generator' => 'categorymembers',
                                'gcmtitle' => "Category:$cat",
                                'gcmtype' => 'subcat',
                                'gcmlimit' => 'max'
                            ]
                        ];
                    } else {
                        $cats[] = [
                            'type' => 'no-date',
                            'params' => [
                                'titles' => "Category:$cat"
                            ]
                        ];
                    }
                    break;
            }

            foreach ($cats as $params) {
                $ret = $this->mediawiki->getProp('categoryinfo', $params['params']);

                if (! isset($ret['query']) || ! isset($ret['query']['pages']) || empty($ret['query']['pages'])) {
                    if (! isset($params['from-monthly-no-date']))
                        Logger::log("category not found " . print_r($params, true));
                    continue;
                }

                foreach ($ret['query']['pages'] as $catid => $catinfo) {
                    if (! isset($catinfo['categoryinfo'])) {
                        Logger::log("categoryinfo not found id= $catid\n" . print_r($catinfo, true));
                        continue;
                    }

                    if ($catinfo['categoryinfo']['pages'] == 0)
                        continue;

                    $catinfo['title'] = substr($catinfo['title'], 9);
                    $title = $catinfo['title'];

                    $month = null;
                    $year = null;

                    switch ($params['type']) {
                        case 'from-monthly':
                            if (preg_match("!^$cat from ([ADFJMNOS]\w+) (\d{4})$!", $title, $matches)) {
                                if (! isset($months[$matches[1]])) {
                                    Logger::log("month not found - $title");
                                    continue 2;
                                }

                                $month = $months[$matches[1]];
                                $year = $matches[2];
                            } elseif (preg_match("!^$cat from (\d{4})$!", $title, $matches)) {
                                $year = $matches[1];
                            } else {
                                continue 2;
                            }

                            break;

                        case 'since-yearly':
                            if (preg_match("!^$cat since (\d{4})$!", $title, $matches))
                                $year = $matches[1];
                            break;
                    }

                    if ($subcatsonly) {
                        self::$parentCats[$title] = $cat;
                    } else {
                        $catinfo['title'] = $cat;
                    }

                    if (! $skipCatLoad) {
                        if (isset($this->categories[$catid]))
                            continue; // skip dup categories

                        $isth->execute([
                            $catid,
                            $catinfo['title'],
                            $month,
                            $year
                        ]);

                        ++ $count;
                        $this->categories[$catid] = [
                            't' => $catinfo['title'],
                            'm' => $month,
                            'y' => $year
                        ];

                        $this->loadCategoryMembers($catid, "Category:$title", $dbh_tools);
                    }
                }
            }

            $isth = null;
            $dbh_tools = null;
        }

        if ($skipCatLoad) {
            $dbh_tools = new PDO("mysql:host={$this->tools_host};dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $this->user, $this->pass);
            $dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $results = $dbh_tools->query('SELECT * FROM category');
            $results->setFetchMode(PDO::FETCH_ASSOC);

            while ($row = $results->fetch()) {
                $catid = (int) $row['cat_id'];
                $this->categories[$catid] = [
                    't' => $row['cat_title'],
                    'm' => $row['month'],
                    'y' => $row['year']
                ];
            }

            $results->closeCursor();
            $results = null;
            $dbh_tools = null;
        }

        return $count;
    }

    /**
     * Load article ids for a category.
     *
     * @param int $catid
     *            Category id
     * @param string $cat
     *            Category
     */
    function loadCategoryMembers($catid, $cat, PDO $dbh_tools)
    {
        $isth = $dbh_tools->prepare('INSERT IGNORE INTO categorylinks VALUES (?,?)');
        $continue = '';

        while ($continue !== false) {
            $members = $this->getChunk($cat, $continue);
            $dbh_tools->beginTransaction();

            foreach ($members as $page) {
                if ($page['ns'] != 0 && $page['ns'] != 1)
                    continue;
                $pagetitle = $page['title'];
                if ($page['ns'] == 1)
                    $pagetitle = substr($pagetitle, 5);
                $isth->execute([
                    $pagetitle,
                    $catid
                ]);
            }

            $dbh_tools->commit();
        }

        $isth = null;
    }

    /**
     * Get a chunk of category members.
     *
     * @param string $category
     * @param mixed $continue
     * @return mixed
     */
    function getChunk($category, &$continue)
    {
        $params = [
            'continue' => $continue,
            'cmtitle' => $category,
            'cmlimit' => 'max',
            'cmtype' => 'page'
        ];

        $ret = $this->mediawiki->getList('categorymembers', $params);

        if (isset($ret['continue']))
            $continue = $ret['continue'];
        else
            $continue = false;

        if (! isset($ret['query']) || ! isset($ret['query']['categorymembers']))
            return [];

        return $ret['query']['categorymembers'];
	}
}
