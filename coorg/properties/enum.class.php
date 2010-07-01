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

abstract class PropertyEnum extends Property implements IProperty
{
	private $_options;
	
	public function __construct($name, $options)
	{
		parent::__construct($name);
		$this->_options = $options;
	}
	
	public function set($v)
	{
		parent::set(trim($v));
	}
	
	public function get()
	{
		return $this->_value;
	}
	
	public function validate($type)
	{
		if ($this->isRequired($type) && $this->_value === null && $this->_value !== '')
		{
			$this->error(t('%n is required'));
			return false;
		}

		if (($this->_value !== null) && ($this->_value !== ''))
		{
			if (in_array($this->_value, $this->_options))
			{
				return true;
			}
			$this->error(t('Not a valid choice for %n'));
			return false;
		}
		else
		{
			return true;
		}
	}
	
	protected function toDB($value)
	{
		if (($value !== null) && ($this->_value !== ''))
		{
			if (is_numeric($this->_value))
			{
				return (string)$this->_value;
			}
			else
			{
				return $this->_value;
			}
		}
		else
		{
			return null;
		}
	}
}

?>
