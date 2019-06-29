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

class ProjectPages
{
	const SQL_Articles_by_quality = 'SELECT * FROM category WHERE cat_title = ? AND cat_pages > 0 LIMIT 1';
	const SQL_WikiProject_articles = 'SELECT * FROM category WHERE cat_title = ? AND cat_pages - (cat_subcats + cat_files) > 0 LIMIT 1';
	const SQL_Category_talk = 'SELECT * FROM categorylinks as cl, page WHERE cl.cl_from = page.page_id AND
    			page.page_namespace = 1 AND cl.cl_to = ? LIMIT 1';
	const SQL_Category_article = 'SELECT * FROM categorylinks as cl, page WHERE cl.cl_from = page.page_id AND
    			page.page_namespace = 0 AND cl.cl_to = ? LIMIT 1';
	const SQL_Importance = "SELECT cl_from FROM categorylinks WHERE cl_to = ? AND cl_type = 'page'";
	const SQL_Class = "SELECT cl_from FROM categorylinks WHERE cl_to = ? AND cl_type = 'page'";

	var $mediawiki;
	var $user;
	var $pass;
	var $tools_host;

    /**
     * Constructor
     *
	 * @param string $mediawiki
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
     * Load a projects pages
     *
     * @param string $category
     * @param int $member_cat_type
     * @return int Pages loaded
     * @throws Exception
     */
    public function load($category, $member_cat_type)
    {
    	$category = str_replace('_', ' ', $category);

    	// Load the pages
    	$dbh_tools = new PDO("mysql:host={$this->tools_host};dbname=s51454__CleanupWorklistBot;charset=utf8", $this->user, $this->pass);
   		$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$dbh_tools->exec('TRUNCATE page');
    	$page_count = 0;

    	$continue = '';
    	// IGNORE necessary because of replica database integrity issues
    	$isth = $dbh_tools->prepare('INSERT IGNORE INTO page VALUES (?,?,?)');

    	while ($members = $this->getChunk($member_cat_type, $category, $continue)) {
    	    $dbh_tools->beginTransaction();

    	    foreach ($members as $attribs) {
    	        ++$page_count;

    	        $isth->execute([$attribs['pt'], $attribs['i'], $attribs['c']]);
    	    }

    	    $dbh_tools->commit();

    	    if ($continue === false) break;
    	}

    	// Delete the pages with no issues
    	$sql = 'DELETE FROM page
    		WHERE page_title NOT IN (
    			SELECT cl_from
    			FROM categorylinks
			)';

    	$dbh_tools->exec($sql);

    	$dbh_tools = null;

    	return $page_count;
    }

    /**
     * Get a chunk of project members.
     *
     * @param int $member_cat_type
     * @param string $category
     * @param mixed $continue
     * @return mixed
     */
    function getChunk($member_cat_type, $category, &$continue)
    {
        $result = [];
        $params = ['continue' => $continue];

        switch ($member_cat_type) {
            case 0: // articles by quality (subcats)
                $params['wppprojects'] = $category;
                $params['wpplimit'] = 'max';
                $params['wppassessments'] = 'true';
                $ret = $this->mediawiki->getList('projectpages', $params);
                break;

            case 1: // WikiProject x articles (talk namespace)
                $params['cmtitle'] = "Category:WikiProject {$category} articles";
                $params['cmlimit'] = 'max';
                $ret = $this->mediawiki->getList('categorymembers', $params);
                break;

            case 2: // x (talk namespace)
            case 3: // x (article namespace)
                $params['cmtitle'] = "Category:$category";
                $params['cmlimit'] = 'max';
                $ret = $this->mediawiki->getList('categorymembers', $params);
                break;
        }

        if (isset($ret['continue'])) $continue = $ret['continue'];
        else $continue = false;

        switch ($member_cat_type) {
            case 0: // articles by quality (subcats)
                if (! isset($ret['query']) || ! isset($ret['query']['projects']) || empty($ret['query']['projects'])) return [];
                $ret = reset($ret['query']['projects']);

                foreach ($ret as $page) {
                    if ($page['ns'] != 0) continue;
                    $result[] = ['pt' => $page['title'], 'i' => $page['assessment']['importance'], 'c' => $page['assessment']['class']];
                }

                break;

            case 1: // WikiProject x articles (talk namespace)
            case 2: // x (talk namespace)
                if (! isset($ret['query']) || ! isset($ret['query']['categorymembers']) || empty($ret['query']['categorymembers'])) return [];
                foreach ($ret['query']['categorymembers'] as $page) {
                    if ($page['ns'] != 1) continue;
                    $result[] = ['pt' => substr($page['title'], 5), 'i' => '', 'c' => ''];
                }
                break;

            case 3:// x (article namespace)
                if (! isset($ret['query']) || ! isset($ret['query']['categorymembers']) || empty($ret['query']['categorymembers'])) return [];
                foreach ($ret['query']['categorymembers'] as $page) {
                    if ($page['ns'] != 0) continue;
                    $result[] = ['pt' => $page['title'], 'i' => '', 'c' => ''];
                }
                break;
        }

        return $result;
    }
}