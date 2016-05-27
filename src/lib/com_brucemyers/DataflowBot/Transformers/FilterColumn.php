<?php
/**
 Copyright 2016 Myers Enterprises II

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
use com_brucemyers\DataflowBot\ComponentParameter;
use com_brucemyers\MediaWiki\MediaWiki;
use PDO;

class FilterColumn extends Transformer
{
	var $paramValues;
	var $firstRowHeaders;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Filter Column';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Filter a column by regex';
	}

	/**
	 * Get the component identifier.
	 *
	 * @return string ID
	 */
	public function getID()
	{
		return 'FltrCol';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
			new ComponentParameter('filtercol', ComponentParameter::PARAMETER_TYPE_STRING, 'Filter column #',
		    	'Column numbers start at 1',
		    	array('size' => 5, 'maxlength' => 6)),
			new ComponentParameter('includeregex', ComponentParameter::PARAMETER_TYPE_STRING, 'Include if matches regular expression',
		    	'! must be escaped',
		    	array('size' => 30, 'maxlength' => 256)),
			new ComponentParameter('excluderegex', ComponentParameter::PARAMETER_TYPE_STRING, 'Exclude if matches regular expression',
		    	'! must be escaped',
		    	array('size' => 30, 'maxlength' => 256)),
			new ComponentParameter('disambig', ComponentParameter::PARAMETER_TYPE_ENUM, 'Include disambiguation pages', '',
					array('enum' => array('no' => 'No', 'yes' => 'Yes')))
		);
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
		$colnum = (int)$this->paramValues['filtercol'] - 1;
		if ($colnum < 0) return "Invalid filter column #";
		$includeregex = false;
		$excluderegex = false;
		$disambig = false;
		if (isset($this->paramValues['includeregex'])) $includeregex = $this->paramValues['includeregex'];
		if (isset($this->paramValues['excluderegex'])) $excluderegex = $this->paramValues['excluderegex'];
		if (isset($this->paramValues['disambig'])) $disambig = $this->paramValues['disambig'];

		while ($rows = $reader->readRecords()) {
			if ($disambig !== false) {
				$disambigs = $this->getDisambigs($rows, $colnum, $firstrow);
			}

			foreach ($rows as $row) {
				if ($firstrow) {
					$firstrow = false;
					if ($this->isFirstRowHeaders()) {
						$rows2 = array($row);
						$writer->writeRecords($rows2);
						continue;
					}
				}

				if ($colnum >= count($row)) return "Invalid filter column #";

				$includeRecord = true;

				if ($includeregex !== false && ! preg_match("!$includeregex!u", $row[$colnum])) {
						$includeRecord = false;
				}

				if ($excluderegex !== false && preg_match("!$excluderegex!u", $row[$colnum])) {
						$includeRecord = false;
				}

				if ($disambig !== false) {
					$pagetitle = str_replace(' ', '_', $row[$colnum]);
					$pageIsDAB = in_array($pagetitle, $disambigs);

					if (($disambig == 'no' && $pageIsDAB) || ($disambig == 'yes' && ! $pageIsDAB)) {
						$includeRecord = false;
					}
				}

				if ($includeRecord) {
					$rows2 = array($row);
					$writer->writeRecords($rows2);
				}
			}
		}

		return true;
	}

	/**
	 * Get disambiguation page titles.
	 *
	 * @param array $rows
	 * @param int $colnum
	 * @param bool $firstrow
	 * @return array disambig page titles
	 */
	protected function getDisambigs($rows, $colnum, $firstrow)
	{
		foreach ($rows as $key => $row) {
			if ($firstrow) {
				$firstrow = false;
				if ($this->isFirstRowHeaders()) {
					continue;
				}
			}

			if ($colnum >= count($row)) return array();
			$pagename = str_replace(' ', '_', $row[$colnum]);
			if (strlen($pagename) == 0) return array();
			if ($pagename[0] == ':') $pagename = substr($pagename, 1);

			$namespace = MediaWiki::getNamespaceName($pagename);
			if (strlen($namespace) > 0) $pagename = substr($pagename, strlen($namespace) + 1); // Strip namespace + :
			if (strlen($pagename) == 0) return array();

			$nsid = MediaWiki::getNamespaceId($namespace);
			$pagenames[] = array($nsid, $pagename, null);
		}

		// Group by namespace
		$namespaces = array();

		foreach ($pagenames as $pagedata) {
			$nsid = $pagedata[0];
			if (! isset($namespaces[$nsid])) $namespaces[$nsid] = array();
			$namespaces[$nsid][$pagedata[1]] = $pagedata;
		}

		$disambigs = array();

		// Retrieve the disambigs
		foreach ($namespaces as $nsid => $pages) {
			$this->retrieveDisambigs($nsid, $pages);

			foreach ($pages as $pagedata) {
				$disambig = $pagedata[2];
				if (! empty($disambig)) $disambigs[] = $disambig;
			}
		}

		return $disambigs;
	}

	/**
	 * Get disambiguations
	 *
	 * @param int $nsid Namespace id
	 * @param array $pages Pagedata
	 */
	protected function retrieveDisambigs($nsid, &$pages)
	{
		$dbh = $this->serviceMgr->getDBConnection('enwiki');
		$escapednames = array();

		foreach ($pages as $pagedata) {
			$escapednames[] = $dbh->quote($pagedata[1]);
		}

		$escapednames = implode(',', $escapednames);

		$sql = "SELECT page_title FROM page, page_props " .
			" WHERE page_namespace = $nsid AND page_title IN ($escapednames) AND page_id = pp_page " .
			" AND pp_propname = 'disambiguation' ";

		$results = $dbh->query($sql);
		$results->setFetchMode(PDO::FETCH_NUM);

		while($row = $results->fetch()) {
			$pagename = $row[0];
			$pages[$pagename][2] = MediaWiki::getLinkSafeNamespacePrefix($nsid) . $pagename;
		}

		$results->closeCursor();
		$results = null;
		$dbh = null;
	}
}