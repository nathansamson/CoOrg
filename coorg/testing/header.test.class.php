<?php

class Header implements IHeader
{
	public static $contentType;
	public static $errorCode;
	public static $redirect;

	public static function redirect($to)
	{
		self::$redirect = $to;
	}
	
	public static function setErrorCode($code)
	{
		self::$errorCode = $code;
	}

	public static function setContentType($ct)
	{
		self::$contentType = $ct;
	}
}

?>
