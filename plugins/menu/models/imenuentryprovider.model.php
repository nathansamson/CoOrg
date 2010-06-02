<?php

interface IMenuEntryProvider
{
	public static function name();
	
	public static function listActions();
	public static function listData($action, $language);
	
	public static function url($action, $language, $data);
}

?>
