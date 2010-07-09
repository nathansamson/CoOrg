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

class MockCoOrgFile extends CoOrgFile
{
	static $_deleted = array();

	public function __construct($base, $name, $manager)
	{
		parent::__construct($base, $name, $manager);
	}

	public function delete()
	{
		self::$_deleted[] = $this->fullpath();
	}
	
	public static function isDeleted($name)
	{
		return in_array($name, self::$_deleted);
	}
}

class MockDataManager
{
	private $_base;

	public function __construct($base)
	{
		$this->_base = $base;
	}

	public function get($name)
	{
		return new MockCoOrgFile($this->_base, $name, $this);
	}
}

?>
