<?php

class MenuMenuAside extends AsideConfigurableController
{
	public function run($widgetParams, $orient, $request)
	{
		$menu = Menu::get($widgetParams['menu']);
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
	
	public function preview($widgetParams, $orient)
	{
		return $this->renderPreview('aside/menu-preview');
	}
	
	public function configure($widgetParams, $orient)
	{
		$this->menu = $widgetParams['menu'];
		$menus = Menu::all();
		$textMenus = array();
		foreach ($menus as $menu)
		{
			$textMenus[$menu->name] = $menu->name;
		}
		$this->menus = $textMenus;
		return $this->renderConfigure('aside/menu-configure');
	}
}

?>
