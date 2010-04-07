<?php

class PropertyString extends Property implements IProperty
{
	private $_maxLength;

	public function __construct($name, $maxLength = -1)
	{
		parent::__construct($name);
		$this->_maxLength = $maxLength;
	}

	public function validate($for)
	{
		if (trim($this->_value) == '' && $this->isRequired($for))
		{
			$this->error('%n is required');
			return false;
		}
		if ($this->_maxLength > 0 && strlen(trim($this->_value)) > $this->_maxLength)
		{
			$this->error('%n is too long');
			return false;
		}
		return true;
	}

	public function get()
	{
		$value = trim($this->_value);
		if ($value == '')
		{
			return null;
		}
		else
		{
			return $value;
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
			return $value;
		}
	}
}

?>
