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

class Admin
{
	static private $_modules = array();
	static private $_orphanTabs = array();
	
	public static function tabs($moduleName, $current)
	{
		$module = self::$_modules[$moduleName];
		
		$session = UserSession::get();
		if ($session)
		{
			$user = $session->user();
		}
		else
		{
			return null;
		}
		
		return $module->tabs($user, $current);
	}

	public static function registerModule($moduleName)
	{
		$module = new $moduleName;
		self::$_modules[$moduleName] = $module;
		if (array_key_exists($moduleName, self::$_orphanTabs))
		{
			$tabs = self::$_orphanTabs[$moduleName];
			foreach ($tabs as $tab)
			{
				$module->addTab($tab);
			}
			unset(self::$_orphanTabs[$moduleName]);
		}
	}

	public static function registerTab($tabName, $moduleName)
	{
		if (array_key_exists($moduleName, self::$_modules))
		{
			self::$_modules[$moduleName]->addTab($tabName);
		}
		else if (array_key_exists($moduleName, self::$_orphanTabs))
		{
			self::$_orphanTabs[$moduleName][] = $tabName;
		}
		else
		{
			self::$_orphanTabs[$moduleName] = array($tabName);
		}
		
	}

	public static function modules()
	{
		$session = UserSession::get();
		if ($session)
		{
			$user = $session->user();
			if (!Acl::isAllowed($user->username, 'admin'))
			{
				return null;
			}
		}
		else
		{
			return null;
		}	
		
		CoOrg::loadPluginInfo('admin');
		$modules = array();
		foreach (self::$_modules as $m)
		{
			if ($m->isAllowed($user))
			{
				$modules[] = $m;
			}
		}
		usort($modules, array('Admin', 'cmpModule'));
		return $modules;
	}
	
	public static function cmpModule($m1, $m2)
	{
		if ($m1->priority < $m2->priority)
		{
			return -1;
		}
		elseif ($m1->priority == $m2->priority)
		{
			if ($m1->name < $m2->name)
			{
				return -1;
			}
			elseif ($m1->name == $m2->name)
			{
				return 0;
			}
			else
			{
				return 1;
			}
		}
		else
		{
			return 1;
		}
	}
}

?>
