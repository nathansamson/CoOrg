<?php

include_once 'lib/smarty/Smarty.class.php';

class Header {
	public static function setErrorCode($code)
	{
		header('HTTP/1.1 ' . $code);
	}

	public static function setContentType($ct)
	{
		header('Content-Type: ' . $ct);
	}
}

class CoOrgSmarty extends Smarty {}

?>
