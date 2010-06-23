<?php

class UserLoginAside extends AsideController
{
	public function run($widgetParams, $request)
	{
		return $this->render('aside/login');
	}
	
	public function preview($widgetParams)
	{
		return $this->renderPreview('aside/login-preview');
	}
}

?>
