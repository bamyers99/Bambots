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

use com_brucemyers\DataflowBot\Component;
use com_brucemyers\DataflowBot\io\FlowReader;
use com_brucemyers\DataflowBot\io\FlowWriter;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\DataflowBot\ComponentParameter;

class AddColumnFirstImage extends AddColumn
{
	var $firstRowHeaders;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Add Column First Page Image';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Add a new column with the first image on a page. enwiki only.';
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
		    new ComponentParameter('fileoptions', ComponentParameter::PARAMETER_TYPE_STRING, 'File: options', 'ie. |left|100x100px',
		    		array('size' => 10, 'maxlength' => 64)),
			new ComponentParameter('nonfree', ComponentParameter::PARAMETER_TYPE_ENUM, 'Display non-free images', '',
					array('enum' => array('no' => 'No', 'link' => 'Link only', 'yes' => 'Yes')))
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
		$includenonfree = $this->paramValues['nonfree'];
		$firstrow = true;
		$firstrow2 = true;
		$column = (int)$this->paramValues['lookupcol'] - 1;
		if ($column < 0) return "Invalid Article name column # {$this->paramValues['lookupcol']}";
		$fileoptions = $this->paramValues['fileoptions'];
		if (strlen($fileoptions) > 0 && $fileoptions[0] != '|') $fileoptions = '|' . $fileoptions;

		while ($rows = $reader->readRecords()) {
			$pagenames = array();

			// Gather the pagenames
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

				$pagenames[] = $pagename;
			}

			// Cache the pages
			$wiki = $this->serviceMgr->getMediaWiki('enwiki');
			$wiki->cachePages($pagenames);

			// Process each page

			foreach ($rows as $key => $row) {
				if ($firstrow2) {
					$firstrow2 = false;
					if ($this->isFirstRowHeaders()) {
						continue;
					}
				}

				$pagename = preg_replace('/\\[|\\]/u', '', $row[$column]);
				if ($pagename[0] == ':') $pagename = substr($pagename, 1);
				$data = $wiki->getPageWithCache($pagename);

				// Strip comments
        		$data = preg_replace('/<!--.*?-->/us', '', $data);

        		$value = '';

        		// Look for '[[file:' or '|...='; no [ in filename to prevent = [[File: from matching
        		$regex = '/(?:\\[\\[\\s*(?:file|image)\\s*:|\\|\\s*\w+\\s*=)\\s*([^{[|]+?\\.(?:jpg|jpeg|gif|svg|png))/iu';
        		if (preg_match($regex, $data, $matches) == 1) {
        			$filename = $matches[1];
        			$imageok = true;
        			if ($includenonfree != 'yes') $imageok = $this->isImageFree($filename);
        			if ($imageok) $value = "[[File:$filename$fileoptions]]";
        			elseif ($includenonfree == 'link') $value = "[[:File:$filename$fileoptions]]";
        		}

				$retval = $this->insertColumn($rows[$key], $value);
				if ($retval !== true) return $retval;
			}

			$writer->writeRecords($rows);
		}

		return true;
	}

	/**
	 * Check an images free status.
	 *
	 * @param string $filename Image filename
	 * @return bool true = free
	 */
	protected function isImageFree($filename)
	{
		$filename = str_replace(' ', '_', $filename);

		$dbh = $this->serviceMgr->getDBConnection('enwiki');
		$sql = "SELECT page.page_id FROM categorylinks as cl, page WHERE cl.cl_from = page.page_id AND
    			page.page_namespace = 6 AND page.page_title = ? AND cl.cl_to = 'All_non-free_media'";

		$sth = $dbh->prepare($sql);
		$sth->bindParam(1, $filename);
		$sth->execute();

		$imageFree = true;
		if ($sth->fetch()) $imageFree = false;

    	$sth->closeCursor();
    	$sth = null;
		$dbh = null;

		return $imageFree;
	}
}