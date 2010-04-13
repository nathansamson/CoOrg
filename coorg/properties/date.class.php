<?php

abstract class PropertyDateTimeish extends Property
{
	public function get()
	{
		if (trim($this->_value) == '')
		{
			return null;
		}
		else
		{
			if (is_int($this->_value))
			{
				if ($this->_value > 0)
				{
					return strtotime($this->format( $this->_value));
				}
				else
				{
					return null;
				}
			}
			else
			{
				return strtotime($this->_value);
			}
		}
	}
	
	
	public function validate ($t)
	{
		if ($this->isRequired($t) && (trim($this->_value) == '' || $this->_value == 0))
		{
			$this->error('%n is required');
			return false;
		}
		if (is_string($this->_value))
		{
			$s = trim($this->_value);
			if ($s != '')
			{
				if (strtotime($this->_value) === false)
				{
					$this->error('%n is not a valid date');
					return false;
				}
			}
		}
		return true;
	}

	public function toDB($value)
	{
		if (is_string($value) && trim($value) != '')
		{
			return $this->format(strtotime($value));
		}
		else if (is_int($value) && $value > 0)
		{
			return $this->format($value);
		}
		return null;
	}
	
	abstract protected function format($value);
}

class PropertyDate extends PropertyDateTimeish implements IProperty
{
	protected function format($value)
	{
		return date('Y-m-d', $value);
	}
}

class PropertyDateTime extends PropertyDateTimeish implements IProperty
{
	protected function format($value)
	{
		return date('Y-m-d H:i:s', $value);
	}
}

?>
