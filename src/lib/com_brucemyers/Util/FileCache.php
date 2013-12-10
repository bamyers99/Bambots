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
 * File cache
 */
class FileCache
{
    const CACHEDIR = 'cache.dir';
    const CACHEEXPIRYDAYS = 'cache.expirydays';
    const CACHEBASEDIR = '{basedir}';

    protected static $instance = null;
    protected $cacheDir;

    protected function __construct()
    {
        $cacheDir = Config::get(self::CACHEDIR);
        $cacheDir = str_replace(self::CACHEBASEDIR, Config::get(Config::BASEDIR), $cacheDir);
        $cacheDir = preg_replace('!(/|\\\\)$!', '', $cacheDir); // Drop trailing slash
        $this->cacheDir = $cacheDir;

        $expirydays = (int)Config::get(self::CACHEEXPIRYDAYS) + 1;
        $rmbefore = strtotime("-$expirydays days");

        // Expire the cache
        $handle = opendir($cacheDir);
    	while (($entry = readdir($handle)) !== false) {
    		if ($entry == '.' || $entry == '..') continue;
            $filepath = $cacheDir . DIRECTORY_SEPARATOR . $entry;
            $lastupdate = filemtime($filepath);
            if ($lastupdate < $rmbefore) unlink($filepath);
    	}
    	closedir($handle);
    }

    /**
     * Get instance
     *
     * @return FileCache
     */
    protected static function getInstance()
    {
        if (! self::$instance) self::$instance = new FileCache();
        return self::$instance;
    }

    /**
     * Get cached data
     *
     * @param $key Cache key
     * @return mixed false = not cached, else data
     */
    public static function getData($key)
    {
        $inst = self::getInstance();
        $key = $inst->safeKey($key);
        $filepath = $inst->cacheDir . DIRECTORY_SEPARATOR . $key;

        if (! file_exists($filepath)) return false;

        return file_get_contents($filepath);
    }

    /**
     * Put data into cache
     *
     * @param $key string Cache key
     * @param $data string Cache data
     */
    public static function putData($key, &$data)
    {
        $inst = self::getInstance();
        $key = $inst->safeKey($key);
        $filepath = $inst->cacheDir . DIRECTORY_SEPARATOR . $key;

        file_put_contents($filepath, $data);
    }

    /**
     * Generate a filesystem safe filename
     *
     * @param $key string Cache key
     * @return string Cache key
     */
    protected function safeKey($key)
    {
        $key = str_replace(' ', '_', $key);

        $key = preg_replace_callback('/\W/', function ($c) {
            return '~' . bin2hex($c[0]);
        }, $key);

        if (strlen($key) > 254) $key = md5($key);

        return $key;
    }

    /**
     * Purge everything in the cache
     */
    public static function purgeAll()
    {
        $inst = self::getInstance();
        $handle = opendir($inst->cacheDir);
    	while (($entry = readdir($handle)) !== false) {
    		if ($entry == '.' || $entry == '..') continue;
    		unlink($inst->cacheDir . DIRECTORY_SEPARATOR . $entry);
    	}
    	closedir($handle);
    }

    /**
     * Get cache dir
     *
     * @return string Cache dir
     */
    public static function getCacheDir()
    {
        $inst = self::getInstance();
        return $inst->cacheDir;
    }
}