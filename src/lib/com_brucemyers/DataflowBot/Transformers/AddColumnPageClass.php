<?php
/**
 Copyright 2015 Myers Enterprises II

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

namespace com_brucemyers\DataflowBot\Transformers;

use com_brucemyers\DataflowBot\io\FlowReader;
use com_brucemyers\DataflowBot\io\FlowWriter;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\CleanupWorklistBot\CreateTables;
use com_brucemyers\DataflowBot\ComponentParameter;
use PDO;

class AddColumnPageClass extends AddColumn
{
	const UNASSESSED_REGEX = '/^Unassessed_.+_articles$/';
	const OTHER_REGEX = '/^([^-]+)-Class_.+_articles$/';
	static $IMAGES = array(
		'FA' => '[[File:Featured article star.svg|16px|Featured article]]',
		'FL' => '[[File:Featured article star.svg|16px|Featured list]]',
		'A' => '[[File:Symbol a class.svg|16px|A-class]]',
		'GA' => '[[File:Symbol support vote.svg|16px|Good article]]',
		'Bplus' => '[[File:Symbol b class.svg|16px|B-class]]',
		'B' => '[[File:Symbol b class.svg|16px|B-class]]',
		'C' => '[[File:Symbol c class.svg|16px|C-class]]',
		'Start' => '[[File:Symbol start class.svg|16px|Start-class]]',
		'Stub' => '[[File:Symbol stub class.svg|16px|Stub-class]]',
		'Unassessed' => '[[File:Symbol question.svg|16px|Unassessed-class]]'
	);

	var $firstRowHeaders;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Add Column Page Class';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Add a new column with page classes retrieved from talk pages. enwiki only.';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		$basetypes = parent::getParameterTypes();
		$types = array(
		    new ComponentParameter('lookupcol', ComponentParameter::PARAMETER_TYPE_STRING, 'Article name column #',
		    		'Column numbers start at 1',
		    		array('size' => 3, 'maxlength' => 3)),
			new ComponentParameter('priority', ComponentParameter::PARAMETER_TYPE_ENUM, 'Multiple class priority', '',
					array('enum' => array('best' => 'Best', "common" => "Most common"))),
			new ComponentParameter('valuetype', ComponentParameter::PARAMETER_TYPE_ENUM, 'Value type', '',
					array('enum' => array('text' => 'Text', "image" => "Image")))
		);

		return array_merge($basetypes, $types);
	}

	/**
	 * Initialize transformer.
	 *
	 * @param array $params Parameters
	 * @param bool $isFirstRowHeaders Is the first row in input data headers?
	 * @return mixed true = success, string = error message
	 */
	public function init($params, $isFirstRowHeaders)
	{
		$this->paramValues = $params;
		$this->firstRowHeaders = $isFirstRowHeaders;

		return true;
	}

	/**
	 * Is the first row column headers?
	 *
	 * @return bool Is the first row column headers?
	 */
	public function isFirstRowHeaders()
	{
		return $this->firstRowHeaders;
	}

	/**
	 * Transform reader data, output to writer.
	 *
	 * @param FlowReader $reader
	 * @param FlowWriter $writer
	 * @return mixed true = success, string = error message
	 */
	public function process(FlowReader $reader, FlowWriter $writer)
	{
		$firstrow = true;
		$column = (int)$this->paramValues['lookupcol'] - 1;
		if ($column < 0) return "Invalid Article name column # {$this->paramValues['lookupcol']}";

		while ($rows = $reader->readRecords()) {
			$pagenames = array();

			// Get the talk namespace for each page
			foreach ($rows as $key => $row) {
				if ($firstrow) {
					$firstrow = false;
					if ($this->isFirstRowHeaders()) {
						$retval = $this->insertColumn($rows[$key], $this->paramValues['title']);
						if ($retval !== true) return $retval;
						continue;
					}
				}

				if ($column >= count($row)) return "Invalid Article name column # {$this->paramValues['lookupcol']}";
				$pagename = preg_replace('/\\[|\\]/u', '', $row[$column]);
				if (strlen($pagename) == 0) return "Row with no page name";
				if ($pagename[0] == ':') $pagename = substr($pagename, 1);

				$namespace = MediaWiki::getNamespaceName($pagename);
				if (strlen($namespace) > 0) $pagename = substr($pagename, strlen($namespace) + 1); // Strip namespace + :
				if (strlen($pagename) == 0) return "Row with no page name";

				if (strlen($namespace) > 5 && substr($namespace, -5) != ' talk') $namespace .= ' talk';
				if (strlen($namespace) == 0) $namespace = 'Talk';

				$nsid = MediaWiki::getNamespaceId($namespace);
				$pagenames[] = array($nsid, str_replace(' ', '_', $pagename), $key, array());
			}

			// Group by namespace
			$namespaces = array();

			foreach ($pagenames as $pagedata) {
				$nsid = $pagedata[0];
				if (! isset($namespaces[$nsid])) $namespaces[$nsid] = array();
				$namespaces[$nsid][$pagedata[2]] = $pagedata; // Using key instead of pagename because could be duplicate pagenames do to page moves.
			}

			// Retrieve the categories
			foreach ($namespaces as $nsid => $pages) {
				$this->retrieveCategories($nsid, $pages);

				foreach ($pages as $pagedata) {
					$class = $this->calculateClass($pagedata[3]);
					$retval = $this->insertColumn($rows[$pagedata[2]], $class);
					if ($retval !== true) return $retval;
				}
			}

			$writer->writeRecords($rows);
		}

		return true;
	}

	/**
	 * Get page categories.
	 *
	 * @param int $nsid Namespace id
	 * @param array $pages Pagedata
	 */
	protected function retrieveCategories($nsid, &$pages)
	{
		$dbh = $this->serviceMgr->getDBConnection('enwiki');
		$escapednames = array();

		foreach ($pages as $pagedata) {
			$escapednames[] = $dbh->quote($pagedata[1]);
		}

		$escapednames = implode(',', $escapednames);

		$sql = "SELECT page_title, cl_to FROM page, categorylinks " .
			" WHERE page_namespace = $nsid AND page_title IN ($escapednames) AND page_id = cl_from";

		$results = $dbh->query($sql);
		$results->setFetchMode(PDO::FETCH_NUM);

		while($row = $results->fetch()) {
			$pagename = $row[0];
			$catname = $row[1];

			foreach ($pages as $key => $page) { // Could be duplicate pagenames.
				if ($page[1] == $pagename) {
					$pages[$key][3][] = $catname;
				}
			}
		}

		$results->closeCursor();
		$results = null;
		$dbh = null;
	}

	/**
	 * Calculate the class.
	 *
	 * @param array $cats
	 * @return string Class or image link
	 */
	protected function calculateClass(&$cats)
	{
		if (! count($cats)) return '';

		$classes = array();

		foreach ($cats as $cat) {
			if (preg_match(self::UNASSESSED_REGEX, $cat) == 1) $class = 'Unassessed';
			elseif (preg_match(self::OTHER_REGEX, $cat, $matches) == 1) $class = $matches[1];
			else continue;

			if ($this->paramValues['priority'] == 'best') {
				if (isset(CreateTables::$CLASSES[$class])) $classes[$class] = CreateTables::$CLASSES[$class];
			} else {
				if (! isset($classes[$class])) $classes[$class] = 0;
				++$classes[$class];
			}
		}

		if (! count($classes)) return '';

		if ($this->paramValues['priority'] == 'best') {
			asort($classes);
		} else { // common
			arsort($classes, SORT_NUMERIC);
		}

		$class = each($classes);
		$class = $class['key'];

		if ($this->paramValues['valuetype'] == 'text') return $class;

		if (! isset(self::$IMAGES[$class])) return '';
		return self::$IMAGES[$class];
	}
}