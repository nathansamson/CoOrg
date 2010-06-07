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
 * @property primary; ID String('ID', 256); required
 * @property primary; language String(t('Language'), 6); required
 * @property created Date('Created'); required
 * @property updated Date('Updated'); required only('update')
 * @property author String(t('Author'), 24); required
 * @property lastEditor String('Last Editor', 24); required only('update')
 * @property title String(t('Title'), 256); required
 * @property content String(t('Content')); required
*/
class Page extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function beforeInsert()
	{
		$this->created = date('Y-m-d');
		$this->ID = self::normalizeTitle($this->title, $this->language);
	}
	
	public function beforeUpdate()
	{
		$this->updated = date('Y-m-d');
	}

	public static function get($ID, $language)
	{
		$q = DB::prepare('SELECT * FROM Page WHERE ID=:ID AND language=:l');
		$q->execute(array('ID' => $ID, ':l' => $language));
		
		if ($row = $q->fetch())
		{
			return self::fetch($row, 'Page');
		}
		else
		{
			return null;
		}
	}
	
	public static function pages($language)
	{
		$pager = new PagePager('SELECT * FROM Page WHERE language=:l
		                         ORDER BY title',
		                       array(':l' => $language));
		return $pager;
	}
	
	private static function normalizeTitle($title, $language)
	{
		$basenorm = strtolower(str_replace(' ', '-', $title));
		$norm = $basenorm;
		$i = 1;
		while (self::get($norm, $language)) {
			$norm = $basenorm.$i;
			$i++;
		}
		return $norm;
	}
}
