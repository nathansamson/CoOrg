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
	public static $referrer = '';
	public static $site =  '';

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
	
	public static function IP()
	{
		return '0.0.0.0';
	}
	
	public static function getReferrer()
	{
		return self::$referrer;
	}
	
	public static function getSite()
	{
		return self::$site;
	}
}

?>
