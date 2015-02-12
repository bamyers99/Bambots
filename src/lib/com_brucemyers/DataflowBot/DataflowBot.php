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

namespace com_brucemyers\DataflowBot;

use com_brucemyers\Util\FileCache;
use com_brucemyers\DataflowBot\io\FileReader;
use com_brucemyers\DataflowBot\io\FileWriter;
use com_brucemyers\DataflowBot\ServiceManager;
use com_brucemyers\DataflowBot\Extractors\Extractor;
use com_brucemyers\DataflowBot\Transformers\Transformer;
use com_brucemyers\DataflowBot\Loaders\Loader;
use com_brucemyers\Util\Logger;

class DataflowBot
{

    public function __construct()
    {
    	$components = array(
    	    array('class' => 'com_brucemyers\\DataflowBot\\Extractors\\SQLQuery',
    			'params' => array(
    	    		'wiki' => 'enwiki',
    				'sql' => "SELECT REPLACE(rc_title, '_', ' ') AS Article, COUNT(*) AS Edits,
  						COUNT(DISTINCT rc_user_text) AS Editors, -- used to be rc_user which counted all IPs as same
  						ROUND(LN(COUNT(*)) * (LN(COUNT(DISTINCT rc_user_text))+1.4), 2) AS `Weighted rank`
						FROM recentchanges
						WHERE rc_namespace = 0
  						AND rc_timestamp > DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 7 DAY), '%Y%m%d%H%i%s')
						GROUP BY article ORDER BY `Weighted rank` DESC
						LIMIT 20;")),
    		array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\DeleteColumn',
    			'params' => array(
    				'deletecol' => '4')),
    		array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\WikilinkColumn',
    			'params' => array(
    				'linkcol' => '1')),
    		array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnPageClass',
    			'params' => array(
    				'insertpos' => '2',
    	    		'lookupcol' => '1',
    				'priority' => 'best',
    				'valuetype' => 'image',
    				'title' => 'Class')),
    		array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnFirstImage',
    			'params' => array(
    				'insertpos' => 'append',
    	    		'lookupcol' => '1',
    				'title' => 'Image',
    				'nonfree' => 'no',
    				'fileoptions' => 'left|100x100px')),
    		array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnFirstSentence',
    			'params' => array(
    				'insertpos' => 'append',
    	    		'lookupcol' => '1',
    				'title' => 'Abstract')),
    		array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnSequentialNumber',
    			'params' => array(
    				'insertpos' => '1',
    				'title' => 'Rank',
    				'startnum' => '1')),
    		array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\ToWikitable',
    			'params' => array(
    				'sortable' => '1',
					'unsortable' => '7')),
    		array('class' => 'com_brucemyers\\DataflowBot\\Loaders\\WikiLoader',
    			'params' => array(
    				'wiki' => 'enwiki',
					'pagename' => 'Top 20 enwiki articles by edits and editors in past 7 days',
    				'header' => 'Last updated: {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)',
    				'footer' => ''))
    			);

    	$compnum = 0;
    	$flownum = 1;
    	$firstRowHeaders = false;
    	$serviceMgr = new ServiceManager();

    	foreach ($components as $component) {
			$compobj = new $component['class']($serviceMgr);

			if ($compobj instanceof Extractor) {
				$outfilename = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . "Dataflow#$flownum#$compnum";
				$prevoutfilename = $outfilename;
				$oh = fopen($outfilename, 'w');
				$writer = new FileWriter($oh);

				$retval = $compobj->init($component['params']);
				if ($retval !== true) {
					Logger::log("init failed for $flownum-$compnum $retval");
					fclose($oh);
					break;
				}
				$firstRowHeaders = $compobj->isFirstRowHeaders();

				$retval = $compobj->process($writer);
				if ($retval !== true) {
					Logger::log("process failed for $flownum-$compnum $retval");
					fclose($oh);
					break;
				}

				fclose($oh);
			} elseif ($compobj instanceof Transformer) {
				$infilename = $prevoutfilename;
				$ih = fopen($infilename, 'r');
				$reader = new FileReader($ih);

				$outfilename = FileCache::getCacheDir() . DIRECTORY_SEPARATOR . "Dataflow#$flownum#$compnum";
				$prevoutfilename = $outfilename;
				$oh = fopen($outfilename, 'w');
				$writer = new FileWriter($oh);

				$retval = $compobj->init($component['params'], $firstRowHeaders);
				if ($retval !== true) {
					Logger::log("init failed for $flownum-$compnum $retval");
					fclose($ih);
					fclose($oh);
					break;
				}
				$firstRowHeaders = $compobj->isFirstRowHeaders();

				$retval = $compobj->process($reader, $writer);
				if ($retval !== true) {
					Logger::log("process failed for $flownum-$compnum $retval");
					fclose($ih);
					fclose($oh);
					break;
				}

				fclose($ih);
				fclose($oh);
			} elseif ($compobj instanceof Loader) {
				$infilename = $prevoutfilename;
				$ih = fopen($infilename, 'r');
				$reader = new FileReader($ih);

				$retval = $compobj->init($component['params'], $firstRowHeaders, $flownum);
				if ($retval !== true) {
					Logger::log("init failed for $flownum-$compnum $retval");
					fclose($ih);
					break;
				}

				$retval = $compobj->process($reader);
				if ($retval !== true) {
					Logger::log("process failed for $flownum-$compnum $retval");
					fclose($ih);
					break;
				}

				fclose($ih);
			}

			++$compnum;
    	}
    }
}