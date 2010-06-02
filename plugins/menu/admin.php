<?php

class MenuAdminModule
{
	public function __construct()
	{
		$this->name = 'Menu';
		$this->url = CoOrg::createURL(array('admin', 'menu'));
		$this->image = CoOrg::staticFile('images/menu.png', 'menu');
		$this->priority = 1;
	}
	
	public function isAllowed($user)
	{
		return Acl::isAllowed($user->username, 'admin-menu-edit');
	}
}

Admin::registerModule('MenuAdminModule');

?>
