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
