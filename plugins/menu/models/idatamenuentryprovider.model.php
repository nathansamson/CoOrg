<?php

interface IDataMenuEntryProvider
{
	public static function name();
	public static function url($data, $language);
}

?>
