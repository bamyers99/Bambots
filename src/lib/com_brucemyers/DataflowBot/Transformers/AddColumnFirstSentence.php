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

class AddColumnFirstSentence extends AddColumn
{
	var $firstRowHeaders;

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public function getTitle()
	{
		return 'Add Column First Sentence';
	}

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public function getDescription()
	{
		return 'Add a new column with the first sentence on a page. enwiki only.';
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
		$firstrow2 = true;
		$column = (int)$this->paramValues['lookupcol'] - 1;
		if ($column < 0) return "Invalid Article name column # {$this->paramValues['lookupcol']}";

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

        		$value = $this->getFirstSentence($data);

				$retval = $this->insertColumn($rows[$key], $value);
				if ($retval !== true) return $retval;
			}

			$writer->writeRecords($rows);
		}

		return true;
	}

	/**
	 * Get the first sentence.
	 *
	 * @param string $data Page data
	 * @return string First sentence
	 */
	protected function getFirstSentence($data)
	{
		$sentence = '';
		$bracedepth = 0;
		$bracketdepth = 0;
		$prevchar = '';
		$bracketed = '';

		// Strip comments and refs
		$data = preg_replace(array('/<!--.*?-->/us', '!<ref.*?</ref>!us'), '', $data);

		$len = mb_strlen($data, 'UTF-8');

		for ($x = 0; $x < $len; ++$x) {
			$char = mb_substr($data, $x, 1, 'UTF-8');
			$newlen = mb_strlen($sentence, 'UTF-8');

			// Skip templates and [[File: at the beginning of the page
			if ($newlen == 0 && $char == '[' && ! $bracedepth) {
				$bracketed .= $char;
				++$bracketdepth;
			}

			elseif ($newlen == 0 && $char == ']' && ! $bracedepth) {
				$bracketed .= $char;
				--$bracketdepth;
				if (! $bracketdepth) {
					if (! preg_match('/\\[\\[\\s*file\\s*:/ui', $bracketed)) {
						$sentence .= $bracketed;
					}

					$bracketed = '';
				}
			}

			elseif ($newlen == 0 && $bracketdepth && ! $bracedepth) $bracketed .= $char;

			elseif ($newlen == 0 && $char == '{') ++$bracedepth;

			elseif ($newlen == 0 && $char == '}') --$bracedepth;

			elseif ($newlen == 0 && preg_match('/\s/u', $char)) { /* skip whitespace */ }

			elseif ($bracedepth == 0) {
				if ($char == '.' || ($char == "\n" && $prevchar == "\n")) {
					if ($newlen < 100) {
						if ($char == '.') $sentence .= $char;
						$prevchar = $char;
						continue;
					}

					$sentence .= '.';
					break;
				}

				$sentence .= $char;
			}

			$prevchar = $char;
		}

		return trim($sentence);
	}
}
