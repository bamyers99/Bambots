<?php
/**
 Copyright 2014 Myers Enterprises II

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

class HTMLForm
{
	/**
	 * Get select form field markup
	 *
	 * @param string $name Field name
	 * @param array $options $value => $display
	 * @param string $selected Selected value (default: <empty>)
	 * @return string Select form field markup
	 */
	public static function generateSelect($name, $options, $selected = '')
	{
		$markup = "<select name='$name'>";
		foreach ($options as $value => $display) {
			$selected_option = '';
			if ($value == $selected) $selected_option = ' selected="selected"';
			$value = htmlspecialchars($value);
			$display = htmlspecialchars($display);
			$markup .= "<option value=\"$value\"$selected_option>$display</option>";
		}
		$markup .= '</select>';
		return $markup;
	}
}