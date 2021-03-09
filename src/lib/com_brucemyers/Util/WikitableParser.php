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

namespace com_brucemyers\Util;

/**
 * WikitableParser
 */
class WikitableParser
{
	const REGEX_NOWIKI = '!<\s*nowiki[^>]*>[^<]*?<\s*/\s*nowiki\s*>!ui';
	const REGEX_TABLE_START = '!^(?:\s|:)*\{\|!u';
	const REGEX_TABLE_CAPTION = '!^\s*\|\+!u';
	const REGEX_TABLE_NEWROW = '!^\s*\|\-!u';
	const REGEX_TABLE_HEADING = '/^\s*!/u';
	const REGEX_TABLE_DATA = '!^\s*\|\|?!u';
	const REGEX_TABLE_END = '!^\s*\|\}!u';
	const REGEX_ATTRIBS = '!(\w+)(?:\s*=\s*(?:"[^"]*+"|\'[^\']*+\'|[^\'"\s]+))?!u';
	const REGEX_ATTRIB_VALUE = '!^\s*=\s*("[^"]*+"|\'[^\']*+\'|[^\'"\s]+)!u';
	const REGEX_WIKILINK = '/\[\[(?:.(?!\[\[))+?\]\]/s';
	const REGEX_TEMPLATE = '!\{\{\s*(?P<content>(?P<name>[^{}\|]+?)(?:\|(?P<params>[^{}]+?)?)?\}\})!u';
	
	const NO_TABLE = 0;
	const IN_TABLE = 1;

	const ROWTYPE_CAPTION = 0;
	const ROWTYPE_HEADINGS = 1;
	const ROWTYPE_DATA = 2;

