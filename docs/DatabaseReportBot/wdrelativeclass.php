<?php
/**
 Copyright 2020 Myers Enterprises II

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

$count = 0;
$pass = '1';

if (isset($argv[1])) $pass = $argv[1];

$classes = [
    'human' => ['Q5'],
    'horse' => ['Q726','Q2442470','Q1897960','Q757833','Q1104034']
];

$relations = ['P22','P25','P26','P40','P451','P1038','P1290','P3373','P3448','P8810'];

$hndl = fopen('php://stdin', 'r');

if ($pass == '1') {
    while (! feof($hndl)) {
    	if (++$count % 100000 == 0) echo "Processed $count\n";
    	$buffer = fgets($hndl);
    	if (empty($buffer)) continue;
    	$buffer = substr($buffer, 30); // /mediawiki/page/revision/text=
    	$item = new WikidataItem(json_decode($buffer, true));

        $qid = $item->getId();
        if (empty($qid)) continue;

        $instanceofs = $item->getStatementsOfType(WikidataItem::TYPE_INSTANCE_OF);
        if (empty($instanceofs)) continue;

        $checkit = false;

        foreach ($classes as $type => $classqids) {
            foreach ($classqids as $classqid) {
                foreach ($instanceofs as $instanceof) {
                    if ($instanceof == $classqid) {
                        $checkit = true;
                        break 3;
                    }
                }
            }
        }

        if (! $checkit) continue;

        foreach ($relations as $relation) {
            $relatives = $item->getStatementsOfType($relation);

            foreach ($relatives as $relative) {
                $fileid = floor(intval(substr($relative, 1)) / 1000000);
                $whndl = fopen("wdr$fileid.tsv", 'a');
                fwrite($whndl, "$qid\t$type\t$relation\t$relative\n");
                fclose($whndl);
            }
        }

    }
} else { // pass 2
    $prev_fileid = -1;

    $whndl = fopen('wdrelativeclass.tsv', 'w');

    while (! feof($hndl)) {
        if (++$count % 100000 == 0) echo "Processed $count\n";
        $buffer = fgets($hndl);
        if (empty($buffer)) continue;
        $buffer = substr($buffer, 30); // /mediawiki/page/revision/text=
        $item = new WikidataItem(json_decode($buffer, true));

        $qid = $item->getId();
        if (empty($qid)) continue;

        $instanceofs = $item->getStatementsOfType(WikidataItem::TYPE_INSTANCE_OF);
        if (empty($instanceofs)) continue;

        $fileid = intval(floor(intval(substr($qid, 1)) / 1000000));

        if ($fileid != $prev_fileid) {
            $targets = [];
            $tocheck = file_get_contents("wdr$fileid.tsv");
            $tocheck = explode("\n", $tocheck);

            foreach ($tocheck as $target) {
                $target = explode("\t", $target); // "$qid\t$type\t$relation\t$relative\n"
                $targets[$target[3]] = ['f' => $target[0], 't' => $target[1], 'r' => $target[2]];
            }

            $prev_fileid = $fileid;
        }

        if (isset($targets[$qid])) {
            $type = $targets[$qid]['t'];
            $validqids = $classes[$type];

            if (empty(array_intersect($validqids, $instanceofs))) {
                $from = $targets[$qid]['f'];
                $relation = $targets[$qid]['r'];
                fwrite($whndl, "$from\t$type\t$relation\t$qid\n");
            }
        }
    }

    fclose($whndl);
}

echo "Processed $count\n";

fclose($hndl);

/**
 * Wikidata item
 */
class WikidataItem
{
	const TYPE_INSTANCE_OF = 'P31';

	static public $preferred_langs = ['mul','en','de','es','fr','it','pt'];

	static public $quantity_units = [
	    'Q573' => 'day',
	    'Q23387' => 'week',
	    'Q5151' => 'month',
	    'Q577' => 'year',
	    'Q100995' => 'pound',
	    'Q11570' => 'kilogram',
	    'Q41803' => 'gram',
	    'Q48013' => 'ounce'
	];

