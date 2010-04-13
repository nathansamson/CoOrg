<?php

//FIXME implement better + test
class PropertyDate extends Property implements IProperty
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
					return strtotime(date('Y-m-d', $this->_value));
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
			return date('Y-m-d', strtotime($value));
		}
		else if (is_int($value) && $value > 0)
		{
			return date('Y-m-d', $value);
		}
		return null;
	}
}

?>
