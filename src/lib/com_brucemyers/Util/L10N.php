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

namespace com_brucemyers\Util;
use IntlDateFormatter;

/**
 * Localization
 */
class L10N
{
    protected $properties;
    protected $lang;

    /**
     * Constructor
     */
    public function __construct($lang)
    {
    	$this->lang = $lang;
    	$locdir = Config::get(Config::BASEDIR) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'l10n' . DIRECTORY_SEPARATOR .
    		$GLOBALS['botname'] . DIRECTORY_SEPARATOR;
    	$filepath = $locdir . $lang . '.properties';
    	if (! file_exists($filepath)) $filepath = $locdir . 'en.properties';
        $this->properties = new Properties($filepath);
    }

    /**
     * Get localized string
     *
     * @param $key string Property name
     * @param $capFirst bool (optional) Capitalize first letter; default = false
     * @return string Property value
     */
    public function get($key, $capFirst = false)
    {
        $value =  $this->properties->get($key);
        if (empty($value)) $value = $key;
        if ($capFirst) $value = mb_strtoupper(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($value, 1, mb_strlen($value) - 1, 'UTF-8');
        return $value;
    }

    /**
     * Format a date with localization
     *
     * @param int $timestamp Timestamp
     * @param string $key Property name with date format
     * @param string $timezone (optional) Time zone; default = GMT
     * @return string Formatted date
     */
    public function formatDate($timestamp, $key, $timezone = 'GMT')
    {
    	$dateformat = $this->get($key);
    	$formatter = new IntlDateFormatter($this->lang, NULL, NULL, $timezone, NULL , $dateformat);
    	return $formatter->format($timestamp);
    }
}