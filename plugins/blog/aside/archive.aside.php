<?php

class BlogArchiveAside extends AsideController
{
	public function run($widgetParams, $request)
	{
		$this->blogArchive = Blog::getArchives(CoOrg::getLanguage());
		return $this->render('aside/archive');
	}
	
	public function preview($widgetParams)
	{
		return $this->renderPreview('aside/archive-preview');
	}
}

?>
