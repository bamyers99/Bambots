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

namespace com_brucemyers\InceptionBot;

use com_brucemyers\Util\CommonRegex;
use Exception;

class RuleSetProcessor
{
    protected $ruleSet;
    protected $ledeEnd = null;

    /**
     * Constructor
     *
     * @param $ruleSet RuleSet
     */
    public function __construct(RuleSet $ruleSet)
    {
        $this->ruleSet = $ruleSet;
    }

    /**
     * Process data against the RuleSet
     *
     * @param $data string Data
     * @param $title string Article title
     * @return array Results, one record per rule match, record keys = score, regex
     */
    public function processData(&$data, &$title)
    {
        $results = array();
        $this->ledeEnd = null;
        $cleandata = preg_replace(CommonRegex::REFERENCESTUB_REGEX, '', $data); // Must be first
    	if ($cleandata === null) $cleandata = $data;
   		else $cleandata = preg_replace(CommonRegex::REFERENCE_REGEX, '', $cleandata);
        $cleandata = preg_replace(CommonRegex::COMMENT_REGEX, '', $cleandata);

        foreach ($this->ruleSet->rules as &$rule) {
            $score = $this->processRule($cleandata, $rule, $title);
            if ($score != 0) {
                $results[] = array('score' => $score, 'regex' => preg_replace('/ui$/u', '', $rule['regex']));
            }
        }

        return $results;
    }

    /**
     * Process a rule
     *
     * @param $data string Data
     * @param $rule array Rule
     * @param $title string Article title
     * @return int Score
     */
    protected function processRule(&$data, &$rule, &$title)
    {
        $score = 0;

        switch ($rule['type']) {
            case 'regex':
                if (preg_match($rule['regex'], $data, $matches, PREG_OFFSET_CAPTURE)) {
                    $score = $rule['score'];

                    if ($this->ledeEnd === null) {
                        $this->ledeEnd = false;
                        if (preg_match('/^.*?(?:(?:\\r?\\n){2}|\\n==)/us', $data, $ledeMatches)){
                            $this->ledeEnd = strlen($ledeMatches[0]);
                        }
                    }

                    if ($this->ledeEnd !== false && $matches[0][1] < $this->ledeEnd) $score *= 2;
                }
                break;

            case 'size':
                switch ($rule['sizeoperator']) {
                    case '<':
                        if (strlen($data) < $rule['sizeoperand']) $score = $rule['score'];
                        break;
                    case '>':
                        if (strlen($data) > $rule['sizeoperand']) $score = $rule['score'];
                        break;
                    default:
                        throw new Exception('Unknown size operator ' . $rule['sizeoperator']);
                        break;
                }
                break;

            case 'title':
                if (preg_match($rule['regex'], $title)) {
                    $score = $rule['score'];
                }
            	break;

            default:
                throw new Exception('Unknown rule type ' . $rule['type']);
                break;
        }

        // Check the inhibitors
        if ($score != 0) {
            foreach ($rule['inhibitors'] as $inhibitor) {
                if (preg_match($inhibitor, $data)) {
                    $score = 0;
                    break;
                }
            }
        }

        return $score;
    }
}