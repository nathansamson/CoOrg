<?php

class HomeController extends Controller
{
	public static $indexExecuted = false;
	
	public function index()
	{
		self::$indexExecuted = true;
	}
}

?>
