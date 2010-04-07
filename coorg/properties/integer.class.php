<?php

class PropertyInteger extends Property implements IProperty
{
	private $_maxInt;

	public function __construct($name, $max = null)
	{
		parent::__construct($name);
		$this->_maxInt = $max;
	}
	
	public function get()
	{
		return $this->toDB($this->_value);
	}

	public function validate($for)
	{
		$value = trim($this->_value);
		if ($value == '' && $this->isRequired($for))
		{
			$this->error('%n is required');
			return false;
		}
		else if ($value != '')
		{
			if (is_string($this->_value))
			{
				if (!preg_match('/^[+-]?[0-9]*$/', $value))
				{
					$this->error('%n is not a valid number');
					return false;
				}
			}
			else if (!is_int($this->_value))
			{
				$this->error('%n is not a valid number');
				return false;
			}
			if ($this->_maxInt !== null &&
			    (int)$this->_value > $this->_maxInt)
			{
				$this->error('%n is a too large number');
				return false;
			}
			return true;
		}
		else
		{
			return true;
		}
	}

	protected function toDB($value)
	{
		$value = trim($value);
		if ($value == '')
		{
			return null;
		}
		else
		{
			return (int)$value;
		}
	}
}

?>
