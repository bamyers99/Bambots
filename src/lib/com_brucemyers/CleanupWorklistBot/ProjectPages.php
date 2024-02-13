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
     * @param string $project
     * @return int Pages loaded
     * @throws Exception
     */
    public function load($category, $member_cat_type, $project, $assessment_project)
    {
    	$category = str_replace('_', ' ', $category);
    	$project = str_replace('_', ' ', $project);
    	$assessment_project = str_replace('_', ' ', $assessment_project);
    	if (empty($assessment_project)) $assessment_project = $project;
    	
    	// Load the pages
    	$dbh_tools = new PDO("mysql:host={$this->tools_host};dbname=s51454__CleanupWorklistBot;charset=utf8mb4", $this->user, $this->pass);
   		$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$dbh_tools->exec('TRUNCATE page');
    	$page_count = 0;

    	if ($member_cat_type == 0) {
    	    $categories = [];
    	    $params = ['generator' => 'categorymembers',
                'gcmtitle' => "Category:$category articles by quality",
    	        'gcmtype' => 'subcat',
    	        'gcmlimit' => 'max'];

    	    $ret = $this->mediawiki->getProp('categoryinfo', $params);

    	    if (isset($ret['query']) && isset($ret['query']['pages'])) {
    	        foreach ($ret['query']['pages'] as $catinfo) {
    	            if ($catinfo['categoryinfo']['pages'] == 0) continue;
    	            $categories[] = $catinfo['title'];
    	        }
    	    }
    	} else {
    	    $categories = [$category];
    	}

    	// IGNORE necessary because of replica database integrity issues
    	$isth = $dbh_tools->prepare('INSERT IGNORE INTO page VALUES (?,?,?)');

    	foreach ($categories as $cat) {
        	$continue = '';

        	while ($continue !== false) {
        	    $members = $this->getChunk($member_cat_type, $cat, $continue);
        	    $dbh_tools->beginTransaction();

        	    foreach ($members as $title) {
        	        ++$page_count;
        	        $isth->execute([$title, '', '']);
        	    }

        	    $dbh_tools->commit();
        	}
    	}
    	
    	$assessments_loaded = false;
    	$assessment_count = 0;
    	
    	// If < 10 pages found, use WikiProject assessements
    	if ($page_count < 10 && $assessment_project != 'None') {
    	    $dbh_tools->exec('TRUNCATE page');
    	    $page_count = 0;
    	    $assessments_loaded = true;
    	    $continue = '';
    	    
    	    while ($continue !== false) {
    	        $members = $this->getAssessmentChunk($assessment_project, $continue);
    	        $dbh_tools->beginTransaction();
    	        
    	        foreach ($members as $attribs) {
    	            ++$page_count;
    	            ++$assessment_count;
    	            $isth->execute([$attribs['pt'], $attribs['i'], $attribs['c']]);
    	        }
    	        
    	        $dbh_tools->commit();
    	    }
    	}

    	// Delete the pages with no issues
    	$sql = 'DELETE FROM page
    		WHERE page_title NOT IN (
    			SELECT cl_from
    			FROM categorylinks
			)';

    	$dbh_tools->exec($sql);

    	// Get article assessments
    	$isth = $dbh_tools->prepare('UPDATE IGNORE page SET class = ?, importance = ? WHERE page_title = ?');
    	
    	if ($assessments_loaded || $assessment_project == 'None') $continue = false;
    	else $continue = '';

    	while ($continue !== false) {
    	    $members = $this->getAssessmentChunk($assessment_project, $continue);
    	    $dbh_tools->beginTransaction();

    	    foreach ($members as $attribs) {
    	        ++$assessment_count;
     	        $isth->execute([$attribs['c'], $attribs['i'], $attribs['pt']]);
    	    }

    	    $dbh_tools->commit();
    	}

    	$dbh_tools = null;

    	return [$page_count, $assessment_count];
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
        $params = ['continue' => $continue, 'cmlimit' => 'max', 'cmtype' => 'page'];

        switch ($member_cat_type) {
            case 0: // articles by quality (subcats, talk namespace)
                $params['cmtitle'] = $category;
                break;

            case 1: // WikiProject x articles (talk namespace)
                $params['cmtitle'] = "Category:WikiProject {$category} articles";
                break;

            case 2: // x (talk namespace)
            case 3: // x (article namespace)
                $params['cmtitle'] = "Category:$category";
                break;
        }

        $ret = $this->mediawiki->getList('categorymembers', $params);

        if (isset($ret['continue'])) $continue = $ret['continue'];
        else $continue = false;

        if (! isset($ret['query']) || ! isset($ret['query']['categorymembers']) || empty($ret['query']['categorymembers'])) return [];

        switch ($member_cat_type) {
            case 0: // articles by quality (subcats, talk namespace)
            case 1: // WikiProject x articles (talk namespace)
            case 2: // x (talk namespace)
                foreach ($ret['query']['categorymembers'] as $page) {
                    if ($page['ns'] != 1) continue;
                    $result[] = substr($page['title'], 5);
                }
                break;

            case 3:// x (article namespace)
                foreach ($ret['query']['categorymembers'] as $page) {
                    if ($page['ns'] != 0) continue;
                    $result[] = $page['title'];
                }
                break;
        }

        return $result;
    }

    /**
     * Get a chunk of project member assessments.
     *
     * @param string $project
     * @param mixed $continue
     * @return mixed
     */
    function getAssessmentChunk($project, &$continue)
    {
        $result = [];

        $params = ['continue' => $continue];
        $params['wppprojects'] = $project;
        $params['wpplimit'] = 'max';
        $params['wppassessments'] = 'true';
        $ret = $this->mediawiki->getList('projectpages', $params);

        if (isset($ret['continue'])) $continue = $ret['continue'];
        else $continue = false;

        if (! isset($ret['query']) || ! isset($ret['query']['projects']) || empty($ret['query']['projects'])) return [];
        $ret = reset($ret['query']['projects']);

        foreach ($ret as $page) {
            if ($page['ns'] != 0) continue;
            $result[] = ['pt' => $page['title'], 'i' => $page['assessment']['importance'], 'c' => $page['assessment']['class']];
        }

        return $result;
    }
}