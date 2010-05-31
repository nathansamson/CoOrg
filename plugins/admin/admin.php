<?php
class ToSiteAdminModule extends AdminModule
{
	public function __construct()
	{
		$this->name = t('Visit Site');
		$this->url = CoOrg::createURL(array('home'));
		$this->priority = 5;
		$this->image = CoOrg::staticFile('images/home.png', 'admin');
	}
	
	public function isAllowed()
	{
		return true;
	}
}

Admin::registerModule('ToSiteAdminModule');

?>
