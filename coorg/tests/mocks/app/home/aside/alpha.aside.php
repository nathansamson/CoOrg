<?php

class HomeAlphaAside extends AsideController
{
	public static $request = null;
	public static $p2 = null;
	public static $widgetParams = null;

	public function run($widgetParams, $request, $p1 = null, $p2 = null)
	{
		self::$p2 = $p2;
		self::$request = $request;
		self::$widgetParams = $widgetParams;
		$this->asideVar = 'WyZ';
		
		if ($p1 == 'triggerSomethingBad')
		{
			$this->myActionVar = 'lets rock\'n roll';
		}
		
		return $this->render('alpha');
	}
	
	public function preview($widgetParams)
	{
	}
}

?>
