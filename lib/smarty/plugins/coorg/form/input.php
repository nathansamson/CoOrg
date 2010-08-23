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

interface IUserInput
{
	public function setPlaceholder($placeholder);
	public function setValue($value);
	public function setObject($instance, $name);
	
	public function required();
	public function readonly();
}

abstract class FormElement
{
	protected $_name;

	abstract public function render();
	abstract public function setSpecificParameters(&$params);
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	
	protected function errors()
	{
		return null;
	}
	
	public static function getObject($type)
	{
		switch ($type)
		{
			case 'text':
			case 'url':
			case 'email':
			case 'search':
				return new TextInput($type);
			case 'password':
				return new PasswordInput;
			case 'date':
				return new DateInput();
			case 'file':
				return new FileInput();
			case 'textarea':
				return new Textarea();
			case 'select':
				return new SelectInput();
			case 'radio':
				return new RadioboxInput();
			case 'checkbox';
				return new CheckboxInput();
			case 'submit':
				return new SubmitButton();
			case 'hidden':
				return new HiddenInput();
			default:
				throw new Exception('Unknown input type ('.$type.').');
		}
	}
	
	public static function getParameter(&$params, $key, $default = null)
	{
		if (array_key_exists($key, $params))
		{
			$v = $params[$key];
			unset($params[$key]);
		}
		else
		{
			$v = $default;
		}
		return $v;
	}
	
	public static function getBoolParameter(&$params, $key)
	{
		if (array_key_exists($key, $params))
		{
			unset($params[$key]);
			return true;
		}
		return false;
	}
}

abstract class LabeledFormElement extends FormElement
{
	protected $_label;
	private $_idPrefix;
	protected $_labelClasses = array();

	public function setLabel($label)
	{
		$this->_label = $label;
	}
	
	public function setIDPrefix($idPrefix)
	{
		$this->_idPrefix = $idPrefix;
	}
	
	abstract public function disable();
	abstract public function tabindex($index);
	
	protected function getID()
	{
		if ($this->_idPrefix)
		{
			return $this->_idPrefix . '_' . $this->_name;
		}
		else
		{
			return $this->_name;
		}
	}
}

abstract class UserInput extends LabeledFormElement implements IUserInput
{
	protected $_objectName;
	protected $_instance;
	protected $_value;
	protected $_inputAttributes;
	protected $_inputClasses = array();
	private $_errors;
	
	public function __construct()
	{
		$this->_inputAttributes = new stdClass;
	}

	public function required()
	{
		$this->_inputAttributes->required = true;
		$this->_labelClasses[] = 'required';
	}

	public function readonly()
	{
		$this->_inputAttributes->readonly = true;
		$this->_labelClasses[] = 'readonly';
	}
	
	public function disable()
	{
		$this->_inputAttributes->disabled = true;
		$this->_labelClasses[] = 'disabled';
	}
	
	public function tabindex($index)
	{
		$this->_inputAttributes->tabindex = $index;
	}

	public function setObject($instance, $objectName)
	{
		$this->_objectName = $objectName;
		$this->_instance = $instance;
		
		$rawName = $objectName.'_raw';
		$this->setValue($instance->$rawName);
		
		$errorName = $objectName.'_errors';
		$this->setErrors($instance->$errorName);
	}
	
	public function setValue($value)
	{
		$this->_value = $value;
	}
	
	public function setPlaceholder($p)
	{
		$this->_inputAttributes->placeholder = $p;
	}
	
	protected function renderLabel()
	{
		$label = '<label for="'.$this->getID().'"' . 
		                 ($this->_labelClasses ? ' class="'.implode(' ', $this->_labelClasses).'"' : '') . 
		             '>'.$this->_label.'</label>';
		
		if (is_string($this->_errors))
		{
			$label .= '<p class="form-error">'.$this->_errors.'</p>';
		}
		else if ($this->_errors != null)
		{
			foreach ($this->_errors as $error)
			{
				$label .= '<p class="form-error">'.$errors.'</p>';
			}
		}
	    return $label;
	}
	
	protected function setErrors($errors)
	{
		$this->_errors = $errors;
		if ($errors)
		{
			$this->_labelClasses[] = 'error';
			$this->_labelClasses[] = 'invalid'; // Much better name, keep error for backwards compat
			$this->_inputClasses[] = 'invalid';
			$this->_inputClasses[] = 'error';
		}
	}
	
	protected function renderOptions()
	{
		$input = '';
		foreach ($this->_inputAttributes as $attribute => $value)
		{
			
			if ($value === true)
			{
				$value = $attribute;
			}
			else if ($value === false)
			{
				continue;
			}
			$input .= ' ' . $attribute . '="' . $value . '"';
		}
		if ($this->_inputClasses)
		{
			$input .= ' class="' . implode(' ', $this->_inputClasses) . '"';
		}
		return $input;
	}
}

class HiddenInput extends FormElement
{
	public function setSpecificParameters(&$params)
	{		
	}
	
	public function render()
	{
	}
}

include_once dirname(__FILE__).'/textinput.php';
include_once dirname(__FILE__).'/fileinput.php';
include_once dirname(__FILE__).'/textarea.php';
include_once dirname(__FILE__).'/select.php';
include_once dirname(__FILE__).'/checkbox.php';
include_once dirname(__FILE__).'/radiobox.php';
include_once dirname(__FILE__).'/submit.php';


?>
