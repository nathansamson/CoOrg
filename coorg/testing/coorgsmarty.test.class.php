<?php

include_once 'lib/smarty/Smarty.class.php';

class CoOrgSmarty extends Smarty implements ICoOrgSmarty
{
	public static $vars = array();
	public static $renderedOutput;
	public static $renderedTemplate;
	public static $notices = array();
	public static $errors = array();

	public static function clearAll()
	{
		self::$renderedOutput = null;
		self::$vars = array();
		self::$notices = array();
		self::$errors = array();
		self::$renderedTemplate = null;
	}

	public function notice($notice)
	{
		self::$notices[] = $notice;
	}
	
	public function error($error)
	{
		self::$errors[] = $error;
	}

	public function assign($key, $value)
	{
		self::$vars[$key] = $value;
		parent::assign($key, $value);
	}
	
	public function clearAssign($key)
	{
		unset(self::$vars[$key]);
		parent::clearAssign($key);
	}
	
	public function display($tpl)
	{
		self::$renderedOutput = parent::fetch($tpl);
		self::$renderedTemplate = $tpl;
	}
	
	public function saveState()
	{
	}
}


?>
