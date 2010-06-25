<?php

class UserLoginAside extends AsideController
{
	public function run($widgetParams, $request)
	{
		return $this->render('aside/login');
	}
	
	public function preview($widgetParams)
	{
		if ($widgetParams === null)
		{
			$this->ID = 'asideMockPreview';
		}
		else
		{
			$this->ID = 'asidePreview';
		}
		return $this->renderPreview('aside/login-preview');
	}
}

?>
