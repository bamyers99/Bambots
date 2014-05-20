<?php
/**
 Copyright 2013 Myers Enterprises II

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

namespace com_brucemyers\CleanupWorklistBot;

class Categories
{
    public $categories = array(
    	// from-monthly
    	'1911 Britannica articles needing updates' 			=> array('type' => 'from-monthly'),
    	'Articles containing potentially dated statements' 	=> array('type' => 'from-monthly'),
    	'Accuracy disputes' 								=> array('type' => 'from-monthly'),
    	'Article sections to be split' 						=> array('type' => 'from-monthly'),
   		'Articles about possible neologisms' 				=> array('type' => 'from-monthly'),
    	'Articles lacking in-text citations' 				=> array('type' => 'from-monthly'),
    	'Articles lacking page references' 					=> array('type' => 'from-monthly'),
   		'Articles lacking reliable references' 				=> array('type' => 'from-monthly'),
   		'Articles lacking sources' 							=> array('type' => 'from-monthly'),
    	'Articles needing additional references' 			=> array('type' => 'from-monthly'),
    	'Articles needing chemical formulas' 				=> array('type' => 'from-monthly'),
    	'Articles needing cleanup' 							=> array('type' => 'from-monthly'),
   		'Articles needing expert attention' 				=> array('type' => 'from-monthly'),
   		'Articles needing link rot cleanup' 				=> array('type' => 'from-monthly'),
    	'Articles needing more viewpoints' 					=> array('type' => 'from-monthly'),
    	'Articles needing sections' 						=> array('type' => 'from-monthly'),
    	'Articles needing the year an event occurred' 		=> array('type' => 'from-monthly'),
    	'Articles requiring tables' 						=> array('type' => 'from-monthly'),
    	'Articles slanted towards recent events' 			=> array('type' => 'from-monthly'),
    	'Articles sourced by IMDB' 							=> array('type' => 'from-monthly'),
    	'Articles sourced only by IMDB' 					=> array('type' => 'from-monthly'),
    	'Articles that may be too long' 					=> array('type' => 'from-monthly'),
    	'Articles that may contain original research' 		=> array('type' => 'from-monthly'),
    	'Articles that need to differentiate between fact and fiction' => array('type' => 'from-monthly'),
    	'Articles to be expanded' 							=> array('type' => 'from-monthly'),
   		'Articles to be expanded with sources' 				=> array('type' => 'from-monthly'),
    	'Articles to be merged' 							=> array('type' => 'from-monthly'),
   		'Articles to be pruned' 							=> array('type' => 'from-monthly'),
   		'Articles to be split' 								=> array('type' => 'from-monthly'),
    	'Articles with a promotional tone' 					=> array('type' => 'from-monthly'),
    	'Articles with broken or outdated citations' 		=> array('type' => 'from-monthly'),
    	'Articles with close paraphrasing' 					=> array('type' => 'from-monthly'),
   		'Articles with close paraphrasing of public domain sources' => array('type' => 'from-monthly'),
    	'Articles with dead external links' 				=> array('type' => 'from-monthly'),
   		'Articles with disproportional geographic scope' 	=> array('type' => 'from-monthly'),
    	'Articles with disputed statements' 				=> array('type' => 'from-monthly'),
   		'Articles with excessive "see also" sections' 		=> array('type' => 'from-monthly'),
   		'Articles with improper non-free content' 			=> array('type' => 'from-monthly'),
    	'Articles with improper non-free content (lists)' 	=> array('type' => 'from-monthly'),
    	'Articles with limited geographic scope' 			=> array('type' => 'from-monthly'),
    	'Articles with links needing disambiguation' 		=> array('type' => 'from-monthly'),
    	'Articles with minor POV problems' 					=> array('type' => 'from-monthly'),
   		'Articles with obsolete information' 				=> array('type' => 'from-monthly'),
    	'Articles with peacock terms' 						=> array('type' => 'from-monthly'),
   		'Articles with sections that need to be turned into prose' => array('type' => 'from-monthly'),
    	'Articles with specifically marked weasel-worded phrases' => array('type' => 'from-monthly'),
    	'Articles with too few wikilinks' 					=> array('type' => 'from-monthly'),
    	'Articles with topics of unclear notability' 		=> array('type' => 'from-monthly'),
   		'Articles with trivia sections' 					=> array('type' => 'from-monthly'),
   		'Articles with unsourced statements' 				=> array('type' => 'from-monthly'),
   		'Articles with weasel words' 						=> array('type' => 'from-monthly'),
   		'Autobiographical articles' 						=> array('type' => 'from-monthly'),
    	'BLP articles lacking sources' 						=> array('type' => 'from-monthly'),
   		'Category needed' 									=> array('type' => 'from-monthly'),
   		'Cleanup section' 									=> array('type' => 'from-monthly'),
    	'Copied and pasted articles and sections' 			=> array('type' => 'from-monthly'),
    	'Copied and pasted articles and sections with url provided' => array('type' => 'from-monthly'),
    	'Dead-end pages' 									=> array('type' => 'from-monthly'),
   		'Disambiguation pages in need of cleanup' 			=> array('type' => 'from-monthly'),
    	'Incomplete disambiguation' 						=> array('type' => 'from-monthly'),
    	'Incomplete lists' 									=> array('type' => 'from-monthly'),
    	'NPOV disputes' 									=> array('type' => 'from-monthly'),
    	'NRHP articles with dead external links' 			=> array('type' => 'from-monthly'),
    	'Orphaned articles' 								=> array('type' => 'from-monthly'),
    	'Pages with excessive dablinks' 					=> array('type' => 'from-monthly'),
    	'Recently revised' 									=> array('type' => 'from-monthly'),
    	'Self-contradictory articles' 						=> array('type' => 'from-monthly'),
   		'Suspected copyright infringements without a source' => array('type' => 'from-monthly'),
    	'Uncategorized stubs' 								=> array('type' => 'from-monthly'),
    	'Unreferenced BLPs' 								=> array('type' => 'from-monthly'),
    	'Unreviewed new articles' 							=> array('type' => 'from-monthly'),
    	'Unreviewed new articles created via the Article Wizard' => array('type' => 'from-monthly'),
   		'Vague or ambiguous geographic scope' 				=> array('type' => 'from-monthly'),
    	'Vague or ambiguous time' 							=> array('type' => 'from-monthly'),
    	'Wikipedia articles in need of updating' 			=> array('type' => 'from-monthly'),
    	'Wikipedia articles needing clarification' 			=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing context' 				=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing copy edit' 				=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing factual verification' 	=> array('type' => 'from-monthly'),
    	'Wikipedia articles needing page number citations' 	=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing reorganization' 		=> array('type' => 'from-monthly'),
    	'Wikipedia articles needing rewrite' 				=> array('type' => 'from-monthly'),
   		'Wikipedia articles needing style editing' 			=> array('type' => 'from-monthly'),
   		'Wikipedia articles that are too technical' 		=> array('type' => 'from-monthly'),
    	'Wikipedia articles with plot summary needing attention' => array('type' => 'from-monthly'),
    	'Wikipedia articles with possible conflicts of interest' => array('type' => 'from-monthly'),
   		'Wikipedia external links cleanup' 					=> array('type' => 'from-monthly'),
   		'Wikipedia introduction cleanup' 					=> array('type' => 'from-monthly'),
   		'Wikipedia laundry list cleanup' 					=> array('type' => 'from-monthly'),
    	'Wikipedia list cleanup' 							=> array('type' => 'from-monthly'),
    	'Wikipedia pages needing cleanup' 					=> array('type' => 'from-monthly'),
   		'Wikipedia references cleanup' 						=> array('type' => 'from-monthly'),
    	'Wikipedia spam cleanup' 							=> array('type' => 'from-monthly'),
   		'Wikipedia articles containing buzzwords' 			=> array('type' => 'from-monthly'),
   		'Wikipedia articles without plot summaries' 		=> array('type' => 'from-monthly'),
   		'Wikipedia red link cleanup' 						=> array('type' => 'from-monthly'),

    	// no-date
    	'All articles needing coordinates' 					=> array('type' => 'no-date'),
    	'All articles needing expert attention' 			=> array('type' => 'no-date'),
    	'Animals cleanup' 									=> array('type' => 'no-date'),
    	'Articles needing more detailed references' 		=> array('type' => 'no-date'),
 		'Invalid conservation status' 						=> array('type' => 'no-date'),
    	'Missing taxobox' 									=> array('type' => 'no-date'),
    	'Pages with several capitalization mistakes' 		=> array('type' => 'no-date'),
    	'Persondata templates without short description parameter' => array('type' => 'no-date'),
    	'Plant articles needing a taxobox' 					=> array('type' => 'no-date'),
    	'Proposed moves' 									=> array('type' => 'no-date'),
    	'Redundant taxobox' 								=> array('type' => 'no-date'),
    	'Taxoboxes needing a status system parameter' 		=> array('type' => 'no-date'),
    	'Taxoboxes with an invalid color' 					=> array('type' => 'no-date'),
    	'Taxoboxes with an unrecognised status system' 		=> array('type' => 'no-date'),
    	'Tree of Life cleanup' 								=> array('type' => 'no-date'),
    	'Wikipedia articles needing cleanup after translation' => array('type' => 'no-date'),

    	// since-yearly
    	'Pages with DOIs inactive' 							=> array('type' => 'since-yearly')
    );

}