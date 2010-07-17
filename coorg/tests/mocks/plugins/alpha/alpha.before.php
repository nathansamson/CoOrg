<?php

class AlphaBeforeController extends Controller
{
	private $_stopped;

	public function in($value, $a, $name = null)
	{
		if ($value != 'reverse')
		{
			$this->_stopped = ($value == 'myStopCode');
			$this->value = $value;
			$this->name = $name;
			$this->arbitraryValue = $a;
		}
		else
		{
			$this->combined = strrev($a);
		}
	}

	public function out()
	{
		if ($this->_stopped)
		{
			$this->status = 'stopped';
		}
		return !$this->_stopped;
	}
}

?>
