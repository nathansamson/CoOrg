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
			$this->error(t('%n is required'));
			return false;
		}
		else if ($value != '')
		{
			if (is_string($this->_value))
			{
				if (!preg_match('/^[+-]?[0-9]*$/', $value))
				{
					$this->error(t('%n is not a valid number'));
					return false;
				}
			}
			else if (!is_int($this->_value))
			{
				$this->error(t('%n is not a valid number'));
				return false;
			}
			if ($this->_maxInt !== null &&
			    (int)$this->_value > $this->_maxInt)
			{
				$this->error(t('%n is a too large number'));
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
		if (is_string($value)) $value = trim($value);
		if ($value === '' || $value === null)
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
