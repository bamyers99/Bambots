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

namespace com_brucemyers\DataflowBot\Extractors;

use com_brucemyers\DataflowBot\Component;
use com_brucemyers\DataflowBot\io\FlowWriter;

abstract class Extractor extends Component
{
	/**
	 * Initialize a component.
	 *
	 * @param array $params Component specific parameters
	 * @return mixed true = success, string = error message
	 */
	public function init($params)
	{
		return true;
	}

	/**
	 * Extract data and write to writer.
	 *
	 * @param FlowWriter $writer
	 * @return mixed true = success, string = error message
	 */
	public abstract function process(FlowWriter $writer);
}