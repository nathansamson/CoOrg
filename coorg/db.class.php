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

class DB
{
	private static $_pdo = null;

	public static function open($dsn, $username = null, $password = null)
	{
		$driverName = strtolower(substr($dsn, 0, strpos($dsn, ':')));
		include_once 'coorg/pdo/generic.class.php';
		if ($driverName == 'mysql')
		{
			include_once 'coorg/pdo/mysql.class.php';
			$pdoClass = 'MySQLPDO';
		}
		else if ($driverName == 'sqlite')
		{
			include_once 'coorg/pdo/sqlite.class.php';
			$pdoClass = 'SQLitePDO';
		}
		else
		{
			$pdoClass = 'GenericPDO';
		}
		
		self::$_pdo = new $pdoClass($dsn, $username, $password);
		self::$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public static function acceptTransactions()
	{
		return true;
	}
	
	public static function prepare($sql)
	{
		try
		{
			return self::$_pdo->prepare($sql);
		}
		catch (PDOException $p)
		{
			var_dump($p);
			die($sql);
		}
	}
	
	public static function beginTransaction()
	{
		self::$_pdo->beginTransaction();
	}
	
	public static function commit()
	{
		self::$_pdo->commit();
	}
	
	public static function rollback()
	{
		self::$_pdo->rollback();
	}
	
	public static function lastInsertID($name)
	{
		return self::$_pdo->lastInsertId($name);
	}
	
	// Only use this function if you really need to...
	public static function pdo()
	{
		return self::$_pdo;
	}
}

?>
