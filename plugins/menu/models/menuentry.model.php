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
 * @property primary autoincrement; ID Integer('ID');
 * @property menuID String(t('Menu'), 32); required
 * @property language String('Language', 6); required
 * @property url String(t('URL'), 1024); required
 * @property title String(t('menu|Title'), 64); required
 * @property provider String('Provider', 64); required
 * @property action String('Action', 64); required
 * @property data String('Data', 128);
 * @property writeonly; entryID String('EntryID');
 * @extends Sortable menu language
*/
class MenuEntry extends DBModel
{
	public function __construct()
	{
		parent::__construct();
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
				$this->url = call_user_func(array($p[0], 'url'), $this->action, $this->language, $this->data);
			}
			else
			{
				$this->action = 'do';
				$this->url = call_user_func(array($p[0], 'url'), $this->data, $this->language);
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
			return self::fetch($row, 'MenuEntry');
		}
		else
		{
			return null;
		}
	}
}
?>
