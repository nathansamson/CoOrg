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

class PageMenuEntryProvider implements IMenuEntryProvider
{
	public static function name()
	{
		return t('Content');
	}
	
	public static function listActions()
	{
		return array('show' => t('page|Show'), 'create' => t('New Page'));
	}
	
	public static function listData($action, $language)
	{
		if ($action == 'show')
		{
			$pages = Page::pages($language);
			$menu = array();
			foreach ($pages->execute(0, 0) as $page)
			{
				$menu[$page->ID] = $page->title;
			}
			return $menu;
		}
		else
		{
			return null;
		}
	}
	
	public static function url($action, $language, $data)
	{
		if ($action == 'show')
		{
			return CoOrg::createURL(array('page', 'show', $data), $language);
		}
		else
		{
			return CoOrg::createURL(array('page', $action), $language);
		}
	}
}

Menu::registerEntryProvider('PageMenuEntryProvider');

?>
