<?php

$count = 0;

$whndl = fopen('wdnoinstanceof.tsv', 'w');
$hndl = fopen('php://stdin', 'r');

while (! feof($hndl)) {
	if (++$count % 100000 == 0) echo "Processed $count\n";
	$buffer = fgets($hndl);
	if (empty($buffer)) continue;
	$buffer = str_replace('/mediawiki/page/revision/text=', '', $buffer);
	$item = new WikidataItem(json_decode($buffer, true));
    
    $qid = $item->getId();
    if (empty($qid)) continue;
    $name = $item->getLabelDescription('label', 'en');
    if (empty($name)) continue;
    $description = $item->getLabelDescription('description', 'en');
    
    $link = $item->getSiteLink('enwiki');
    if (empty($link)) continue;
    if ($link['site'] != 'enwiki') continue;
    
    $redirect = '';
    if ($link['redirect']) $redirect = ' &#8618;';

//    $country = $item->getStatementsOfType('P17');
//    if (empty($country)) continue;
//    if ($country[0] != 'Q30') continue;
    
    fwrite($whndl, "$qid\t$name\t$description$redirect\n");
}

echo "Processed $count\n";

fclose($hndl);
fclose($whndl);

/**
 * Wikidata item
 */
class WikidataItem
{
	const TYPE_INSTANCE_OF = 'P31';
	
	static public $preferred_langs = array('en','de','es','fr','it','pt','nl','pl');
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

		if (isset($ret)) return $ret;
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
						$value = substr($parts[0], 0, 2) . ' century' . $bce;
						break;
					case '6': // millennium
						$parts = explode('-', $date);
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
				break;
		}
	}
	
	/**
	 * Get a site link
	 *
	 * @param string $site preferred site/wiki, If site not found, return enwiki or first site
	 * @return array keys = site, title, redirect (bool)
	 */
	public function getSiteLink($site)
	{
		$reten = [];

		if (empty($this->data['sitelinks'])) return [];

		foreach ($this->data['sitelinks'] as $sitelink) {
		    $redirect = false;
		    
		    if (isset($sitelink['badges'])) {
		        foreach ($sitelink['badges'] as $badge) {
		            if ($badge == 'Q70893996' || $badge == 'Q70894304') {
		                $redirect = true;
		                break;
		            }
		        }
		    }
		    
			if (! preg_match('![a-z]{2,3}wiki!', $sitelink['site'])) continue;
			if ($sitelink['site'] == $site) return ['site' => $site, 'title' => $sitelink['title'], 'redirect' => $redirect];
			if ($sitelink['site'] == 'enwiki') $reten = ['site' => 'enwiki', 'title' => $sitelink['title'], 'redirect' => $redirect];
			elseif (! isset($ret)) $ret = ['site' => $sitelink['site'], 'title' => $sitelink['title'], 'redirect' => $redirect];
		}

		if (! empty($reten)) return $reten;
		if (isset($ret)) return $ret;
		return [];
	}
}
