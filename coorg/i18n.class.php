<?php

class I18n
{
	private static $_searchDirs = array();
	private static $_strings = array();
	private static $_language = '';

	public static function addSearchDir($dir)
	{
		self::$_searchDirs[] = $dir;
	}

	public static function getLanguage()
	{
		return self::$_language;
	}

	public static function setLanguage($lang)
	{
		$_ = array();
		if ($lang != null) 
		{
			foreach (self::$_searchDirs as $dir)
			{
				if (file_exists($dir.'/'.$lang.'.lang.php'))
				{
					include $dir.'/'.$lang.'.lang.php';
				}
			}
		}
		
		self::$_language = $lang;
		self::$_strings = $_;
	}
	
	public static function translate($string, $params)
	{
		if (array_key_exists($string, self::$_strings))
		{
			$translated = self::$_strings[$string];
		}
		else
		{
			$translated = $string;
		}
		
		foreach ($params as $key =>$replacement)
		{
			$translated = str_replace('%'.$key, $replacement, $translated);
		}
		return $translated;
	}
}

function t($string, $params = array())
{
	return I18n::translate($string, $params);
}

?>
