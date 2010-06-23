<?php

class UserUsernavAside extends AsideController
{
	public function run($widgetParams, $request)
	{
		return $this->render('aside/usernav');
	}
	
	public function preview($widgetParams)
	{
		return $this->renderPreview('aside/usernav-preview');
	}
}

?>
