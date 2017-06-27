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
	const TYPE_OFFICIAL_WEBSITE = 'P856';

	static public $preferred_langs = array('en','de','es','fr','it');

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
	 * @return string empty if no item
	 */
	public function getId()
	{
		if (isset($this->data['id'])) return $this->data['id'];
		return '';
	}

	/**
	 * Get the datatype of a property.
	 *
	 * @return string empty if no datatype
	 */
	public function getDatatype()
	{
		if (isset($this->data['datatype'])) return $this->data['datatype'];
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

				$statements[] = $this->decodeValue($type, $value);
			}
		}

		return $statements;
	}

	/**
	 * Decode a value
	 *
	 * @param string $type
	 * @param mixed $value
	 */
	protected function decodeValue($type, $value)
	{
		switch ($type) {
			case 'wikibase-entityid':
				$entity_type = $value['entity-type'];
				$entity_id = $value['numeric-id'];
				return self::$entity_types[$entity_type] . $entity_id;
				break;

			case 'string':
				return $value;
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

				while (strlen($date) && $date[0] == '0') $date = substr($date, 1);

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
					case '9': // year
						$parts = explode('-', $date);
						$value = $parts[0] . $bce;
						break;
					case '8': // decade
						$parts = explode('-', $date);
						$value = $parts[0] . 's' . $bce;
						break;
					case '7': // century
						$parts = explode('-', $date);
						$parts[0] = str_pad($parts[0], 4, '0', STR_PAD_LEFT);
						$value = substr($parts[0], 0, 2) . ' century' . $bce;
						break;
					case '6': // millennium
						$parts = explode('-', $date);
						$parts[0] = str_pad($parts[0], 4, '0', STR_PAD_LEFT);
						$value = substr($parts[0], 0, 1) . ' millenium' . $bce;
						break;
					default:
						$parts = explode('-', $date);
						$value = $parts[0] . $bce;
						break;
				}

				return $value;
				break;

			default:
				Logger::log("WikidataItem::decodeValue unknown value type='$type' value=$value");
				break;
		}
	}

	/**
	 * Get a statements qualifiers.
	 *
	 * @param string $property_id
	 * @param int $occurrence
	 * @return array(propid => array(values))
	 */
	public function getStatementQualifiers($property_id, $occurrence)
	{
		$occurrence = (int)$occurrence;
		$qualifiers = array();

		if (! isset($this->data['claims']) || ! isset($this->data['claims'][$property_id])) return $qualifiers;
		if (count($this->data['claims'][$property_id]) < $occurrence + 1) return $qualifiers;

		$claim = $this->data['claims'][$property_id][$occurrence];

		if (! isset($claim['qualifiers'])) return $qualifiers;

		foreach ($claim['qualifiers'] as $key => $values) {
			foreach ($values as $val) {
				if ($val['snaktype'] == 'value') {
					$type = $val['datavalue']['type'];
					$value= $val['datavalue']['value'];

					if (! isset($qualifiers[$key])) $qualifiers[$key] = array();
					$qualifiers[$key][] = $this->decodeValue($type, $value);
				}
			}
		}

		return $qualifiers;
	}

	/**
	 * Get an items label or description in a specific language. If language not found, return en or first language.
	 *
	 * @param string $type label or description
	 * @param string $lang preferred language code
	 * @return string label or description
	 */
	public function getLabelDescription($type, $lang)
	{
		$retprefs = array();
		if ($type == 'label') $type = 'labels';
		else $type = 'descriptions';

		if (empty($this->data[$type])) return '';

		foreach ($this->data[$type] as $text) {
			if ($text['language'] == $lang) return $text['value'];
			if (in_array($text['language'], self::$preferred_langs)) $retprefs[$text['language']] = $text['value'];
			elseif (! isset($ret)) $ret = $text['value'];
		}

		if (! empty($retprefs)) {
			foreach (self::$preferred_langs as $preflang) {
				if (isset($retprefs[$preflang])) return $retprefs[$preflang];
			}
		}

		return $ret;
	}

	/**
	 * Get a site link
	 *
	 * @param string $site preferred site/wiki, If site not found, return enwiki or first site
	 * @return array keys = site, title
	 */
	public function getSiteLink($site)
	{
		$reten = array();

		if (empty($this->data['sitelinks'])) return array();

		foreach ($this->data['sitelinks'] as $sitelink) {
			if (! preg_match('![a-z]{2,3}wiki!', $sitelink['site'])) continue;
			if ($sitelink['site'] == $site) return array('site' => $site, 'title' => $sitelink['title']);
			if ($sitelink['site'] == 'enwiki') $reten = array('site' => 'enwiki', 'title' => $sitelink['title']);
			elseif (! isset($ret)) $ret = array('site' => $sitelink['site'], 'title' => $sitelink['title']);
		}

		if (! empty($reten)) return $reten;
		return $ret;
	}
}