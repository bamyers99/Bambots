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

use com_brucemyers\InceptionBot\RuleSet;
use com_brucemyers\Util\CommonRegex;

class MasterRuleConfig
{
    /**
     * Master rule configuration, key = Wikiproject, value = Category
     */
    public $ruleConfig = array();

    public function __construct($data)
    {
        $data = preg_replace(CommonRegex::COMMENT_REGEX, '', $data);
        $data = preg_replace(RuleSet::WIKI_TEMPLATE_REGEX, '', $data);
        $data = preg_replace(CommonRegex::CATEGORY_REGEX, '', $data);
        $lines = preg_split('/\\r?\\n/', $data);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $parts = explode('=>', $line, 2);
            $key = str_replace(' ', '_', trim($parts[0]));
            $value = (count($parts) > 1) ? str_replace(' ', '_', trim($parts[1])) : '';
            $this->ruleConfig[$key] = $value;
        }
    }
}