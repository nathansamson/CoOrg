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

class BlogMenuEntryProvider implements IMenuEntryProvider
{
	public static function name()
	{
		return 'Blog';
	}
	
	public static function listActions()
	{
		return array('show' => 'Show', 'latest' => 'Latest', 'create' => 'Create');
	}
	
	public static function listData($action, $language)
	{
		if ($action == 'show')
		{
			$blogs = Blog::latest($language);
			$menu = array();
			foreach ($blogs as $blog)
			{
				$menu[date('Y-m-d', $blog->datePosted).'/'.$blog->ID] = $blog->title;
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
			$dataArray = explode('/', $data, 2);
			$dateArray = explode('-', $dataArray[0]);
			return CoOrg::createURL(array('blog', 'show', $dateArray[0],
			                               $dateArray[1], $dateArray[2],
			                               $dataArray[1]), $language);
		}
		else if ($action == 'latest')
		{
			return CoOrg::createURL(array('blog'), $language);
		}
		else
		{
			return CoOrg::createURL(array('blog', $action), $language);
		}
	}
}

Menu::registerEntryProvider('BlogMenuEntryProvider');

?>
