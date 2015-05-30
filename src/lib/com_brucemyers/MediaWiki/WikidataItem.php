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

namespace com_brucemyers\MediaWiki;

use com_brucemyers\Util\Logger;


/**
 * Wikidata item
 */
class WikidataItem
{
	const TYPE_INSTANCE_OF = 'P31';
	const TYPE_BIRTHDATE = 'P569';
	const TYPE_DEATHDATE = 'P570';
	const TYPE_AUTHCTRL_VIAF = 'P214';
	const TYPE_AUTHCTRL_ISNI = 'P213';
	const TYPE_AUTHCTRL_ORCID = 'P496';
	const TYPE_AUTHCTRL_LCAuth = 'P244';
	const TYPE_AUTHCTRL_ULAN = 'P245';
	const TYPE_AUTHCTRL_IMDb = 'P345';
	const TYPE_AUTHCTRL_MusicBrainz = 'P434';

	const INSTANCE_OF_DISAMBIGUATION = 'Q4167410';

	static $entity_types = array(
			'item' => 'Q',
			'property' => 'P'
	);

	protected $data;

	/**
	 * Constructor
	 *
	 * @param array $data
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * Get the item id.
	 *
	 * @return string blank if no item
	 */
	public function getId()
	{
		if (isset($this->data['id'])) return $this->data['id'];
		return '';
	}

	/**
	 * Get statements by type
	 *
	 * @param string $property_id Property id
	 * @return array string Statements
	 */
	public function getStatementsOfType($property_id)
	{
		$statements = array();

		if (! isset($this->data['claims']) || ! isset($this->data['claims'][$property_id])) return $statements;

		foreach ($this->data['claims'][$property_id] as $claim) {
			if ($claim['type'] == 'statement' && $claim['mainsnak']['snaktype'] == 'value') {
				$type = $claim['mainsnak']['datavalue']['type'];
				$value= $claim['mainsnak']['datavalue']['value'];

				switch ($type) {
				    case 'wikibase-entityid':
						$entity_type = $value['entity-type'];
						$entity_id = $value['numeric-id'];
						$statements[] = self::$entity_types[$entity_type] . $entity_id;
				    	break;

				    case 'string':
				    	$statements[] = $value;
				    	break;

				    case 'time':
				    	$time = '';
				    	$datetime = explode('T', $value['time']);
				    	$date = $datetime[0];
				    	if (isset($datetime[1])) $time = $datetime[1];

				    	$bce = '';
				    	if ($date[0] == '+') $date = substr($date, 1);
				    	elseif ($date[0] == '-') {
				    		$date = substr($date, 1);
				    		$bce = ' BCE';
				    	}

				    	$precision = $value['precision'];
				    	switch ($precision) {
				    	    case '14': // second
				    	    	$value = $date . ' ' . $time . $bce;
				    	    	break;
				    	    case '13': // minute
				    	    	$value = $date . ' ' . substr($time, 0, 5) . $bce;
				    	    	break;
				    		case '12': // hour
				    	    	$value = $date . ' ' . substr($time, 0, 2) . ':00' . $bce;
				    	    	break;
				    		case '11': // day
				    	    	$value = $date . $bce;
				    	    	break;
				    		case '10': // month
				    			$parts = explode('-', $date);
				    	    	$value = $parts[0] . '-' . $parts[1] . $bce;
				    	    	break;
				    		default:
				    			$parts = explode('-', $date);
				    	    	$value = $parts[0] . $bce;
				    	    	break;
				    	}

				    	$statements[] = $value;
				    	break;

				    default:
				    	Logger::log("WikidataItem::getStatementsOfType($property_id) unknown value type='$type' value=$value");
				    	break;
				}
			}
		}

		return $statements;
	}

	/**
	 * Get an items label or description in a specific language. If language not found, return first language.
	 *
	 * @param string $type label or description
	 * @param string $lang preferred language code
	 * @return string label or description
	 */
	public function getLabelDescription($type, $lang)
	{
		if ($type == 'label') $type = 'labels';
		else $type = 'descriptions';

		if (empty($this->data[$type])) return '';

		foreach ($this->data[$type] as $text) {
			if ($text['language'] == $lang) return $text['value'];
			if (! isset($ret)) $ret = $text['value'];
		}

		return $ret;
	}
}