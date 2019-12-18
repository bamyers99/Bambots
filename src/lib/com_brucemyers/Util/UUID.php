<?php
/**
 Copyright 2019 Myers Enterprises II

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

class UUID
{
	/**
	 * RFC 4122 A Universally Unique IDentifier (UUID) URN Namespace
	 *
	 * @return string UUID version 4
	 */
	static public function getUUID()
	{
	    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	        mt_rand(0, 0xffff),
	        mt_rand(0, 0x0fff) | 0x4000, // version 4
	        mt_rand(0, 0x3fff) | 0x8000, // variant DCE1.1
	        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	        );
	}
}