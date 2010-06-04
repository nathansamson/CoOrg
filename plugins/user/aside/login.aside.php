<?php

class UserLoginAside extends AsideController
{
	public function run($widgetParams, $request)
	{
		return $this->render('aside/login');
	}
}

?>
