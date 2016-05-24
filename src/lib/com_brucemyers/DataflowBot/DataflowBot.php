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
	const ERROREMAIL = 'DataflowBot.erroremail';

    public function __construct($argcnt, $argval)
    {
    	if ($argcnt > 1) {
			switch ($argval[1]) {
				case "MostEdited":
					$components = $this->_configMostEdited();
    				$flownum = 1;
					break;

				case "PopularLowQuality":
					$components = $this->_configPopularLowQuality();
    				$flownum = 2;
					break;
			}
    	} else {
    		$components = $this->_configMostEdited();
    		$flownum = 1;
    	}

    	$compnum = 0;
    	$compcnts = array();
    	$firstRowHeaders = false;
    	$serviceMgr = new ServiceManager();

    	foreach ($components as $component) {
			$compobj = new $component['class']($serviceMgr);
			$compID = $compobj->getID();
			if (! isset($compcnts[$compID])) $compcnts[$compID] = 0;
			++$compcnts[$compID];

			foreach ($component['params'] as $param_name => $param_value) {
				$tempname = $compID;
				if ($compcnts[$compID] > 1) $tempname .= $compcnts[$compID];
				$tempname .= "#$param_name";
				$serviceMgr->setVar($tempname, $param_value);
			}

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
					fclose ( $ih );
					break;
				}

				$retval = $compobj->process ( $reader );
				if ($retval !== true) {
					Logger::log ( "process failed for $flownum-$compnum $retval" );
					fclose ( $ih );
					break;
				}

				fclose ( $ih );
			}

			++ $compnum;
		}
	}

	/**
	 * MostEdited
	 */
	function _configMostEdited() {
		$components = array (
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Extractors\\SQLQuery',
						'params' => array (
							'wiki' => 'enwiki',
							'sql' => "SELECT rc_title AS Article, COUNT(*) AS Edits,
  						COUNT(DISTINCT rc_user_text) AS Editors, -- used to be rc_user which counted all IPs as same
  						ROUND(LN(COUNT(*)) * (LN(COUNT(DISTINCT rc_user_text))+1.4), 2) AS `Weighted rank`
						FROM recentchanges
						WHERE rc_namespace = 0
  						AND rc_timestamp > DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 7 DAY), '%Y%m%d%H%i%s')
						GROUP BY article ORDER BY `Weighted rank` DESC
						LIMIT 20;"
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\DeleteColumn',
						'params' => array (
							'deletecol' => '4'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\ResolveRedirectColumn',
						'params' => array (
							'linkcol' => '1'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\WikilinkColumn',
						'params' => array (
							'linkcol' => '1'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnPageClass',
						'params' => array (
							'insertpos' => '2',
							'lookupcol' => '1',
							'priority' => 'best',
							'valuetype' => 'image',
							'title' => 'Class'
						)
				),
				// Commented out because does not handle redirected non-free images
				// array('class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnFirstImage',
				// 'params' => array(
				// 'insertpos' => 'append',
				// 'lookupcol' => '1',
				// 'title' => 'Image',
				// 'nonfree' => 'no',
				// 'fileoptions' => 'left|100x100px')),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnFirstSentence',
						'params' => array (
							'insertpos' => 'append',
							'lookupcol' => '1',
							'title' => 'Abstract'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnSequentialNumber',
						'params' => array (
							'insertpos' => '1',
							'title' => 'Rank',
							'startnum' => '1'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\ToWikitable',
						'params' => array (
							'sortable' => '1',
							'unsortable' => '6'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Loaders\\WikiLoader',
						'params' => array (
							'wiki' => 'enwiki',
    						'pagename' => 'Top 20 enwiki articles by edits and editors in past 7 days',
    						'header' => 'Last updated: {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)',
    						'footer' => ''))
    	);

		return $components;
    }

    /**
     * PopularLowQuality
     */
	function _configPopularLowQuality() {
		$header = <<<EOT
:'''Warning:''' many of the articles shown here have only transient popularity probably due to automated downloads and some of them are incorrectly classified because they are composed of mostly template content. Please disregard such mistakes.

==Lowest quality high-popularity articles==
Last updated: {{subst:CURRENTYEAR}}-{{subst:CURRENTMONTH}}-{{subst:CURRENTDAY2}} {{subst:CURRENTTIME}} (UTC)<br />Page views asof: @@TPV#year@@-@@TPV#month@@-@@TPV#day@@
EOT;
		$components = array (
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Extractors\\TopPageViews',
						'params' => array (
							'wiki' => 'en.wikipedia.org',
							'daysago' => '5',
							'checkdays' => '3'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\FilterColumn',
						'params' => array (
							'filtercol' => '1',
							'excluderegex' => '^List_of'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\ResolveRedirectColumn',
						'params' => array (
							'linkcol' => '1'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnRevisionID',
						'params' => array (
							'insertpos' => 'append',
							'lookupcol' => '1',
							'title' => 'Revision ID'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnORESScore',
						'params' => array (
							'wiki' => 'enwiki',
							'model' => 'wp10',
							'lookupcol' => '3',
							'insertpos' => '2',
							'title' => 'ORES prediction'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\DeleteColumn',
						'params' => array (
							'deletecol' => '4'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\FilterColumn',
						'params' => array (
							'filtercol' => '2',
							'includeregex' => '^(Stub|Start|C)$'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\SortData',
						'params' => array (
							'sortcol1' => '2',
							'sorttype1' => 'enum',
							'sortdir1' => 'asc',
							'sortenum1' => 'Stub|Start|C',
							'sortcol2' => '3',
							'sorttype2' => 'numeric',
							'sortdir2' => 'desc'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\WikilinkColumn',
						'params' => array (
							'linkcol' => '1'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\AddColumnSequentialNumber',
						'params' => array (
							'insertpos' => '1',
							'title' => 'Rank',
							'startnum' => '1'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Transformers\\ToWikitable',
						'params' => array (
							'sortable' => '1'
						)
				),
				array (
						'class' => 'com_brucemyers\\DataflowBot\\Loaders\\WikiLoader',
						'params' => array (
							'wiki' => 'enwiki',
    						'pagename' => 'Popular low quality articles',
    						'header' => $header,
    						'footer' => ''))
    	);

		return $components;
    }
}