<?php

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
	public function setUnchanged();
}

abstract class Property
{
	protected $_name = null;
	protected $_value = null;
	protected $_errors = array();
	protected $_required = false;
	protected $_oldValue = null;
	
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
			$this->_errors[] = t($msg, array('n' => $this->_name));
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
		return $this->db() != $this->old();
	}
	
	public function setUnchanged()
	{
		$this->_oldValue = $this->_value;
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
