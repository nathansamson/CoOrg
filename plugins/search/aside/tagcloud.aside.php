<?php

class SearchTagcloudAside extends AsideController
{
	public function run($widgetParams, $orient, $request)
	{
		$this->tagcloud = Taggable::cloud(15);
		return $this->render('widgets/tagcloud');
	}
	
	public function preview($widgetParams, $orient)
	{
		return $this->renderPreview('widgets/tagcloud-preview');
	}
}

?>
