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

namespace com_brucemyers\WikidataBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Config;

class WikidataBot
{
	const ERROREMAIL = 'WikidataBot.erroremail';

	public $serviceMgr;

    public function __construct()
    {
    	$this->serviceMgr = new ServiceManager();
    }

    public function run($taskid, $params)
    {
        switch ($taskid) {
            case 1:
                if (count($params) != 2) throw new \Exception('task 1, 2 params required');
                
                $this->importTSV($params[0], $params[1]);
                break;
        }
    }
    
    public function importTSV($tsvpath, $propid)
    {
        $mywikiname = Config::get(MediaWiki::WIKIUSERNAMEKEY);
        if ($propid[0] != 'P') $propid = 'P' . $propid;
        
        $wdwiki = $this->serviceMgr->getMediaWiki('wikidatawiki');
        
        $prop = $wdwiki->getItemWithCache("Property:$propid");
        $datatype = $prop->getDatatype();
        
        if ($datatype != 'external-id' && $datatype != 'string') throw new \Exception('only datatypes: external-id, string');
        
        $rhndl = fopen($tsvpath, 'r');
        
        while (! feof($rhndl)) {
            sleep(1);
            $buffer = trim(fgets($rhndl));
            if (empty($buffer)) continue;
            list($qid, $propvalue) = explode("\t", $buffer);
            
            $items = $wdwiki->getItemsNoCache($qid);
            
            if (empty($items)) throw new \Exception("item $qid not found");
            $item = $items[0];
            
            $existingvalues = $item->getStatementsOfType($propid);
            if (! empty($existingvalues)) {
                continue;
            }
            
            echo "$qid\t$propvalue\n";
            
            $lastrevid = $wdwiki->getLastRevID($qid);
            $csrftoken = $wdwiki->getCSRFToken();
                                    
            // Create the claim
            $ret = $wdwiki->createCreateClaim($lastrevid, $mywikiname, $csrftoken, $qid, 'value', $propid, "\"$propvalue\"");
            
            if (! empty($ret)) throw new \Exception('createCreateClaim error = ' . $ret);
        }
    }
}