	/**
	 * Get wikitables
	 *
	 * TODO: colspans are repeated
	 * TODO: rowspans are duplicated
	 *
	 * @param string $origdata
	 * @return array Tables array('attribs' => array('name' => 'value'), 'headings' => array of string, 'rows' => array of array of string)
	 */
	public static function getTables($origdata)
	{
		$markers = [];
		$tables = [];
		$data = preg_replace(CommonRegex::COMMENT_REGEX, '', $origdata); // Strip comments

		// Replace nowiki/wikilink/template with markers

		foreach ([self::REGEX_NOWIKI, self::REGEX_TEMPLATE, self::REGEX_WIKILINK] as $regex) {
		    $match_cnt = preg_match_all($regex, $data, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		    $offset_adjust = 0;
		    
    		if ($match_cnt) {
    			foreach ($matches as $match) {
    				// Replace the match with a marker
    				$marker_id = "\v" . count($markers) . "\f";
    				$content = $match[0][0];
    				$content_len = strlen($content);
    				$offset = $match[0][1] - $offset_adjust;
    				$offset_adjust += $content_len - strlen($marker_id);
    				
    				$data = substr_replace($data, $marker_id, $offset, $content_len);

    				$markers[$marker_id] = $content;
    			}
    		}
		}

		// Process each line

		$lines = preg_split('!\\r?\\n!', $data);
		$state = self::NO_TABLE;

		foreach ($lines as $line) {
			$line .= "\n"; // re-add newline for multi-line cell data

			switch ($state) {
				case self::NO_TABLE:
					if (preg_match(self::REGEX_TABLE_START, $line, $matches)) {
						$line = substr($line, strlen($matches[0]));
						$attribs = self::getAttribs($line, $markers);
						$headings = [];
						$rows = [];
						$currow = [];
						$state = self::IN_TABLE;
						$rowtype = self::ROWTYPE_DATA;
						$depth = 0;
						break;
					}
					break;

				case self::IN_TABLE:
					$continuation = false;

					if (preg_match(self::REGEX_TABLE_END, $line)) {
						if (! $depth) {
							self::saveRow($currow, $rowtype, $rows, $headings, $attribs, $markers);

							$tables[] = ['attribs' => $attribs, 'headings' => $headings, 'rows' => $rows];
							$state = self::NO_TABLE;
							break;
						}

						--$depth;
						$continuation = true;
						// fall thru
					} elseif (preg_match(self::REGEX_TABLE_NEWROW, $line) && ! $depth) {
						self::saveRow($currow, $rowtype, $rows, $headings, $attribs, $markers);

						$rowtype = self::ROWTYPE_DATA;
						break;
					} elseif (preg_match(self::REGEX_TABLE_CAPTION, $line, $matches) && ! $depth) {
						$rowtype = self::ROWTYPE_CAPTION;
						$line = substr($line, strlen($matches[0]));
						// fall thru
					} elseif (preg_match(self::REGEX_TABLE_HEADING, $line, $matches) && ! $depth) {
						if ($rowtype == self::ROWTYPE_CAPTION) {
							self::saveRow($currow, $rowtype, $rows, $headings, $attribs, $markers);
						}
						$rowtype = self::ROWTYPE_HEADINGS;
						$line = substr($line, strlen($matches[0]));
						// fall thru
					} elseif (preg_match(self::REGEX_TABLE_DATA, $line, $matches) && ! $depth) {
						if ($rowtype == self::ROWTYPE_CAPTION) {
							self::saveRow($currow, $rowtype, $rows, $headings, $attribs, $markers);
						}
						$rowtype = self::ROWTYPE_DATA;
						$line = substr($line, strlen($matches[0]));
						// fall thru
					} elseif (preg_match(self::REGEX_TABLE_START, $line, $matches)) {
						++$depth;
						$continuation = true;
						// fall thru
					} else {
						$continuation = true;
					}

					if ($rowtype == self::ROWTYPE_HEADINGS && ! $depth) {
						if (strpos($line, '||') !== false) $rowtype = self::ROWTYPE_DATA;
						if (strpos($line, '!!') !== false || $rowtype == self::ROWTYPE_DATA) {
							$currow = array_merge($currow, preg_split('/(?:!!|\|\|)/u', $line));
							break;
						}
					}

					if (strpos($line, '||') !== false && ! $depth) {
						$currow = array_merge($currow, preg_split('!\|\|!u', $line));
						break;
					}

					if ($continuation && ! empty($currow)) {
						$currow[count($currow) - 1] .= $line;
					} else {
						$currow[] = $line;
					}

					break;
			}
		}

		return $tables;
	}

	protected static function saveRow(&$currow, $rowtype, &$rows, &$headings, &$attribs, $markers)
	{
		if (empty($currow)) return;

		// Strip attributes
		foreach ($currow as $key => $cell) {
			if (preg_match('!^[^\n\|]*\|!u', $cell)) { // Only check first line
				$barpos = strpos($cell, '|');
				if ($barpos !== false) $cell = substr($cell, $barpos + 1);
			}

			// Replace any markers in the cell
			preg_match_all("!\v\\d+\f!", $cell, $marker_matches);
			foreach ($marker_matches[0] as $marker_match) {
				$cell = str_replace($marker_match, $markers[$marker_match], $cell);
			}

			$currow[$key] = trim($cell);
		}

		if ($rowtype == self::ROWTYPE_HEADINGS) $headings = $currow;
		elseif ($rowtype == self::ROWTYPE_CAPTION && ! empty($currow[0])) $attribs['caption'] = $currow[0];
		else $rows[] = $currow;

		$currow = [];
	}

	/**
	 * Get html style attributes.
	 *
	 * @param string $data
	 * @param array(string) $markers
	 * @return array('name' => 'value')
	 */
	protected static function getAttribs($data, $markers)
	{
		$match_cnt = preg_match_all(self::REGEX_ATTRIBS, $data, $matches, PREG_SET_ORDER);
		if (! $match_cnt) return [];

		$attribs = [];

		foreach ($matches as $match) {
			$attrib_name = $match[1];
			$attrib_value = substr($match[0], strlen($attrib_name));
			if (preg_match(self::REGEX_ATTRIB_VALUE, $attrib_value, $matches2)) {
				$attrib_value = $matches2[1];
				if ($attrib_value[0] == '"' || $attrib_value == "'") {
					$attrib_value = substr($attrib_value, 1, -1);
				}
			}

			// Replace any markers in the name
			preg_match_all("!\v\\d+\f!", $attrib_name, $marker_matches);
			foreach ($marker_matches[0] as $marker_match) {
				$attrib_name = str_replace($marker_match, $markers[$marker_match], $attrib_name);
			}

			// Replace any markers in the value
			preg_match_all("!\v\\d+\f!", $attrib_value, $marker_matches);
			foreach ($marker_matches[0] as $marker_match) {
				$attrib_value = str_replace($marker_match, $markers[$marker_match], $attrib_value);
			}

			$attribs[$attrib_name] = $attrib_value;
		}

		return $attribs;
	}
}