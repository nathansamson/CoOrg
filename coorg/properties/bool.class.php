<?php

class PropertyBool extends Property implements IProperty
{
	public function get()
	{
		if ($this->_value !== null && !(is_string($this->_value) && trim($this->_value) == ''))
		{
			if ($this->_value !== true && $this->_value == 'false')
			{
				return false;
			}
			else
			{
				return (bool)$this->_value;
			}
		}
		return null;
	}
	
	public function validate($type)
	{
		if ($this->isRequired($type) && $this->_value === null)
		{
			$this->error('%n is required');
			return false;
		}
		return true;
	}
	
	protected function toDB($value)
	{
		if ($value !== null && !(is_string($value) && trim($value) == ''))
		{
			if ($value === true || ($value && $value != 'false'))
			{
				return '1';
			}
			else
			{
				return '0';
			}
		}
		else
		{
			return null;
		}
	}
}

?>
