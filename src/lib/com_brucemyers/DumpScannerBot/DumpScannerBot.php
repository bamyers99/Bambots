<?php
/**
 Copyright 2025 Myers Enterprises II

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

namespace com_brucemyers\DumpScannerBot;

class DumpScannerBot
{
    const HTMLDIR = 'DumpScannerBot.htmldir';
	const OUTPUTDIR = 'DumpScannerBot.outputdir';
    const ERROREMAIL = 'DumpScannerBot.erroremail';
    
    var $htmldir;
    var $outputdir;

    public function __construct()
    {
        $this->htmldir = Config::get(self::HTMLDIR);
        $this->outputdir = Config::get(self::OUTPUTDIR);
    }

    public function commenceScan($scannername, $params)
    {
        $params['htmldir'] = $this->htmldir;
        $params['outputdir'] = $this->outputdir;
        
    	$classname = "com_brucemyers\\DumpScannerBot\\Scanners\\$scannername";
    	$scanner = new $classname();
    	$continue = $scanner->init($params);
    	if (! $continue) return;
    	
    	$scanner->commenceScan();
    }
}