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

class TextInput extends UserInput
{
	private $_type;
	private $_autocomplete;

	public function __construct($type)
	{
		parent::__construct();
		$this->_type = $type;
	}
	
	public function setSpecificParameters(&$params)
	{
		if ($size = self::getParameter($params, 'size'))
		{
			switch ($size)
			{
				case 'wide':
					$chars = 40;
					break;
				case 'full-wide':
					$chars = 60;
					break;
				default:
					$chars = $size;
			}
			$this->_inputAttributes->size = $chars;
		}
		if ($autocomplete = self::getParameter($params, 'autocomplete'))
		{
			$this->_autocomplete = $autocomplete;
		}
	}

	public function render()
	{
		return $this->renderLabel() . $this->renderInput();
	}
	
	protected function renderInput()
	{
		$input = '<input type="'.$this->_type.'" name="'.$this->_name.'" '. 'id="'.$this->getID().'"';
		if ($this->_value)
		{
			$this->_inputAttributes->value = $this->_value;
		}
		else
		{
			// It is possible that the same inputis rendered more than once
			// (eg for the ListInput), and the value is changed to null.
			unset($this->_inputAttributes->value);
		}
		$input .= $this->renderOptions();
		$input .= '/><br />';
		if ($this->_autocomplete)
		{
			$input .= '<script>';
			if ($this->_autocomplete)
			{
				$input .= 'CoOrgAutoSuggest($("#'.$this->getID().'"), "'.CoOrg::createURL($this->_autocomplete) .'")';
			}
			$input .= '</script>';
		}
		return $input;
	}
}

class PasswordInput extends TextInput
{
	public function __construct()
	{
		parent::__construct('password');
	}
	
	public function setValue($value)
	{
		$this->_value = null;
	}
}

class DateInput extends TextInput
{
	public function __construct()
	{
		parent::__construct('date');
	}
}

?>
