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

namespace com_brucemyers\WPPageListBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Config;
use Exception;

/**
 * WikiProject page list creation bot
 */
class WPPageListBot
{
    protected $mediawiki;
    protected $curtime;
    protected $category;
    protected $bannertemplate;
    protected $wikiproject;
    protected $resultWriter;
    protected $namespaces;

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki
     * @param $configs array
     * @param $resultWriter ResultWriter
     *
     * Configs:
     *   'category' => 'WikiProject Oregon pages',
     *   'articles' => 'Wikipedia:WikiProject Oregon/Admin',
     *   'nonarticles' => 'Wikipedia:WikiProject Oregon/Admin2',
     *   'bannertemplate' => 'Wikipedia:WikiProject Oregon/Nav'
     */
    public function __construct(MediaWiki $mediawiki, $configs, ResultWriter $resultWriter)
    {
        $this->mediawiki = $mediawiki;
        $this->resultWriter = $resultWriter;

        // Relabel some namespaces
        foreach ($mediawiki->namespaces as $ns => $title) {
            if ($ns % 2 == 1) $title = str_replace('talk', '(mistagged)', $title);
            $this->namespaces[$ns] = $title;
        }

        foreach ($configs as $wikiproject => $config) {
            $this->wikiproject = $wikiproject;
            $this->curtime = date('G:i l F j, Y');
            $this->bannertemplate = $config['bannertemplate'];

            // Retrieve the category page list
            $this->category = $config['category'];
            if (stripos($this->category, 'category:') !== 0) $this->category = 'Category:' . $this->category;

            $params = array(
            	'cmtitle' => $this->category,
            	'cmprop' => 'title',
            	'cmtype' => 'page',
            	'cmlimit' => Config::get(MediaWiki::WIKICHANGESINCREMENT)
            );

            $pages = array();
            $continue = array('continue' => '');

            while ($continue !== false) {
                $cmparams = array_merge($params, $continue);

                $ret = $this->mediawiki->getCategoryMembers($cmparams);

                if (isset($ret['error'])) throw new Exception('WPPageListBot failed ' . $ret['error']);
                if (isset($ret['continue'])) $continue = $ret['continue'];
                else $continue = false;

                foreach ($ret['query']['categorymembers'] as $cm) {
                    $ns = $cm['ns'] - 1; // Convert from talk to non-talk namespace
                    // If wasn't in talk namespace flip the namespace
                    if (abs($ns % 2) == 1) $ns += 2;
                    if (! isset($pages[$ns])) $pages[$ns] = array();
                    $title = preg_replace('/(?:^Talk| talk):/', ':', $cm['title']);
                    if ($title[0] == ':') $title = substr($title, 1);
                    $pages[$ns][] = $title;
                }
            }

            ksort($pages);

            // Generate the result pages
            if (empty($config['nonarticles'])) {
                $this->generatePage($config['articles'], '', true, $pages);
            } else {
                $articles = array();
                if (isset($pages[0])) {
                    $articles[0] = $pages[0];
                    unset($pages[0]);
                }
                if (isset($pages[1])) {
                    $articles[1] = $pages[1];
                    unset($pages[1]);
                }

                $this->generatePage($config['articles'], $config['nonarticles'], true, $articles);
                $this->generatePage($config['nonarticles'], $config['articles'], false, $pages);
            }
        }
    }

    /**
     * Generate a results page
     */
    protected function generatePage($pagename, $otherpagename, $isArticles, &$pages)
    {
        $output = '';

        if (! empty($this->bannertemplate)) $output .= '{{' . $this->bannertemplate . '}}' . "\n";
        $output .= 'This list was constructed from articles tagged with {{tl|' . $this->wikiproject .
            '}} (or any other article in [[:' . $this->category .
            ']]) as of ' . $this->curtime . '. This list makes possible [http://en.wikipedia.org/w/index.php?title=Special:Recentchangeslinked&target=' .
            urlencode(str_replace(' ', '_', $pagename)) . ' Recent article changes].' . "\n\n";

        // Print totals
        $totals = array();
        foreach ($pages as $ns => $titles) {
            $totals[] = $this->namespaces[$ns] . ': ' . count($titles);
        }
        $output .= 'Totals - ' . implode(', ', $totals) . "\n\n";

        if (! empty($otherpagename)) {
            $nonarticles = ($isArticles) ? 'non-' : '';
            $output .= "<small>''See also: [[$otherpagename]] for {$nonarticles}article entries''</small>\n\n";
        }

        // Print the titles for each namespace
        foreach ($pages as $ns => $titles) {
            $output .= '==' . $this->namespaces[$ns] . "==\n";
            foreach ($titles as $title) {
                if ($ns != 0 && $ns != 1) $title = ':' . $title;
                $output .= "*[[$title]]\n";
            }
        }

        $this->resultWriter->writeResults($pagename, $output);
    }
}