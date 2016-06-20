<?php
/**
 Copyright 2015 Myers Enterprises II

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

namespace com_brucemyers\DataflowBot\Loaders;

use com_brucemyers\DataflowBot\Component;
use com_brucemyers\DataflowBot\io\FlowReader;

abstract class Loader extends Component
{
	/**
	 * Initialize a component.
	 *
	 * @param array $params Component specific parameters
	 * @param bool $isFirstRowHeaders Is the first row in input data headers?
	 * @param int $flowID Flow ID
	 * @return mixed true = success, string = error message
	 */
	public function init($params, $isFirstRowHeaders, $flowID)
	{
		return true;
	}

	/**
	 * Load data from a reader into an external store.
	 *
	 * @param FlowReader $reader
	 * @return mixed true = success, string = error message
	 */
	public abstract function process(FlowReader $reader);
}