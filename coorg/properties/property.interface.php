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

interface IPropertyVariant
{
	public static function instance(IProperty $property, $args);

	public function get();
	public function set($value);
	
	public function update();
}

interface IProperty
{
	public function get();
	public function set($value);
	public function db();
	public function raw();
	public function errors();
	public function error($msg);

	public function required();
	public function validate($type);
	public function only($type);
	
	public function old();
	public function changed();
	public function presave();
	public function postsave();
	
	public function attachVariant(IPropertyVariant $var);
}

abstract class Property
{
	protected $_name = null;
	protected $_value = null;
	protected $_errors = array();
	protected $_required = false;
	protected $_oldValue = null;
	private $_variants = array();
	
	public function __construct($name)
	{
		$this->_name = $name;
	}
	
	public function get()
	{
		return $this->_value;
	}
	
	public function set($value)
	{
		$this->_value = $value;
		foreach ($this->_variants as $variant)
		{
			$variant->update();
		}
	}
	
	public function raw()
	{
		return $this->_value;
	}
	
	public function db()
	{
		return $this->toDB($this->_value);
	}
	
	public function errors()
	{
		if ($this->_errors == array())
		{
			return null;
		}
		else if (count($this->_errors) == 1)
		{
			return $this->_errors[0];
		}
		else
		{
			return $this->_errors;
		}
	}
	
	public function error($msg)
	{
		if ($msg != null)
		{
			$this->_errors[] = str_replace('%n', $this->_name, $msg);
		}
		else
		{
			$this->_errors = array();
		}
	}
	
	public function required()
	{
		$this->_required = true;
	}
	
	public function only($for)
	{
		if ($this->_required === true)
		{
			$this->_required = array($for);
		}
		else if (is_array($this->_required))
		{
			$this->_required[] = $for;
		}
	}
	
	public function old()
	{
		return $this->toDB($this->_oldValue);
	}
	
	public function changed()
	{
		return $this->db() !== $this->old();
	}
	
	public function presave()
	{
	}
	
	public function postsave()
	{
		$this->_oldValue = $this->_value;
	}
	
	public function attachVariant(IPropertyVariant $variant)
	{
		$this->_variants[] = $variant;
	}
	
	protected function toDB($value)
	{
		return $value;
	}
	
	protected function isRequired($type)
	{
		if (is_array($this->_required))
		{
			return in_array($type, $this->_required);
		}
		else
		{
			return $this->_required;
		}
	}
}

?>
