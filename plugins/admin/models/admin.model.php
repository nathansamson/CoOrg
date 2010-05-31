<?php

class Admin
{
	static private $_modules = array();

	public static function registerModule($module)
	{
		self::$_modules[] = $module;
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
			$mi = new $m;
			if ($mi->isAllowed($user))
			{
				$modules[] = $mi;
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
