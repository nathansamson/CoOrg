<?php

class HomeController extends Controller
{
	public static $indexExecuted = false;
	
	public function index()
	{
		self::$indexExecuted = true;
		$this->language = t('en');
		$this->render('home');
	}
}

?>
