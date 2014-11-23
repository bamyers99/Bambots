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

namespace com_brucemyers\DatabaseReportBot\Reports;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\RenderedWiki\RenderedWiki;
use com_brucemyers\Util\FileCache;
use MediaWiki\Sanitizer;
use PDO;
use Exception;

class StubTypeSizes extends DatabaseReport
{
    public function getUsage()
    {
    	return " - List stub category sizes";
    }

	public function getTitle()
	{
		return 'Stub type sizes';
	}

	public function getIntro()
	{
		return 'Stub categories by size; data as of %s.';
	}

	public function getHeadings()
	{
		return array('Category', 'Members', 'Subcategories');
	}

	public function getRows(PDO $dbh_wiki, PDO $dbh_tools, MediaWiki $mediawiki, RenderedWiki $renderedwiki, PDO $dbh_wikidata)
	{
		// Retrieve the target page contents

		$sql = "SELECT cat_title, cat_pages, cat_subcats FROM category, page " . // Making sure a page for the category exists to weed out bad categories.
			" WHERE CONVERT(cat_title USING utf8) LIKE '%stubs%' AND cat_title = page_title AND page_namespace = 14"; // CONVERT provides case insensitivity
		$sth = $dbh_wiki->query($sql);
		$sth->setFetchMode(PDO::FETCH_NUM);
		$titles = array();

		$groups = array('linktemplate' => false,
				'groups' => array('Less than 30 stubs, no subcategories' => array(),
			'Less than 60 stubs, no subcategories' => array(),
			'Less than 200 stubs' => array(),
			'Less than 400 stubs' => array(),
			'Less than 600 stubs' => array(),
			'Less than 800 stubs' => array(),
			'Less than 1000 stubs' => array(),
			'Less than 1200 stubs' => array(),
			'1200 or more stubs' => array()
		));

		$groupcfg = array('Less than 30 stubs, no subcategories' => array('stubmin' => 0, 'stubmax' => 29, 'nosubcat' => true),
			'Less than 60 stubs, no subcategories' => array('stubmin' => 30, 'stubmax' => 59, 'nosubcat' => true),
			'Less than 200 stubs' => array('stubmin' => 0, 'stubmax' => 199),
			'Less than 400 stubs' => array('stubmin' => 200, 'stubmax' => 399),
			'Less than 600 stubs' => array('stubmin' => 400, 'stubmax' => 599),
			'Less than 800 stubs' => array('stubmin' => 600, 'stubmax' => 799),
			'Less than 1000 stubs' => array('stubmin' => 800, 'stubmax' => 999),
			'Less than 1200 stubs' => array('stubmin' => 1000, 'stubmax' => 1199),
			'1200 or more stubs' => array('stubmin' => 1200, 'stubmax' => 99999999)
		);

		while ($row = $sth->fetch()) {
			$catname = str_replace('_', ' ', $row[0]);
			$pagecnt = (int)$row[1];
			$subcatcnt = (int)$row[2];

			foreach ($groupcfg as $groupname => $cfg) {
				if ($pagecnt >= $cfg['stubmin'] && $pagecnt <= $cfg['stubmax'] && (! isset($cfg['nosubcat']) || $subcatcnt == 0)) {
					$groups['groups'][$groupname][] = array("[[:Category:$catname|$catname]]", $pagecnt, $subcatcnt);
					break;
				}
			}
		}
		$sth->closeCursor();

		foreach ($groups['groups'] as &$group) {
			usort($group, function($a, $b) {
				if ($a[1] > $b[1]) return 1;
				if ($a[1] < $b[1]) return -1;
				return strcmp($a[0], $b[0]);
			});
		}

		return $groups;
	}
}