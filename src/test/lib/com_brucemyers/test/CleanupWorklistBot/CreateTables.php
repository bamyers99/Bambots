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

namespace com_brucemyers\test\CleanupWorklistBot;

use PDO;
use Mock;

class CreateTables
{
    var $mediawiki;

	/**
	 * Create test tables
	 *
	 * @param PDO $dbh_enwiki
	 * @param PDO $dbh_tools
	 */
    public function __construct(PDO $dbh_tools)
    {
    	// tools
    	new \com_brucemyers\CleanupWorklistBot\CreateTables($dbh_tools);

   		$dbh_tools->exec('TRUNCATE history');
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-14', 2, 1, 2, 1, 0)");
   		$dbh_tools->exec("INSERT INTO history VALUES ('Michigan', '2014-05-21', 3, 2, 3, 0, 1)");

   		$dbh_tools->exec('TRUNCATE project');
   		$dbh_tools->exec("INSERT INTO project VALUES ('Featured_articles', 1, 3)");
   		$dbh_tools->exec("INSERT INTO project VALUES ('Good_article_nominees', 1, 2)");
   		$dbh_tools->exec("INSERT INTO project VALUES ('India', 1, 1)");
   		$dbh_tools->exec("INSERT INTO project VALUES ('WikiProject_Michigan', 1, 0)");

   		// enwiki
   		Mock::generate('com_brucemyers\\MediaWiki\\MediaWiki', 'MockMediaWiki');
   		$this->mediawiki = new \MockMediaWiki();

   		// category - x articles by quality (subcats)

   		$this->mediawiki->returns('getList',
   		    ['query' => ['projects' => ['Michigan' => [
   		        ['ns' => 0, 'title' =>'Michigan', 'assessment' => ['importance' => 'Top', 'class' => 'B']],
   		        ['ns' => 0, 'title' =>'Detroit, Michigan', 'assessment' => ['importance' => 'NA', 'class' => 'Unassessed']],
   		        ['ns' => 0, 'title' =>'Mackinac Island', 'assessment' => ['importance' => 'NA', 'class' => 'Unassessed']],
   		        ['ns' => 0, 'title' =>'Lansing, Michigan', 'assessment' => ['importance' => 'NA', 'class' => 'Unassessed']]
   		    ]]]],
   		    ['projectpages', ['continue' => '', 'wppprojects' => 'Michigan', 'wpplimit' => 'max', 'wppassessments' => 'true']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        ['title' =>'Category:B-Class Michigan articles', 'categoryinfo' => ['pages' => 1]],
   		        ['title' =>'Category:Unassessed Michigan articles', 'categoryinfo' => ['pages' => 3]]
   		    ]]],
   		    ['categoryinfo', ['generator' => 'categorymembers', 'gcmtitle' => 'Category:Michigan articles by quality', 'gcmtype' => 'subcat', 'gcmlimit' => 'max']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:Michigan', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:B-Class Michigan articles']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:Detroit, Michigan', 'ns' => 1],
   		        ['title' =>'Talk:Mackinac Island', 'ns' => 1],
   		        ['title' =>'Talk:Lansing, Michigan', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:Unassessed Michigan articles']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '11' => ['title' =>'Category:All articles needing coordinates', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['titles' => 'Category:All articles needing coordinates']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '12' => ['title' =>'Category:Articles needing cleanup from May 2013', 'categoryinfo' => ['pages' => 3]],
   		        '13' => ['title' =>'Category:Articles needing cleanup from March 2013', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['generator' => 'allpages', 'gapprefix' => 'Articles needing cleanup from ', 'gapnamespace' => 14, 'gaplimit' => 'max']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Michigan', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:All articles needing coordinates', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Detroit, Michigan', 'ns' => 0],
   		        ['title' =>'Lansing, Michigan', 'ns' => 0],
   		        ['title' =>'Earth', 'ns' => 0],
   		        ['title' =>'Read\'s Cavern', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Articles needing cleanup from May 2013', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Detroit, Michigan', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Articles needing cleanup from March 2013', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );


   		// category - WikiProject x articles (talk namespace)

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:India', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:WikiProject India articles']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '102' => ['title' =>'Category:Articles needing cleanup', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['titles' => 'Category:Articles needing cleanup']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'India', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Articles needing cleanup', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );


    	// category - x (talk namespace)

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'Talk:United States', 'ns' => 1]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:Good article nominees']]
   		    );

   		$this->mediawiki->returns('getProp',
   		    ['query' => ['pages' =>  [
   		        '200' => ['title' =>'Category:Pages using citations with format and no URL', 'categoryinfo' => ['pages' => 1]]
   		    ]]],
   		    ['categoryinfo', ['generator' => 'categorymembers', 'gcmtitle' => 'Category:Articles with incorrect citation syntax', 'gcmtype' => 'subcat', 'gcmlimit' => 'max']]
   		    );

   		$this->mediawiki->returns('getList',
   		    ['query' => ['categorymembers' =>  [
   		        ['title' =>'United States', 'ns' => 0]
   		    ]]],
   		    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Pages using citations with format and no URL', 'cmlimit' => 'max', 'cmtype' => 'page']]
   		    );


    	// category - x (article namespace)

    	$this->mediawiki->returns('getList',
    	    ['query' => ['categorymembers' =>  [
    	        ['title' =>'Earth', 'ns' => 0],
    	        ['title' =>'Read\'s Cavern', 'ns' => 0]
    	    ]]],
    	    ['categorymembers', ['continue' => '', 'cmlimit' => 'max', 'cmtype' => 'page', 'cmtitle' => 'Category:Featured articles']]
    	    );

    	$this->mediawiki->returns('getProp',
    	    ['query' => ['pages' =>  [
    	        '305' => ['title' =>'Category:Pages with DOIs inactive as of 2013', 'categoryinfo' => ['pages' => 2]]
    	    ]]],
    	    ['categoryinfo', ['generator' => 'allpages', 'gapprefix' => 'Pages with DOIs inactive as of ', 'gapnamespace' => 14, 'gaplimit' => 'max']]
    	    );

    	$this->mediawiki->returns('getList',
    	    ['query' => ['categorymembers' =>  [
    	        ['title' =>'Earth', 'ns' => 0],
    	        ['title' =>'Read\'s Cavern', 'ns' => 0]
    	    ]]],
    	    ['categorymembers', ['continue' => '', 'cmtitle' => 'Category:Pages with DOIs inactive as of 2013', 'cmlimit' => 'max', 'cmtype' => 'page']]
    	    );

    	// Dummys

    	$this->mediawiki->returns('getList',
    	    ['query' => ['categorymembers' =>  []]]
    	    );

    	$this->mediawiki->returns('getProp',
    	    ['query' => ['pages' =>  []]]
    	    );
    }

    public function getMediawiki()
    {
        return $this->mediawiki;
    }
}