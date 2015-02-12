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

use com_brucemyers\DataflowBot\ServiceManager;

abstract class Component
{
	/** @var ServiceManager */
	protected $serviceMgr;

	public function __construct(ServiceManager $serviceMgr)
	{
		$this->serviceMgr = $serviceMgr;
	}

	/**
	 * Get the component title.
	 *
	 * @return string Title
	 */
	public abstract function getTitle();

	/**
	 * Get the component description.
	 *
	 * @return string Description
	 */
	public abstract function getDescription();

	/**
	 * Get parameter types.
	 *
	 * @return array ComponentParameter
	 */
	public abstract function getParameterTypes();

	/**
	 * Is the first row column headers?
	 *
	 * @return bool Is the first row column headers?
	 */
	public abstract function isFirstRowHeaders();

	/**
	 * Terminate a component.
	 *
	 * @return mixed true = success, string = error message
	 */
	public function terminate()
	{
		return true;
	}

}