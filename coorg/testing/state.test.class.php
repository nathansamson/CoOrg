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

	public static function has($key)
	{
		return array_key_exists($key, self::$_keys);
	}
	
	public static function get($key)
	{
		return self::$_keys[$key];
	}
	
	public static function set($key, $value)
	{
		self::$_keys[$key] = $value;
	}
	
	public static function delete($key)
	{
		unset(self::$_keys[$key]);
	}
	
	public static function destroy()
	{
		self::$_keys = array();
	}
}

?>
