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

namespace com_brucemyers\Util;

/**
 * Configuration
 */
class Config
{
    private static $properties;

    /**
     * Private constructor
     */
    private function __construct()
    {
    }

    /**
     * Initialize config
     *
     * @param $filepath string Config file path
     */
    public static function init($filepath)
    {
        self::$properties = new Properties($filepath);
    }

    /**
     * Get config property
     *
     * @param $name string Property name
     * @return string Property value
     */
    public static function get($name)
    {
        return self::$properties->get($name);
    }
}