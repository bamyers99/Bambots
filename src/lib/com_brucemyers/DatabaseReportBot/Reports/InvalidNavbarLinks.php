<?php
/**
 Copyright 2014-2016 Myers Enterprises II

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

namespace com_brucemyers\DatabaseReportBot\Reports;

use com_brucemyers\Util\TemplateParamParser;
use PDO;

class InvalidNavbarLinks extends DatabaseReport
{
    public function getUsage()
    {
    	return " - Check that Navbar links match parent template name";
    }

	public function getTitle()
	{
		return 'Invalid Navbar links';
	}

	public function getIntro()
	{
		return 'Invalid Navbar links; v-t-e links point to the wrong template; data as of <onlyinclude>%s</onlyinclude>.';
	}

	public function getHeadings()
	{
		return ['Template', 'Invalid name'];
	}

	public function getRows($apis)
	{
		$template_types = [
			'Sidebar' => [
				'children' => ['Sidebar', 'Sidebar with collapsible lists'],
			    'modules' => ['Sidebar'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [
					'navbar' => ['none', 'off']
				],
				'exclude_templates' => ['Politics of Canada/proposed split']
			],

			'Infobox' => [
				'children' => ['Infobox'],
			    'modules' => ['Infobox'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Infobox3cols' => [
				'children' => ['Infobox3cols'],
			    'modules' => ['Infobox3cols'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'BS-map' => [
				'children' => ['BS-map'],
				'name_param' => 'navbar',
				'exclude_empty' => ['title'],
				'exclude_values' => [],
				'exclude_templates' => ['Arbatsko-Pokrovskaya Line','Filyovskaya Line','Kalininskaya Line','Kaluzhsko-Rizhskaya Line']
			],

			'BS-header' => [
				'children' => ['BS-header'],
				'name_param' => 2,
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Election table' => [
				'children' => ['Election table', 'Election Table', 'Electiontable'],
				'name_param' => 1,
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Football squad' => [
				'children' => ['Football squad'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Fs2' => [
				'children' => ['Fs2'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Navbox rugby league squad' => [
				'children' => ['Navbox rugby league squad'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Rugby union squad' => [
				'children' => ['Rugby union squad'],
				'name_param' => 'templatename',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Campaignbox' => [
				'children' => ['Campaignbox'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Team roster navbox' => [
				'children' => ['Team roster navbox', 'Baseball navbox', 'Baseball roster navbox', 'MLB roster navbox'],
			    'modules' => ['Team roster navbox'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'National squad' => [
			    'children' => ['National squad', 'National basketball squad', 'National field hockey squad', 'National handball squad'],
			    'modules' => ['National squad'],
			    'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'National squad no numbers' => [
				'children' => ['National squad no numbers'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'NRHP navigation box' => [
				'children' => ['NRHP navigation box'],
				'name_param' => 'name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'US county navigation box' => [
				'children' => ['US county navigation box'],
				'name_param' => 'template_name',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Graphical timeline' => [
				'children' => ['Graphical timeline'],
				'name_param' => 'link-to',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Routemap' => [
				'children' => ['Routemap'],
			    'modules' => ['Routemap'],
				'name_param' => 'navbar',
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Navbar-collapsible' => [
				'children' => ['Navbar-collapsible', 'Tnavbar-collapsible'],
				'name_param' => 2,
				'exclude_empty' => [],
				'exclude_values' => [],
				'exclude_templates' => []
			],

			'Navbox' => [ // Must be last because 'MySQL server has gone away' happens after this is run
		    	'children' => ['Navbox', 'Navbox with collapsible groups', 'Navbox with columns', 'Navbox with collapsible sections'],
			    'modules' => ['Navbox'],
		    	'name_param' => 'name',
		    	'exclude_empty' => ['title'],
		    	'exclude_values' => [
		        	'navbar' => ['plain', 'off']
		    	],
				'exclude_templates' => []
		    ]
		];

		$groups = ['linktemplate' => false,
		        'cats' => '[[Category:Active Wikipedia database reports]]',
				'groups' => []];
		$grand_total_scanned = 0;
		$error_count = 0;

		$wiki_host = $apis['wiki_host'];
		$user = $apis['user'];
		$pass = $apis['pass'];
		$mediawiki = $apis['mediawiki'];

		foreach ($template_types as $type_name => $template_type) {
			$groupname = "{{tlp|$type_name|{$template_type['name_param']}&#61;}}";
			//echo "==$groupname==\n";

			// Get the redirects to this navbar
			$navbar_types = $template_type['children'];

			$temps = $navbar_types;
			$temps[] = $type_name;

			$qcnt = count($temps);
			$qs = implode(',', array_fill(0, $qcnt, '?'));

			$sql = "SELECT page_title FROM redirect, page " .
				" WHERE rd_namespace = 10 AND rd_title IN ($qs) " .
				" AND page_id = rd_from";
			$dbh_enwiki = new PDO("mysql:host=$wiki_host;dbname=enwiki_p;charset=utf8", $user, $pass);
			$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sth = $dbh_enwiki->prepare($sql);

			for ($x=1; $x <= $qcnt; ++$x) {
			    $sth->bindValue($x, str_replace(' ', '_', $temps[$x-1]));
			}

			$sth->execute();
			$sth->setFetchMode(PDO::FETCH_NUM);
			$titles = [];

			while ($row = $sth->fetch()) {
				$redir_title = str_replace('_', ' ', $row[0]);
				if (! in_array($redir_title, $navbar_types)) $navbar_types[] = $redir_title;
			}

			$sth->closeCursor();
			$sth = null;
			$dbh_enwiki = null;

			// Retrieve the target navbars

			$temps = $navbar_types;
			$temps[] = $type_name;

			$qcnt = count($temps);
			$qs = implode(',', array_fill(0, $qcnt, '?'));

			$sql = "SELECT DISTINCT page_title FROM templatelinks, page " .
				" WHERE tl_from_namespace = 10 AND tl_namespace = 10 AND tl_title IN ($qs) " .
				" AND page_namespace = 10 AND page_id = tl_from";
			
    		$dbh_enwiki = new PDO("mysql:host=$wiki_host;dbname=enwiki_p;charset=utf8", $user, $pass);
    		$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sth = $dbh_enwiki->prepare($sql);

			for ($x=1; $x <= $qcnt; ++$x) {
			    $sth->bindValue($x, str_replace(' ', '_', $temps[$x-1]));
			}

			$sth->execute();
			$sth->setFetchMode(PDO::FETCH_NUM);
			$titles = [];

			while ($row = $sth->fetch()) {
			    $titles['Template:' . $row[0]] = true; // Removes dups
			}
			
			$sth->closeCursor();
			$sth = null;
			$dbh_enwiki = null;
			
			// Retrieve Module invocations
			
			if (isset($template_type['modules'])) {
			    $temps = $template_type['modules'];
    			
    			$qcnt = count($temps);
    			$qs = implode(',', array_fill(0, $qcnt, '?'));
    			
    			$sql = "SELECT DISTINCT page_title FROM templatelinks, page " .
    			 			" WHERE tl_from_namespace = 10 AND tl_namespace = 828 AND tl_title IN ($qs) " .
    			 			" AND page_namespace = 10 AND page_id = tl_from";

    			$dbh_enwiki = new PDO("mysql:host=$wiki_host;dbname=enwiki_p;charset=utf8", $user, $pass);
    			$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    			$sth = $dbh_enwiki->prepare($sql);
    			
    			for ($x=1; $x <= $qcnt; ++$x) {
    			    $sth->bindValue($x, str_replace(' ', '_', $temps[$x-1]));
    			}
    			
    			$sth->execute();
    			$sth->setFetchMode(PDO::FETCH_NUM);
    			
    			while ($row = $sth->fetch()) {
    			    $titles['Template:' . $row[0]] = true; // Removes dups
    			}
    			
    			$sth->closeCursor();
    			$sth = null;
    			$dbh_enwiki = null;
			}

			ksort($titles);

			$results = [];
			$total_scanned = 0;

			$mediawiki->cachePages(array_keys($titles));

			foreach (array_keys($titles) as $template) {
				// echo "$template\n";
				$data = $mediawiki->getPageWithCache($template);

				$parsed_templates = TemplateParamParser::getTemplates($data);

				$template = substr($template, 9);
				$template = str_replace('_', ' ', $template);
				$template = ucfirst($template);

				if (in_array($template, $template_type['exclude_templates'])) continue;

				foreach ($parsed_templates as $parsed_template) {
				    // print_r($parsed_template);
				    $parsed_name = $parsed_template['name'];
				    $parsed_template_type = $parsed_template['type'];
				    
				    if ($parsed_template_type == TemplateParamParser::TEMPLATE_TYPE_MODULE) {
				        if (! in_array($parsed_name, $template_type['modules'])) continue;
				    } else {
				        if (! in_array($parsed_name, $navbar_types)) continue;
				    }
				    
				    ++$total_scanned;
				    ++$grand_total_scanned;
				    
					$params = $parsed_template['params'];
					// print_r($params);

					// Exclude if template name is empty
					$name_param = $template_type['name_param'];
					if (empty($params[$name_param])) continue;

					// Exclude if a param is empty
					foreach ($template_type['exclude_empty'] as $exclude_empty) {
						if (empty($params[$exclude_empty])) continue 2;
					}

					// Exclude if param = value
					foreach ($template_type['exclude_values'] as $value_name => $value_values) {
						if (empty($params[$value_name])) continue;
						foreach ($value_values as $value_value) {
							if ($params[$value_name] == $value_value) continue 3;
						}
					}

		    		if (preg_match('!/(archive|child|doc|drafts|main|more|sandbox|shell|testcase|table|box|lua)!i', $template)) continue;

		    		$name = str_replace('_', ' ', $params[$name_param]);
		    		$name = preg_replace('!\s+!', ' ', $name);
		    		$name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
		    		$name = ucfirst($name);

		    		if (strpos($name, 'Template:') === 0) {
		    			$name = ucfirst(ltrim(substr($name, 9)));
		    		}

		    		if (strpos($name, '{') !== false) continue;
		    		if (strpos($name, '<') !== false) continue;
		    		
		    		if ($name != $template) $results[] = ["[[Template:$template|$template]]", $name];
				}
			}

			$groups['groups'][$groupname] = $results;
			$groups['groups'][$groupname]['group_footer'] = "Templates scanned: " . number_format($total_scanned) . "\n";
			$error_count += count($results);
		}

		ksort($groups['groups']);
		$groups['cats'] = "\nGrand total templates scanned: "  . number_format($grand_total_scanned) . "\n" . $groups['cats'];
		$groups['comment'] = "Record count: $error_count Templates scanned: " . number_format($grand_total_scanned);

		return $groups;
	}
}