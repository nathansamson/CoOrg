<?php

class Header implements IHeader
{
	public static function setErrorCode($code)
	{
		header('HTTP/1.1 ' . $code);
	}

	public static function setContentType($ct)
	{
		header('Content-Type: ' . $ct);
	}
	
	public static function redirect($to)
	{
		$args = func_get_args();
		$full = CoOrg::createURL($args);
		header('Location: '.$full);
	}
}

?>
