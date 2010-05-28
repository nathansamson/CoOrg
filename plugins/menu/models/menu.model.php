<?php

/**
 * @property primary; name String('Name', 32); required
 * @property description String('Description', 256);
*/
class Menu extends DBModel
{
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
		
		$row = $q->fetch(PDO::FETCH_ASSOC);
		if ($row)
		{
			return self::constructFromDB($row);
		}
		else
		{
			return null;
		}
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
