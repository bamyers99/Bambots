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

				    default:
				    	Logger::log("WikidataItem::getStatementsOfType($property_id) unknown value type='$type' value=$value");
				    	break;
				}
			}
		}

		return $statements;
	}
}