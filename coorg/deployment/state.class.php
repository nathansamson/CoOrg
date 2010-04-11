<?php

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
	
	private static function start()
	{
		if (!self::$_started)
		{
			session_start();
			
			if (array_key_exists('__IP', $_SESSION))
			{
				if ($_SESSION['__IP'] != $_SERVER['REMOTE_ADDR'])
				{
					session_destroy();
				}
			}
			else
			{
				$_SESSION['__IP'] = $_SERVER['REMOTE_ADDR'];
			}
			
			self::$_started = true;
		}
	}
}

?>
