<?php

interface IHeader
{
	static function redirect($to);
	static function setErrorCode($code);
	static function setContentType($type);
}

?>
