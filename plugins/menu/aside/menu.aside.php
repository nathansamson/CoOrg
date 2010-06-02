<?php

class MenuMenuAside extends AsideController
{
	public function run($widgetParams, $request)
	{
		$menu = Menu::get($widgetParams['name']);
		if ($menu != null)
		{
			$this->widgetMenu = $menu;
			return $this->render('aside/menu');
		}
		else
		{
			return 'Menu not found';
		}
	}
}

?>
