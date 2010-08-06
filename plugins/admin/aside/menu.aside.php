<?php

class FakeAdminModule
{
	public function __construct()
	{
		$this->name = t('Admin');
	}

	function url()
	{
		return CoOrg::createURL('admin');
	}
}

class AdminMenuAside extends AsideController
{
	public function run($widgetParams, $orient, $request)
	{
		if (UserSession::get() &&
		    Acl::isAllowed(UserSession::get()->username, 'admin'))
		{
			if (substr($request, 0, strpos($request, '/')) == 'admin')
			{
				$this->menu = Admin::modules();
			}
			else
			{
				$this->menu = array(new FakeAdminModule);
			}
			return $this->render('widgets/admin-menu');
		}
	}
	
	public function preview($widgetParams, $orient)
	{
		return $this->renderPreview('widgets/admin-menu-preview');
	}
}

?>
