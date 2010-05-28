<?php

class DB
{
	private static $_pdo = null;

	public static function open($dsn, $username = null, $password = null)
	{
		self::$_pdo = new PDO($dsn, $username, $password);
		self::$_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public static function acceptTransactions()
	{
		return false;
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
	
	// Only use this function if you really need to...
	public static function pdo()
	{
		return self::$_pdo;
	}
}

?>
