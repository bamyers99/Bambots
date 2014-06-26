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

class RuleSet
{
    const COMMENT_REGEX = '/<!--.*?-->/us';
    const REFERENCE_REGEX = '!<ref.*?</ref>!us';
    const CATEGORY_REGEX = '/\\[\\[Category:.+?\\]\\]/usi';
    const WIKI_TEMPLATE_REGEX = '/\\{\\{.+?\\}\\}/us';
    const RULE_REGEX = '!^(-?\\d*)\\s*(/.*?/)((?:\\s*,\\s*/.*?/)*)$!u';
    const SCORE_REGEX = '/^@@\\s*(\\d+)\\s*@@$/u';
    const TEMPLATE_LINE_REGEX = '/^(-?\\d*)\\s*\\$\\$(.*)\\$\\$$/u';
    const TEMPLATE_REGEX = '!^/\\s*\\$\\$(.*)\\$\\$\\s*/$!u';
    const SIZE_REGEX = '!^/\\s*\\$SIZE\\s*(<|>)\\s*(\\d+)\\s*/$!u';
    const TITLE_REGEX = '!^/\\s*\\$TITLE\\s*:(.+)/$!u';
    const INIHIBITOR_REGEX = '!\\s*,\\s*(/.*?/)!u';
    const JAVA_UNICODE_REGEX = '/(\\\\[pP]\\{)[iI]s/';
    const OPTION_REGEX = '!^##(\w+\s*=?[^#]*)##$!';
    const DEFAULT_SCORE = 10;

    public $errors = array();
    public $rules = array();
    public $name;
    public $minScore = self::DEFAULT_SCORE;
    public $options = array();
    public $optiontypes = array('SuppressNS' => array('values' => array('Category', 'Draft', 'Template'), 'separator' => '|'));

    /**
     * Constructor
     *
     * @param $name string Rule name
     * @param $data string Rule data
     */
    public function __construct($name, $data)
    {
        $this->name = $name;
        // Strip comments/templates/categories
        $data = preg_replace(self::COMMENT_REGEX, '', $data);
        $data = preg_replace(self::WIKI_TEMPLATE_REGEX, '', $data);
        $data = preg_replace(self::CATEGORY_REGEX, '', $data);
        $lines = preg_split('/\\r?\\n/u', $data);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match(self::RULE_REGEX, $line, $matches)) {
                $rule = $this->parseRule($line, $matches);
                if ($rule['valid']) $this->rules[] = $rule;

            } elseif (preg_match(self::SCORE_REGEX, $line, $matches)) {
                $this->minScore = $matches[1];

            } elseif (preg_match(self::TEMPLATE_LINE_REGEX, $line, $matches)) {
                $score = $matches[1];
                if (empty($score)) $score = self::DEFAULT_SCORE;
                $regex = '/\\{\\{' . $matches[2] . '.*?\\}\\}/ui';
                $this->rules[] = array('type' => 'regex', 'score' => $score, 'regex' => $regex, 'valid' => true, 'inhibitors' => array());

            } elseif (preg_match(self::OPTION_REGEX, $line, $matches)) {
                $this->parseOption($line, $matches);
            } else {
                $this->errors[] = 'Invalid rule: ' . $line;
            }
        }

        if (empty($this->rules)) $this->errors[] = 'No rules found';
    }

    /**
     * Parse an option
     *
     * @param $line string Option line
     * @param $matches Match data
     */
    protected function parseOption(&$line, &$matches)
    {
        $optionparts = explode('=', $matches[1], 2);
        $option = $optionparts[0];
        $value = '';
        if (count($optionparts) == 2) $value = $optionparts[1];

        if (! isset($this->optiontypes[$option])) {
            $this->errors[] = 'Invalid option name: ' . $line;
            return;
        }

        if (! empty($this->optiontypes[$option]['values'])) {
            if (! empty($this->optiontypes[$option]['separator'])) {
                $values = explode($this->optiontypes[$option]['separator'], $value);

                foreach ($values as $value) {
                    if (! in_array($value, $this->optiontypes[$option]['values'])) {
                        $this->errors[] = 'Invalid option value: ' . $line;
                        return;
                    }
                }

                $value = $values;

            } elseif (! in_array($value, $this->optiontypes[$option]['values'])) {
                $this->errors[] = 'Invalid option value: ' . $line;
                return;
            }
        }

        $this->options[$option] = $value;
    }

    /**
     * Parse a rule line
     *
     * @param $line string Rule line
     * @param $matches Match data
     * @return array Rule data, keys = type, score, regex, inhibitors, sizeoperator, sizeoperand
     */
    protected function parseRule(&$line, &$matches)
    {
        $score = $matches[1];
        if (empty($score)) $score = self::DEFAULT_SCORE;
        $regex = $matches[2];
        if (preg_match(self::TEMPLATE_REGEX, $regex, $tmplmatches)) {
            $regex = '/\\{\\{' . $tmplmatches[1] . '.*?\\}\\}/';
        }

        $type = 'regex';
        $valid = true;
        $size = preg_match(self::SIZE_REGEX, $regex, $sizematches);

        if (preg_match(self::TITLE_REGEX, $regex, $titlematches)) {
        	$regex = '/' . $titlematches[1] . '/';
        	$type = 'title';
        }

        if (! $size) {
            $regex = preg_replace(self::JAVA_UNICODE_REGEX, '$1', $regex);
            $regex .= 'ui'; // Add Unicode, ignore case options
            $valid = (@preg_match($regex, '') !== false);
            if (! $valid) $this->errors[] = 'Invalid pattern in rule: ' . $line;
        }

        $rule = array('type' => $type, 'score' => $score, 'regex' => $regex, 'valid' => $valid, 'inhibitors' => array());

        if ($size) {
            $rule['type'] = 'size';
            $rule['sizeoperator'] = $sizematches[1];
            $rule['sizeoperand'] = $sizematches[2];
        }

        // Process the inhibitors
        if (count($matches) > 3) {
            preg_match_all(self::INIHIBITOR_REGEX, $matches[3], $inhibmatches, PREG_PATTERN_ORDER);
            foreach ($inhibmatches[1] as $regex) {
                if (preg_match(self::TEMPLATE_REGEX, $regex, $tmplmatches)) {
                    $regex = '/\\{\\{' . $tmplmatches[1] . '.*?\\}\\}/';
                }

                $regex = preg_replace(self::JAVA_UNICODE_REGEX, '$1', $regex);
                $regex .= 'ui'; // Add Unicode, ignore case options

            	$valid = (@preg_match($regex, '') !== false);
            	if (! $valid) {
            	    $this->errors[] = "Invalid inhibitor ($regex) in rule: $line";
            	    $rule['valid'] = false;
            	}
            	$rule['inhibitors'][] = $regex;
            }
        }

        return $rule;
    }
}
