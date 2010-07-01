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

/**
 * @property primary; name String(t('Group name'), 26); required
 * @property system Bool(t('System group')); required
*/
class UserGroup extends DBModel
{
	public function __construct($name = null)
	{
		parent::__construct();
		$this->name = $name;
	}

	public function beforeInsert()
	{
		if ($this->system === null)
		{
			$this->system = false;
		}
	}
	
	public function add($user)
	{
		$member = new UserGroupMember($user, $this->name);
		$member->save();
	}
	
	public function remove($user)
	{
		$member = UserGroupMember::get($user, $this->name);
		$member->delete();
	}
	
	public function members()
	{
		return UserGroupMember::getAllInGroup($this->name);
	}
	
	public function grant($key)
	{
		$acl = Acl::set($this->name, $key, true);
	}
	
	public function revoke($key)
	{
		$acl = Acl::set($this->name, $key, false);
	}
	
	public static function getGroupByName($name)
	{
		$q = DB::prepare('SELECT * FROM UserGroup WHERE name=:name');
		$q->execute(array(':name' => $name));
		
		$row = $q->fetch();
		if ($row != false)
		{
			return self::fetch($row, 'UserGroup');
		}
		else
		{
			return null;
		}
	}
	
	protected function validate($for)
	{
		if ($for == 'insert' && self::getGroupByName($this->name) != null)
		{
			$this->name_error = 'Group name is already used';
			throw new ValidationException($this);
		}
	}
}

?>
