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
 * @property primary; name String(t('menu|Name'), 32); required
 * @property description String(t('menu|Description'), 256);
*/
class Menu extends DBModel
{
	private static $_providers = array();

	public function __construct()
	{
		parent::__construct();
	}

	protected function validate($for)
	{
		parent::validate($for);
		if ($for == 'insert')
		{
			if (self::get($this->name))
			{
				$this->name_error = t('Name already exists');
				throw new ValidationException($this);
			}
		}
		else if ($for == 'update')
		{
			if ($this->name_changed && self::get($this->name))
			{
				$this->name_error = t('Name already exists');
				throw new ValidationException($this);
			}
		}
	}

	public static function get($name)
	{
		$q = DB::prepare('SELECT * FROM Menu WHERE name=:name');
		$q->execute(array(':name' => $name));
		
		$row = $q->fetch();
		if ($row)
		{
			return self::fetch($row, 'Menu');
		}
		else
		{
			return null;
		}
	}
	
	public static function registerEntryProvider($class)
	{
		self::$_providers[] = $class;
	}
	
	public static function getProviders()
	{
		Coorg::loadPluginInfo('menu');
		return self::$_providers;
	}
	
	public static function all()
	{
		$q = DB::prepare('SELECT * FROM Menu ORDER BY name');
		$q->execute();
		
		$menus = array();
		foreach ($q->fetchAll() as $row)
		{
			$menus[] = self::fetch($row, 'Menu');
		}
		return $menus;
	}
	
	public static function providerActionCombos($language)
	{
		$urls = array();
		foreach (Menu::getProviders() as $p)
		{
			$pi = new $p;
			if ($pi instanceof IDataMenuEntryProvider)
			{
				$urls[$p] = $pi->name();
			}
			else
			{
				$actions = array();
				foreach ($pi->listActions() as $key => $label)
				{
					$data = $pi->listData($key, $language);
					if ($data === null)
					{
						$actions[$p.'/'.$key] = $label;
					}
					else
					{
						foreach ($data as $keyData => $d)
						{
							$actions[$p.'/'.$key.'/'.$keyData] = $d;
						}
					}
	 			}
				$urls[] = array('label' => $pi->name(),
					            'options' => $actions);
			}
		}
		return $urls;
	}
}

?>
