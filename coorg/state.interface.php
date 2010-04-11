<?php


interface ICookies
{
	static function has($key);
	static function get($key);
	static function set($key, $value, $lifeTime = 0);
	static function delete($key);
}

interface ISession
{
	static function has($key);
	static function get($key);
	static function set($key, $value);
	static function delete($key);
	
	static function destroy();
}

?>
