<?php

class UserLoginAside extends AsideController
{
	public function run($widgetParams, $request)
	{
		$this->loginRequest = $request;
		return $this->render('aside/login');
	}
}

?>
