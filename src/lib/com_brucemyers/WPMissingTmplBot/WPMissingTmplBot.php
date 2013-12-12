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

namespace com_brucemyers\WPMissingTmplBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\MediaWiki\ResultWriter;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\Logger;
use Exception;

/**
 * WikiProject missing talk page template bot
 */
class WPMissingTmplBot
{
    protected $mediawiki;
    protected $resultWriter;
    protected $curtime;
    protected $wikiproject;
    protected $category;
    protected $altcategory;

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki
     * @param $configs array
     * @param $resultWriter ResultWriter
     *
     * Configs:
     *   'WPCategory' => 'WikiProject Michigan articles',
     *   'altCategory' => 'Michigan road transport articles',
     *   'articleCategoryPart' => 'Michigan',
     *   'resultPage => 'Wikiproject:Michigan/Missing template',
     *   'excludeWords' => array('alumni','people','faculty')
     */
    public function __construct(MediaWiki $mediawiki, $configs, ResultWriter $resultWriter)
    {
        $this->mediawiki = $mediawiki;
        $this->resultWriter = $resultWriter;

        // Retrieve all Wikipedia categories and cache them
        $cacheDir = FileCache::getCacheDir();
        $cachePath = $cacheDir . DIRECTORY_SEPARATOR . 'WPMissingTmplBot.cats';

        if (! file_exists($cachePath)) $this->getAllCategories($cachePath);

        foreach ($configs as $wikiproject => $config) {
            try {
                Logger::log("Processing $wikiproject");
                $this->wikiproject = $wikiproject;
                $this->curtime = date('G:i l F j, Y');

                // Retrieve the category page list
                $this->category = $config['WPCategory'];
                if (stripos($this->category, 'category:') !== 0) $this->category = 'Category:' . $this->category;
                $WPpages = $this->getCategoryMembers($this->category, '1');

                // Retrieve the alternate category page list
                $this->altcategory = $config['altCategory'];
                if (! empty($this->altcategory)) {
                    if (stripos($this->altcategory, 'category:') !== 0) $this->altcategory = 'Category:' . $this->altcategory;
                    $temppages = $this->getCategoryMembers($this->altcategory, '1');
                    $WPpages = array_merge($WPpages, $temppages);
                }

                ksort($WPpages);

                // Get the article names for the matching article category part
                $excludewords = $config['excludeWords'];
                if (! empty($excludewords)) $excludewords = implode('|', $excludewords);
                $articleCategoryPart = $config['articleCategoryPart'];

                $artpages = array();

                $hndl = fopen($cachePath, 'r');

                while (! feof($hndl)) {
                	$buffer = fgets($hndl);
                	$buffer = trim($buffer);
                	if (empty($buffer)) continue;

                	if (stripos($buffer, $articleCategoryPart) !== false &&
                	        (empty($excludewords) || preg_match("!(?:$excludewords)!i", $buffer) == 0)) {
                	    echo $buffer . "\n";
                	    $pages = $this->getCategoryMembers($buffer, '0');

                	    foreach ($pages as $pagename => $dummy) {
                            if (! isset($WPpages[$pagename])) $artpages[$pagename] = true;
                	    }
                	}
                }

                fclose($hndl);

                ksort($artpages);

                // Generate the result pages
                $this->generatePage($config['resultPage'], $artpages);

             } catch (Exception $ex) {
                Logger::log($ex->getMessage() . "\n" . $ex->getTraceAsString());
             }
        }
    }

    /**
     * Generate a results page
     */
    protected function generatePage($pagename, &$pages)
    {
        $output = '';

        $output .= "This list was constructed from articles '''not''' tagged with {{tl|" . $this->wikiproject .
            '}} or in [[:' . $this->category . ']]';
        if (! empty($this->altcategory)) $output .= ' or in [[:' . $this->altcategory . ']]';
        $output .= ' as of ' . $this->curtime . "\n\n";

        // Print totals
        $output .= 'Total: ' .count($pages) . "\n\n";

        // Print the titles
        foreach ($pages as $title => $dummy) {
            $output .= "*[[$title]]\n";
        }

        $this->resultWriter->writeResults($pagename, $output, 'Total: ' .count($pages));
    }

    /**
     * Get all Wikipedia categories and cache them
     */
    protected function getAllCategories($cachePath)
    {
        $params = array(
        	'acmin' => 1,
        	'aclimit' => Config::get(MediaWiki::WIKICHANGESINCREMENT)
        );
        $continue = array('continue' => '');

        $hndl = fopen($cachePath, 'w');

        while ($continue !== false) {
        	$acparams = array_merge($params, $continue);

        	$ret = $this->mediawiki->getList('allcategories', $acparams);

        	if (isset($ret['error'])) throw new Exception('WPMissingTmplBot failed ' . $ret['error']);
        	if (isset($ret['continue'])) $continue = $ret['continue'];
        	else $continue = false;

        	foreach ($ret['query']['allcategories'] as $cat) {
        	    $catname = $cat['*'];
        	    fwrite($hndl, "$catname\n");
        	}
        }

        fclose($hndl);
    }

    /**
     * Get category members; for talk namespaces, returns non-talk page name
     *
     * @param $catname string Category name
     * @param $namespace string (optional) Pipe separated list of namespaces or empty for all (default)
     * @return array Key = page name, value = true
     */
    protected function getCategoryMembers($catname, $namespace = '')
    {
        if (stripos($catname, 'category:') !== 0) $catname = 'Category:' . $catname;
        $params = array(
        	'cmtitle' => $catname,
        	'cmprop' => 'title',
        	'cmtype' => 'page',
        	'cmlimit' => Config::get(MediaWiki::WIKICHANGESINCREMENT)
        );
        if (strlen($namespace) != 0) $params['cmnamespace'] = $namespace;
        $pages = array();
        $continue = array('continue' => '');

        while ($continue !== false) {
        	$cmparams = array_merge($params, $continue);

        	$ret = $this->mediawiki->getList('categorymembers', $cmparams);

        	if (isset($ret['error'])) throw new Exception('WPMissingTmplBot failed ' . $ret['error']);
        	if (isset($ret['continue'])) $continue = $ret['continue'];
        	else $continue = false;

        	foreach ($ret['query']['categorymembers'] as $cm) {
        		$title = preg_replace('/(?:^Talk| talk):/', ':', $cm['title']);
        		if ($title[0] == ':') $title = substr($title, 1);
        		$pages[$title] = true;
        	}
        }

        return $pages;
    }
}