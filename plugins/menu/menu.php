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

class URLMenuEntryProvider implements IDataMenuEntryProvider
{
	public static function name()
	{
		return t('URL');
	}

	public static function url($data, $language)
	{
		if (! (strpos($data, 'http://') === 0 ||
		       strpos($data, 'https://') === 0))
		{
			return 'http://'.$data;
		}
		else
		{
			return $data;
		}
	}
}

class HomeMenuEntryProvider implements IMenuEntryProvider
{
	public static function name()
	{
		return t('Home');
	}

	public static function listActions()
	{
		return array('home' => t('Home'));
	}
	
	public static function listData($action, $language)
	{
		return null;
	}

	public static function url($action, $data, $language)
	{
		return CoOrg::createURL(array(), $language);
	}
}

Menu::registerEntryProvider('URLMenuEntryProvider');
Menu::registerEntryProvider('HomeMenuEntryProvider');

?>
