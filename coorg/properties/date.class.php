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
				return $this->_value;
			}
			else
			{
				return strtotime($this->_value);
			}
		}
	}

	public function validate ($t)
	{
		return true;
	}

	public function toDB($value)
	{
		if (is_string($value))
		{
			return $value;
		}
		else if (is_int($value))
		{
			return date('Y-m-d', $value);
		}
	}
}

?>
