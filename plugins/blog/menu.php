<?php

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
