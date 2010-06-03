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
