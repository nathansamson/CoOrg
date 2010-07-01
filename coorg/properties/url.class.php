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

class PropertyURL extends PropertyString
{
	public function __construct($name)
	{
		parent::__construct($name, 1024);
	}
	
	public function get()
	{
		if ($this->_value == null)
		{
			return null;
		}
		if (strpos($this->_value, 'http://') !== 0 &&
		    strpos($this->_value, 'https://') !== 0)
		{
			return 'http://'.$this->_value;
		}
		return $this->_value;
	}
}
