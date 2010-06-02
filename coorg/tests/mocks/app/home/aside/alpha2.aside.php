<?php

HomeAlpha2Aside extends AsideController
{
	public static $request = null;
	public static $p2 = null;
	public static $widgetParams = null;

	public function run($widgetParams, $request, $p1 = null, $p2 = null)
	{
		self::$widgetParams = $widgetParams;
		self::$p2 = $p2;
		self::$request = $request;
		$this->asideVar = 'WyZ';
		return $this->render('alpha');
	}
	
}

?>