	static $entity_types = [
		'item' => 'Q',
		'property' => 'P'
	];

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
		elseif (isset($this->data['entity'])) return $this->data['entity'];
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
		$statements = [];

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

			case 'quantity':
			    $amount = $value['amount'];
			    $unit = $value['unit'];

			    if ($unit == '1') $unit = '';
			    else {
			        if (preg_match('!entity/(Q\\d+)!', $unit, $matches)) {
                        $unit = $matches[1];
			        }

			        if (isset(self::$quantity_units[$unit])) {
			            $unit = ' ' . self::$quantity_units[$unit];
			        } else {
			            $unit = " unknown unit ($unit)";
			        }
			    }

			    if (isset($value['lowerBound'])) {
			        $lower_upper = " ({$value['lowerBound']} - {$value['upperBound']})";
			    } else {
			        $lower_upper = '';
			    }

			    return "$amount$lower_upper$unit";
			    break;

			default:
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
		$qualifiers = [];

		if (! isset($this->data['claims']) || ! isset($this->data['claims'][$property_id])) return $qualifiers;
		if (count($this->data['claims'][$property_id]) < $occurrence + 1) return $qualifiers;

		$claim = $this->data['claims'][$property_id][$occurrence];

		if (! isset($claim['qualifiers'])) return $qualifiers;

		foreach ($claim['qualifiers'] as $key => $values) {
			foreach ($values as $val) {
				if ($val['snaktype'] == 'value') {
					$type = $val['datavalue']['type'];
					$value= $val['datavalue']['value'];

					if (! isset($qualifiers[$key])) $qualifiers[$key] = [];
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
		$retprefs = [];
		if ($type == 'label') $type = 'labels';
		else $type = 'descriptions';

		if (empty($this->data[$type])) return '';

		foreach ($this->data[$type] as $language => $text) {
		    if (is_array($text)) {
		        $language = $text['language'];
		        $value = $text['value'];
		    } else { // Wikidata
		        $value = $text;
		    }

		    if ($language == $lang) return $value;
		    if (in_array($language, self::$preferred_langs)) $retprefs[$language] = $value;
		    elseif (! isset($ret)) $ret = $value;
		}

		if (! empty($retprefs)) {
			foreach (self::$preferred_langs as $preflang) {
				if (isset($retprefs[$preflang])) return $retprefs[$preflang];
			}
		}

		if (isset($ret)) return $ret;
		return '';
	}

	/**
	 * Get a site link
	 *
	 * @param string $site preferred site/wiki, If site not found, return enwiki or first site
	 * @return array keys = site, title
	 */
	public function getSiteLink($site)
	{
		$reten = [];

		if (empty($this->data['sitelinks'])) return [];

		foreach ($this->data['sitelinks'] as $sitelink) {
			if ($sitelink['site'] == $site) return ['site' => $site, 'title' => $sitelink['title']];
			if ($sitelink['site'] == 'enwiki') $reten = ['site' => 'enwiki', 'title' => $sitelink['title']];
			elseif (! isset($ret)) $ret = ['site' => $sitelink['site'], 'title' => $sitelink['title']];
		}

		if (! empty($reten)) return $reten;
		if (isset($ret)) return $ret;
		return [];
	}

	/**
	 * Get all of the site links.
	 *
	 * @return array key = site, values = site, title, badges
	 */
	public function getSiteLinks()
	{
	    if (! empty($this->data['sitelinks'])) return $this->data['sitelinks'];
	    return [];
	}

	/**
	 *
	 * @param string $lang alias language
	 * @return array ["language", "value"] ...
	 * @return Wikidata array "alias", ...
	 */
	public function getAliases($lang)
	{
	    if (! isset($this->data['aliases'][$lang])) return [];
	    return $this->data['aliases'][$lang];
	}

	/**
	 * Get a redirected to id.
	 *
	 * @return string redirect id or empty
	 */
	public function getRedirect()
	{
	    if (isset($this->data['redirect'])) return $this->data['redirect'];
        return '';
	}

	/**
	 *
	 * @return string schema text
	 */
	public function getSchemaText()
	{
	    if (isset($this->data['schemaText'])) return $this->data['schemaText'];
	    return '';
	}
}
