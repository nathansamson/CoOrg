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
 * @property primary; groupID String(t('Group'), 26); required
 * @property primary; keyID String(t('Key'), 32); required
 * @property allowed Bool(t('Allowed')); required
*/
class Acl extends DBModel
{
	private static $_ownerMethods = array();

	protected function __construct($group, $key, $allowed)
	{
		parent::__construct();
		$this->groupID = $group;
		$this->keyID = $key;
		$this->allowed = $allowed;
	}
	
	public static function registerOwnsClass($ownsObjectName, $ownsChecker)
	{
		self::$_ownerMethods[$ownsObjectName] = $ownsChecker;
	}
	
	public static function owns($user, $object)
	{
		CoOrg::loadPluginInfo('acl');
		$class = get_class($object);
		while ($class && ! array_key_exists($class, self::$_ownerMethods)) {
			$class = get_parent_class($class);
		}
		if ($class)
		{
			return self::$_ownerMethods[$class]->owns($user, $object);
		}
		else
		{
			return false;
		}
	}
	
	public static function set($group, $key, $allowed)
	{
		if ($acl = self::get($group, $key))
		{
			$acl->allowed = $allowed;
		}
		else
		{
			$acl = new Acl($group, $key, $allowed);
		}
		$acl->save();
	}

	public static function isAllowed($user, $key)
	{
		$q = DB::prepare('SELECT * FROM Acl WHERE 
		                   groupID IN (SELECT groupID FROM UserGroupMember
		                                      WHERE userID=:user)
		                   AND keyID=:key');
		
		$q->execute(array('key' => $key, 'user' => $user));
		
		$allowed = null;
		foreach ($q->fetchAll() as $row)
		{	
			if ($row['allowed'] == 1)
			{
				return true;
			}
			else if ($row['allowed'] == 0)
			{
				$allowed = false;
			}
		}
		if ($allowed === null)
		{
			// Find default, for now the default is false
			$allowed = false;
		}
		return $allowed;
	}
	
	private function get($group, $key)
	{
		$q = DB::prepare('SELECT * FROM Acl WHERE
		                     groupID=:group AND keyID=:key');
		$q->execute(array('group' => $group, 'key'=>$key));
		
		if ($row = $q->fetch())
		{
			$a = new Acl($row['groupID'], $row['keyID'], $row['allowed']);
			$a->setSaved();
			return $a;
		}
		else
		{
			return null;
		}
	}
}

?>
