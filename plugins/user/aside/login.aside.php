<?php

class UserLoginAside extends AsideController
{
	public function run($request)
	{
		$this->loginRequest = $request;
		return $this->render('aside/login');
	}
}

?>
