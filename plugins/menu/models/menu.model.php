<?php

/**
 * @property primary; name String('Name', 32); required
 * @property description String('Description', 256);
*/
class Menu extends DBModel
{
	private static $_providers = array();

	public function __construct()
	{
		parent::__construct();
	}

	public function entries($language)
	{
		return MenuEntry::entries($this->name, $language);
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
			return self::constructFromDB($row);
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
			$menus[] = self::constructFromDB($row);
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
	
	private static function constructFromDB($row)
	{
		$menu = new Menu();
		$menu->name = $row['name'];
		$menu->description = $row['description'];
		$menu->setSaved();
		return $menu;
	}
}

?>
