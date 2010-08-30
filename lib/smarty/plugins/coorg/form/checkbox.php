<?php
/*
 * Copyright 2010 Nathan Samson <nathansamson at gmail dot com>
 *
 * This file is part of CoOrg.
 *
 * CoOrg is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

  * CoOrg is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with CoOrg.  If not, see <http://www.gnu.org/licenses/>.
*/

class CheckboxInput extends OptionsFormElement
{
	protected function renderOption($key, $option, $selected)
	{
		return '<label class="option">
		                 <input type="checkbox" name="'.$this->_name.'[]" 
		                        value="'.$key.'" '.$this->renderOptions().
		                        ($selected ? ' checked="checked"' : '').'/>'.$option.'</label>';
	}
	
	public function setSpecificParameters(&$params)
	{
		parent::setSpecificParameters($params);
		if ($checked = self::getBoolParameter($params, 'checked'))
		{
			$this->_inputAttributes->checked = true;
		}
	}
	
	public function setValue($value)
	{
		if ($value === true)
		{
			$this->_inputAttributes->checked = true;
		}
		else
		{
			parent::setValue($value);
		}
	}
	
	public function render()
	{
		if ($this->_options != array())
		{
			return $this->renderLabel() . $this->renderOptionTags();
		}
		else
		{
			return '<label>
		                 <input type="checkbox" name="'.$this->_name.'" 
		                        value="'. ($this->_value ? $this->_value : '_') .'" '.$this->renderOptions().
		                        '/>'.$this->_label.'</label>';
		}
	}
}

?>
