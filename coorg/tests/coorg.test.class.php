<?php

include_once 'lib/smarty/Smarty.class.php';

class Header {
	public static $contentType;
	public static $errorCode;

	public static function setErrorCode($code)
	{
		self::$errorCode = $code;
	}

	public static function setContentType($ct)
	{
		self::$contentType = $ct;
	}
}

class CoOrgSmarty extends Smarty
{
	public static $vars = array();
	public static $renderedOutput;
	public static $renderedTemplate;

	public function assign($key, $value)
	{
		self::$vars[$key] = $value;
		parent::assign($key, $value);
	}
	
	public function display($tpl)
	{
		self::$renderedTemplate = $tpl;
		self::$renderedOutput = parent::fetch($tpl);
	}
}


?>
