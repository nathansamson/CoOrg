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
				return strptime($this->_value);
			}
		}
	}

	public function validate ($t)
	{
		return true;
	}

	public function toDB($value)
	{
		return date('Y-m-d', $value);
	}
}

?>
