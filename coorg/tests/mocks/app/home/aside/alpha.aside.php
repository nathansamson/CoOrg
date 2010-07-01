<?php

class HomeAlphaAside extends AsideController
{
	public static $request = null;
	public static $p2 = null;
	public static $widgetParams = null;
	public static $orient = null;

	public function run($widgetParams, $orient, $request, $p1 = null, $p2 = null)
	{
		self::$p2 = $p2;
		self::$request = $request;
		self::$widgetParams = $widgetParams;
		$this->asideVar = 'WyZ';
		self::$orient = $orient;
		
		if ($p1 == 'triggerSomethingBad')
		{
			$this->myActionVar = 'lets rock\'n roll';
		}
		
		if ($p2 != 'fallback')
		{
			return $this->render('alpha');
		}
		else
		{
			return $this->render('asidefallback');
		}
	}
	
	public function preview($widgetParams, $orient)
	{
	}
}

?>
