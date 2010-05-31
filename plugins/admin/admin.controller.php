<?php

class AdminController extends Controller
{
	/**
	 * @Acl allow admin
	*/
	public function index()
	{
		$this->modules = Admin::modules();
		$this->render('index');
	}
}

?>
