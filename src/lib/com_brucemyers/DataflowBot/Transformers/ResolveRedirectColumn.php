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
use com_brucemyers\DataflowBot\ComponentParameter;
use PDO;

class ResolveRedirectColumn extends AddColumn
{
	var $firstRowHeaders;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Resolve Redirect Column';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Resolve a redirected article link';
	}

	/**
	 * Get the component identifier.
	 *
	 * @return string ID
	 */
	public function getID()
	{
		return 'RRC';
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
		    new ComponentParameter('linkcol', ComponentParameter::PARAMETER_TYPE_STRING, 'Article name column #',
		    		'Column numbers start at 1',
		    		array('size' => 3, 'maxlength' => 3))
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
		$column = (int)$this->paramValues['linkcol'] - 1;
		if ($column < 0) return "Invalid Article name column # {$this->paramValues['linkcol']}";

		while ($rows = $reader->readRecords()) {
			$pagenames = array();

			// Get the redirect page for each page
			foreach ($rows as $key => $row) {
				if ($firstrow) {
					$firstrow = false;
					if ($this->isFirstRowHeaders()) {
						continue;
					}
				}

				if ($column >= count($row)) return "Invalid Article name column # {$this->paramValues['linkcol']}";
				$pagename = str_replace(' ', '_', $row[$column]);
				if (strlen($pagename) == 0) return "Row with no page name";
				if ($pagename[0] == ':') $pagename = substr($pagename, 1);

				$namespace = MediaWiki::getNamespaceName($pagename);
				if (strlen($namespace) > 0) $pagename = substr($pagename, strlen($namespace) + 1); // Strip namespace + :
				if (strlen($pagename) == 0) return "Row with no page name";

				$nsid = MediaWiki::getNamespaceId($namespace);
				$pagenames[] = array($nsid, $pagename, $key, null);
			}

			// Group by namespace
			$namespaces = array();

			foreach ($pagenames as $pagedata) {
				$nsid = $pagedata[0];
				if (! isset($namespaces[$nsid])) $namespaces[$nsid] = array();
				$namespaces[$nsid][$pagedata[1]] = $pagedata;
			}

			// Retrieve the redirects
			foreach ($namespaces as $nsid => $pages) {
				$this->retrieveRedirects($nsid, $pages);

				foreach ($pages as $pagedata) {
					$redirect = $pagedata[3];
					if (! empty($redirect)) $rows[$pagedata[2]][$column] = $redirect;
				}
			}

			$writer->writeRecords($rows);
		}

		return true;
	}

	/**
	 * Get page redirects.
	 *
	 * @param int $nsid Namespace id
	 * @param array $pages Pagedata
	 */
	protected function retrieveRedirects($nsid, &$pages)
	{
		$dbh = $this->serviceMgr->getDBConnection('enwiki');
		$escapednames = array();

		foreach ($pages as $pagedata) {
			$escapednames[] = $dbh->quote($pagedata[1]);
		}

		$escapednames = implode(',', $escapednames);

		$sql = "SELECT page_title, rd_namespace, rd_title FROM page, redirect " .
			" WHERE page_namespace = $nsid AND page_is_redirect = 1 AND page_title IN ($escapednames) AND page_id = rd_from";

		$results = $dbh->query($sql);
		$results->setFetchMode(PDO::FETCH_NUM);

		while($row = $results->fetch()) {
			$pagename = $row[0];
			$rdnsid = $row[1];
			$rdtitle = $row[2];
			$pages[$pagename][3] = MediaWiki::getLinkSafeNamespacePrefix($rdnsid) . $rdtitle;
		}

		$results->closeCursor();
		$results = null;
		$dbh = null;
	}
}