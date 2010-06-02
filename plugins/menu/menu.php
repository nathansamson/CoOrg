<?php

class URLMenuEntryProvider implements IDataMenuEntryProvider
{
	public static function name()
	{
		return 'URL';
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
		return 'Home';
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
