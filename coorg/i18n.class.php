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

class I18n
{
	private static $_searchDirs = array();
	private static $_strings = array();
	private static $_contexts = array();
	private static $_language = '';

	public static function addSearchDir($dir, $context)
	{
		self::$_searchDirs[$context] = $dir;
	}

	public static function getLanguage()
	{
		return self::$_language;
	}

	public static function setLanguage($lang)
	{
		self::$_strings = array();
		self::$_contexts = array();
		if ($lang != null) 
		{
			foreach (self::$_searchDirs as $context => $dir)
			{
				if (file_exists($dir.'/'.$lang.'.lang.php'))
				{
					$_ = array();
					include $dir.'/'.$lang.'.lang.php';
					self::$_strings = array_merge(self::$_strings, $_);
					self::$_contexts[$context] = $_;
				}
			}
		}
		
		self::$_language = $lang;
	}
	
	public static function translate($string, $params)
	{
		if (preg_match('/^([a-zA-Z0-9]*)\|(.*)/', $string, $matches))
		{
			$context = $matches[1];
			if (array_key_exists($context, self::$_contexts))
			{
				$haystack = self::$_contexts[$context];
			}
			else
			{
				$haystack = array();
			}
			$string = $matches[2];
		}
		else
		{
			$haystack = self::$_strings;
		}
		if (array_key_exists($string, $haystack))
		{
			$translated = $haystack[$string];
			if ($translated == null)
			{
				$translated = $string;
			}
		}
		else
		{
			$translated = $string;
		}
		
		$translated = preg_replace('/@(%?[a-zA-Z0-9]*)\:(%?[^@]*)@/', '<a href="$1">$2</a>', $translated);
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
