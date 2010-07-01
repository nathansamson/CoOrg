<?php

class UserLoginAside extends AsideController
{
	public function run($widgetParams, $orient, $request)
	{
		if ($orient == CoOrg::PANEL_ORIENT_VERTICAL)
		{
			return $this->render('aside/login');
		}
		else
		{
			return $this->render('aside/usernav');
		}
	}
	
	public function preview($widgetParams, $orient)
	{
		if ($orient == CoOrg::PANEL_ORIENT_VERTICAL)
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
		else
		{
			return $this->renderPreview('aside/usernav-preview');
		}
	}
}

?>
