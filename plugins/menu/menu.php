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

Menu::registerEntryProvider('URLMenuEntryProvider');

?>
