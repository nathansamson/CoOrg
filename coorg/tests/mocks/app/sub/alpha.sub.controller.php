<?php

class AlphaSubController extends Controller
{
	public static $indexExecuted = true;

	public static $actionExecuted = false;
	public static $actionParams = array();
	
	public function index()
	{
		self::$indexExecuted = true;
	}
	
	public function action($p1, $p2)
	{
		self::$actionExecuted = true;
		self::$actionParams = array($p1, $p2);
	}
}

?>
