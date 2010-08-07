<?php

class SearchSearchAside extends AsideConfigurableController
{
	public function run($widgetParams, $orient, $request)
	{
		$this->includeSearch = $widgetParams['includes'];
		$this->includeSearch = array('Blog');
		return $this->render('widgets/search');
	}
	
	public function preview($widgetParams, $orient)
	{
		return $this->renderPreview('widgets/search-preview');
	}
	
	public function configure($widgetParams, $orient)
	{
	}
}

?>
