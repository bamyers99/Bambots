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

namespace com_brucemyers\DataflowBot\Extractors;

use com_brucemyers\DataflowBot\io\FlowWriter;
use com_brucemyers\DataflowBot\ComponentParameter;
use PDO;

class SQLQuery extends Extractor
{
	var $paramValues;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'SQL Query';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Run a SQL query and retrieve the resultset';
	}

	/**
	 * Get the component identifier.
	 *
	 * @return string ID
	 */
	public function getID()
	{
		return 'SQLQ';
	}

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public function getParameterTypes()
	{
		return array(
			new ComponentParameter('wiki', ComponentParameter::PARAMETER_TYPE_ENUM, 'Wiki', '',
				array('enum' => array('enwiki' => 'English Wikipedia', 'wikidatawiki' => 'Wikidata'))),
			new ComponentParameter('sql', ComponentParameter::PARAMETER_TYPE_STRING, 'SQL', '',
				array('rows' => 20, 'cols' => 130, 'maxlength' => 2048))
		);
	}

	/**
	 * Initialize extractor.
	 *
	 * @param array $params Parameters
	 * @return mixed true = success, string = error message
	 */
	public function init($params)
	{
		$this->paramValues = $params;

		return true;
	}

	/**
	 * Is the first row column headers?
	 *
	 * @return bool Is the first row column headers?
	 */
	public function isFirstRowHeaders()
	{
		return true;
	}

	/**
	 * Extract data and write to writer.
	 *
	 * @param FlowWriter $writer
	 * @return mixed true = success, string = error message
	 */
	public function process(FlowWriter $writer)
	{
		$dbh = $this->serviceMgr->getDBConnection($this->paramValues['wiki']);
		$results = $dbh->query($this->paramValues['sql']);
		if ($results === false) {
			$errinfo = $dbh->errorInfo();
			return 'Sql query failed: ' . $errinfo[2];
		}

		$headers = array();
		$colcnt = $results->columnCount();
		for ($x = 0; $x < $colcnt; ++$x) {
			$metadata = $results->getColumnMeta($x);
			$headers[] = $metadata['name'];
		}

		$rows = array($headers);
		$writer->writeRecords($rows);

		$results->setFetchMode(PDO::FETCH_NUM);

		while($row = $results->fetch()) {
			$rows = array($row);
			$writer->writeRecords($rows);
		}

		$results->closeCursor();
		$results = null;
		$dbh = null;

		return true;
	}
}