<?php

/**
 * @property primary autoincrement; ID Integer('ID');
 * @property menu String('Menu', 32); required
 * @property sequence Integer('Sequence'); required
 * @property language String('Language', 6); required
 * @property url String('URL', 1024); required
 * @property title String('Title', 64); required
 * @property provider String('Provider', 64); required
 * @property action String('Action', 64); required
 * @property data String('Data', 128);
 * @property writeonly; entryID String('EntryID');
*/
class MenuEntry extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	protected function beforeInsert()
	{
		//TODO: See if we can insert this as a subquery into the insert of DBModel
		$q = DB::prepare('SELECT MAX(sequence) AS seq FROM MenuEntry WHERE
		                    menu=:menu AND language=:l');
		$q->execute(array(':menu' => $this->menu, ':l' => $this->language));
		$result = $q->fetch();
		if ($result)
		{
			$this->sequence = (int)$result['seq'] + 1;
		}
		else
		{
			$this->sequence = 0;
		}
	}
	
	protected function beforeUpdate()
	{
		if ($this->sequence_changed)
		{
			if ($this->sequence_old > $this->sequence_db)
			{
				// Moved forward
				$q = DB::prepare('UPDATE MenuEntry SET sequence=sequence+1
				                        WHERE sequence < :oldsequence
				                          AND sequence >= :newsequence
				                          AND menu=:menu
				                          AND language=:l');
			}
			else
			{
				// Moved backward
				$q = DB::prepare('UPDATE MenuEntry SET sequence=sequence-1
				                        WHERE sequence > :oldsequence
				                          AND sequence <= :newsequence
				                          AND menu=:menu
				                          AND language=:l');
			}
			$q->execute(array(':oldsequence' => $this->sequence_old,
				              ':newsequence' => $this->sequence_db,
				              ':menu' => $this->menu,
				              ':l' => $this->language));
		}
	}
	
	public function delete()
	{
		parent::delete();
		$q = DB::prepare('UPDATE MenuEntry SET sequence=sequence-1
				                        WHERE sequence > :sequence
				                          AND menu=:menu
				                          AND language=:l');
		$q->execute(array(':sequence' => $this->sequence,
				          ':menu' => $this->menu,
				          ':l' => $this->language));
	}
	
	public function __set($name, $value)
	{
		if ($name == 'entryID')
		{
			$p = explode('/', $value, 3);
			
			CoOrg::loadPluginInfo('menu');
			if (!class_exists($p[0]))
			{
				$this->entryID_error = t('Provider not found');
				return;
			}
			
			$this->provider = $p[0];
			if (count($p) > 1)
			{
				$this->action = $p[1];
				
				if (count($p) > 2)
				{
					$this->data = $p[2];
				}
				else
				{
					$this->data = null;
				}
				$this->url = $p[0]::url($this->action, $this->language, $this->data);
			}
			else
			{
				$this->action = 'do';
				$this->url = $p[0]::url($this->data, $this->language);
			}
		}
		parent::__set($name, $value);
	}
	
	public static function get($ID)
	{
		$q = DB::prepare('SELECT * FROM MenuEntry WHERE ID=:ID');
		$q->execute(array(':ID' => $ID));
		
		if ($row = $q->fetch())
		{
			return self::constructByRow($row);
		}
		else
		{
			return null;
		}
	}

	public static function entries($menu, $language)
	{
		$q = DB::prepare('SELECT * FROM MenuEntry WHERE
		                    menu=:menu AND language=:l
		                  ORDER BY sequence');
		$q->execute(array(':menu' => $menu, ':l' => $language));
		
		$entries = array();
		foreach ($q->fetchAll() as $row)
		{
			$entries[] = self::constructByRow($row);
		}
		return $entries;
	}
	
	private static function constructByRow($row)
	{
		$entry = new MenuEntry();
		$entry->ID = $row['ID'];
		$entry->menu = $row['menu'];
		$entry->url = $row['url'];
		$entry->title = $row['title'];
		$entry->language = $row['language'];
		$entry->sequence = $row['sequence'];
		$entry->provider = $row['provider'];
		$entry->action = $row['action'];
		$entry->data = $row['data'];
		$entry->setSaved();
		return $entry;
	}
}
?>
