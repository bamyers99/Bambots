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

namespace com_brucemyers\DumpScannerBot\Scanners;

abstract class ScannerBase
{

	/**
	 * Initialize scanner.
	 *
	 * @param array $params
	 * @return bool continue - Should the report generation continue?
	 */
	public function init($params)
	{
		return true;
	}

	/**
	 * Get usage info.
	 *
	 * @return string
	 */
	public function getUsage()
	{
		return '';
	}
	
	/**
	 * Commence the scan
	 */
	public abstract function commenceScan();
}