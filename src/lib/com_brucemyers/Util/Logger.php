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

class Logger
{
    protected static $filepath;

    /**
     * Protected constructor
     */
    protected function __construct()
    {
    }

    /**
     * Initialize logger
     *
     * @param $filepath string Log file path
     */
    public static function init($filepath)
    {
    	self::$filepath = $filepath;

    	// Limit size to 1M
    	clearstatcache();
    	if (file_exists($filepath)) {
        	$size = filesize($filepath);
        	if ($size > 1024000) {
        	    $backup = $filepath . '.bak';
        	    @unlink($backup);
        	    rename($filepath, $backup);
        	}
    	}
    }

    /**
     * Log a message
     *
     * @param $msg string Log message
     */
    public static function log($msg)
    {
    	$hndl = fopen(self::$filepath, 'a');
    	$output = date('Y-m-d H:i:s') . " $msg\n";
    	fwrite($hndl, $output);
    	fclose($hndl);

        // Set the other user read mode to the parent directories read mode.
        $dirmode = fileperms(dirname(self::$filepath));
        $readmode = '0';
        if ($dirmode & 0x0004) $readmode = '4';
        $mode = octdec("64$readmode");
        chmod(self::$filepath, $mode);
    }

}