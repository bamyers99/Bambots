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

class AllTests extends TestSuite {
	function __construct()
	{
		parent::__construct();
		$this->collect(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'unit',
			new MyPatternCollector());
	}
}

class MyPatternCollector extends SimpleCollector
{
    /**
     * Attempts to add files that match a given pattern.
     *
     * @see SimpleCollector::_handle()
     * @param object $test    Group test with {@link GroupTest::addTestFile()} method.
     * @param string $path    Directory to scan.
     * @access protected
     */
    protected function handle(&$test, $filepath)
    {
        $filename = basename($filepath);
        if ($filename == '.' || $filename == '..') {
		    return;
		}

        if (is_dir($filepath)) {
            if ($handle = opendir($filepath)) {
            	while (($entry = readdir($handle)) !== false) {
            		if ($this->isHidden($entry)) {
            			continue;
            		}
            		$this->handle($test, $filepath . DIRECTORY_SEPARATOR . $entry);
            	}
            	closedir($handle);
            }
        } else {
		    if (substr($filename, 0, 4) != 'Test') {
		        return;
		    }

		    parent::handle($test, $filepath);
        }

    }
}