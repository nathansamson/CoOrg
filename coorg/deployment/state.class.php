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

class Cookies implements ICookies
{

	public static function has($key)
	{
	}
	
	public static function get($key)
	{
	}
	
	public static function set($key, $value, $lifeTime = 0)
	{
	}
	
	public static function delete($key)
	{
	}
}

class Session implements ISession
{
	private static $_keys = array();
	
	private static $_started = false;

	public static function has($key)
	{
		self::start();
		return array_key_exists($key, $_SESSION);
	}
	
	public static function get($key)
	{
		self::start();
		return $_SESSION[$key];
	}
	
	public static function set($key, $value)
	{
		self::start();
		$_SESSION[$key] = $value;
	}
	
	public static function delete($key)
	{
		self::start();
		unset($_SESSION[$key]);
	}
	
	public static function destroy()
	{
		self::start();
		session_destroy();
	}
	
	public static function IP()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
	
	private static function start()
	{
		if (!self::$_started)
		{
			session_start();
			
			if (array_key_exists('__IP', $_SESSION))
			{
				if ($_SESSION['__IP'] != self::IP())
				{
					session_destroy();
				}
			}
			else
			{
				$_SESSION['__IP'] = self::IP();
			}
			
			self::$_started = true;
		}
	}
	
	public static function getReferrer()
	{
		if (array_key_exists('HTTP_REFERER', $_SERVER))
		{
			return $_SERVER['HTTP_REFERER'];
		}
		else
		{
			return '';
		}
	}
	
	public static function getSite()
	{
		return 'http://'.$_SERVER['HTTP_HOST'];
	}
}

?>
