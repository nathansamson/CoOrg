<?php

class SearchSearchAside extends AsideConfigurableController
{
	public function run($widgetParams, $orient, $request)
	{
		$this->includeSearch = $widgetParams['includes'];
		return $this->render('widgets/search');
	}
	
	public function preview($widgetParams, $orient)
	{
		return $this->renderPreview('widgets/search-preview');
	}
	
	public function configure($widgetParams, $orient)
	{
		$this->includeSearch = $widgetParams['includes'] ? $widgetParams['includes'] : array();
		$this->allIncludes = Searchable::searches();
		return $this->renderConfigure('widgets/search-configure');
	}
}

?>
