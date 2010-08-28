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

class TextListInput extends LabeledFormElement implements IUserInput
{
	private $_listClass;
	private $_initialInputs;
	private $_textInput;
	private $_autocomplete;
	private $_value = array();

	public function __construct()
	{
		$this->_textInput = new TextInput('text');
	}

	public function setSpecificParameters(&$params)
	{
		if ($class = self::getParameter($params, 'listClass'))
		{
			$this->_listClass = $class;
		}
		if ($initial = self::getParameter($params, 'initial'))
		{
			$this->_initialInputs = $initial;
		}
		if ($autocomplete = self::getParameter($params, 'autocomplete'))
		{
			$this->_autocomplete = $autocomplete;
		}
		$this->_textInput->setSpecificParameters($params);
	}
	
	public function setLabel($label)
	{
		parent::setLabel($label);
		$this->_textInput->setLabel($label);
	}
	
	public function tabindex($i)
	{
		$this->_textInput->tabindex($i);
	}
	
	public function required()
	{
		$this->_textInput->required();
	}
	
	public function readonly()
	{
		$this->_textInput->readonly();
	}
	
	public function disable()
	{
		$this->_textInput->disable();
	}
	
	public function setPlaceholder($p)
	{
		$this->_textInput->setPlaceholder($p);
	}
	
	public function setValue($value)
	{
		$this->_value = $value;
	}
	
	public function setObject($instance, $name)
	{
		$rawName = $name.'_raw';
		$this->setValue($instance->$rawName);
	}

	public function render()
	{
		$noscript = '<noscript>';
		$i = 1;
		$input = $this->_textInput;
		foreach ($this->_value as $listValue)
		{
			$input->setName($this->_name . $i . '[]');
			$input->setValue($listValue);
			$noscript .= $input->render();
			$i++;
		}
		
		$input->setValue(null);
		for ($j = 1; $j < $this->_initialInputs; $j++) // Start from one, the javascript version adds one to
		{	
			$input->setName($this->_name . $i . '[]');
			$noscript .= $input->render();
			$i++;
		}
		$noscript .= '</noscript>';
		
		$input->setName($this->_name . '[]');
		$script = $input->render();
		
		$script .= '<script>';
		$script .= '$(window).ready(function() {
					CoOrgList("'.$input->getID().'", "'.$this->_name.'", 
					               Array("'.implode('","', $this->_value).'"),
					               ' . ($this->_autocomplete ? 'false' : 'true') . ', 
					               "'. $this->_listClass .'");
					});';
		if ($this->_autocomplete)
		{
			$script .= 'CoOrgAutoSuggest($("#'.$this->getID().'"), "'.CoOrg::createURL($this->_autocomplete) .'", true)';
		}
		$script .= '</script>';
		
		return $noscript.$script;
	}
}

?>
