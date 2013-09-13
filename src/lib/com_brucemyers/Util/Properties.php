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
 * Properties
 */
class Properties
{
    protected $props = array();

    /**
     * Constructor
     *
     * @param $filepath string Properties file path
     */
    public function __construct($filepath)
    {
        $data = file_get_contents($filepath);
        $data = preg_split('/\r?\n/', $data);

        foreach ($data as $line) {
            if (! empty($line) && $line[0] != '#' && strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $this->props[$key] = $value;
            }
        }
    }

    /**
     * Get property
     *
     * @param $name string Property name
     * @return string Property value
     */
    public function get($name)
    {
        if (isset($this->props[$name])) return $this->props[$name];
        return '';
    }
}