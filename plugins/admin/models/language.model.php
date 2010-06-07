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
 * @property primary; language String(t('Language code'), 6); required
 * @property name String(t('Language name'), 128); required
*/
class Language extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public static function get($code)
	{
		$q = DB::prepare('SELECT * FROM Language WHERE language=:code');
		$q->execute(array(':code' => $code));
		
		if ($row = $q->fetch())
		{
			return self::fetch($row, 'Language');
		}
		else
		{
			return null;
		}
	}
	
	public static function languages()
	{
		$q = DB::prepare('SELECT * FROM Language ORDER BY language');
		$q->execute();
		
		$l = array();
		foreach ($q->fetchAll() as $row)
		{
			$l[] = self::fetch($row, 'Language');
		}
		return $l;
	}
	
	protected function validate($for)
	{
		parent::validate($for);
		if ($for == 'insert' && self::get($this->language))
		{
			$this->language_error = t('%n is used');
			throw new ValidationException($this);
		}
	}
}

?>
