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
    protected $filepath;

    /**
     * Constructor
     *
     * @param $filepath string Properties file path
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
        $data = file_get_contents($filepath);
        $data = preg_split('/\r?\n/', $data);
        $commentno = 0;

        foreach ($data as $line) {
            if (empty($line)) continue;
            if ($line[0] == '#' || strpos($line, '=') === false) {
                $this->props[' ' . $commentno++] = $line;
            } else {
                list($key, $value) = explode('=', $line, 2);
                $this->props[trim($key)] = trim($value);
            }
        }
    }

    /**
     * Get property
     *
     * @param $key string Property name
     * @return string Property value
     */
    public function get($key)
    {
        if (isset($this->props[$key])) return $this->props[$key];
        return '';
    }

    /**
     * Set property
     *
     * @param $key string Property name
     * @param $value string Property value
     */
    public function set($key, $value)
    {
        $this->props[$key] = $value;
        $this->_save();
    }

    /**
     * Save the properties to the file
     */
    protected function _save()
    {
        $data = '';
        foreach($this->props as $key => $value) {
            if (! empty($key) && $key[0] == ' ') $data .= $value . "\n";
            else $data .= $key . '=' . $value . "\n";
        }
        file_put_contents($this->filepath, $data);
    }
}