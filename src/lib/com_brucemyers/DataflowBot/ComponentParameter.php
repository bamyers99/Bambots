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

namespace com_brucemyers\DataflowBot;

class ComponentParameter
{
	const PARAMETER_TYPE_STRING = 'string';
	const PARAMETER_TYPE_ENUM = 'enum';
	const PARAMETER_TYPE_BOOL = 'bool';

	public $paramName;
	public $paramType;
	public $paramLabel;
	public $paramDescription;
	public $extra;

	public function __construct($paramName, $paramType, $paramLabel, $paramDescription, $extra = array())
	{
		$this->paramName = $paramName;
		$this->paramType = $paramType;
		$this->paramLabel = $paramLabel;
		$this->paramDescription = $paramDescription;
		$this->extra = $extra;
	}
}