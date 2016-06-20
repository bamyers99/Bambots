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
    const BASEDIR = 'sys.basedir';

    protected static $properties;

    /**
     * Protected constructor
     */
    protected function __construct()
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
     * @param $key string Property name
     * @return string Property value
     */
    public static function get($key)
    {
        return self::$properties->get($key);
    }

    /**
     * Set config property
     *
     * @param $key string Property name
     * @param $value string Property value
     * @param $writefile bool default:false Write to the prop file
     */
    public static function set($key, $value, $writefile = false)
    {
        self::$properties->set($key, $value, $writefile);
    }
}