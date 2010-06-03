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
