<?php
/*
 * Copyright 2010 Nathan Samson <nathansamson at gmail dot com>
 *
 * This file is part of CoOrg.
 *
 * CoOrg is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

  * CoOrg is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with CoOrg.  If not, see <http://www.gnu.org/licenses/>.
*/

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

abstract class AdminBaseController extends Controller
{
	protected $_adminModule;
	protected $_adminTab;

	public function render($tpl, $app = false, $base = 'base')
	{
		if ($base == 'base' && $app == false)
		{
			CoOrg::loadPluginInfo('admin');
			$this->_adminTabs = Admin::tabs($this->_adminModule, $this->_adminTab);

			parent::render($tpl, false, 'base.html.tpl|'.Controller::getTemplatePath('admin.html.tpl', 'admin'));
		}
		else
		{
			parent::render($tpl, $app, $base);
		}
	}
}

?>
