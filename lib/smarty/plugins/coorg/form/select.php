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

abstract class OptionsFormElement extends UserInput
{
	protected $_options = array();

	abstract protected function renderOption($key, $label, $selected);
	
	protected function renderOptionGroup($label, $options)
	{
		return $this->renderOptionTags($options);
	}
	
	public function setSpecificParameters(&$params)
	{
		if ($options = self::getParameter($params, 'options'))
		{
			foreach ($options as $key => $value)
			{
				$this->_options[$key] = $value;
			}
		}
	}
	
	protected function renderOptionTags($options = null)
	{
		$s = '';
		$options = $options ? $options : $this->_options;
		foreach ($options as $key => $option)
		{
			if (!is_array($option))
			{
				if (is_array($this->_value))
				{
					$s .= $this->renderOption($key, $option, in_array($key, $this->_value));
				}
				else
				{
					$s .= $this->renderOption($key, $option, $key == $this->_value);
				}
			}
			else
			{
				$s .= $this->renderOptionGroup($option['label'], $option['options']);
			}
		}
		return $s;
	}
}

class SelectInput extends OptionsFormElement
{
	private $_multiple;
	
	public function setSpecificParameters(&$params)
	{
		parent::setSpecificParameters($params);
		if ($select = self::getBoolParameter($params, 'multiple'))
		{
			$this->_inputAttributes->multiple = true;
			$this->_name .= '[]';
		}
		else
		{
			$this->_labelClasses[] = 'select';
		}
	}

	protected function renderOption($key, $label, $selected)
	{
		return '<option value="'.$key.'"' . ($selected ? ' selected="selected"' : '') . '>'.$label.'</option>';
	}
	
	protected function renderOptionGroup($label, $options)
	{
		return '<optgroup label="'.$label.'">' . $this->renderOptionTags($options) . '</optgroup>';
	}
	
	public function render()
	{
		return $this->renderLabel() . '<select name="'.$this->_name.'" id="'.$this->getID().'"'  .
		                                     $this->renderOptions().'>' . $this->renderOptionTags() . '</select><br />';
	}
}

?>
