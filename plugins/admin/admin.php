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

class ToSiteAdminModule extends AdminModule
{
	public function __construct()
	{
		$this->name = t('Visit Site');
		$this->priority = 5;
		$this->image = CoOrg::staticFile('images/home.png', 'admin');
	}
	
	public function url($user)
	{
		return CoOrg::createURL('');
	}
	
	public function isAllowed()
	{
		return true;
	}
}
Admin::registerModule('ToSiteAdminModule');

class i18nAdminModule extends AdminModule
{
	public function __construct()
	{
		$this->name = t('Languages');
		$this->priority = 2;
		$this->image = CoOrg::staticFile('images/locale.png', 'admin');
	}
}

class i18nAdminTab
{
	public function __construct()
	{
		$this->url = CoOrg::createURL('admin/i18n');
		$this->name = t('Manage language');
		$this->priority = 1;
	}
	
	public function isAllowed($user)
	{
		return Acl::isAllowed($user->username, 'admin-language');
	}
}

Admin::registerModule('i18nAdminModule');
Admin::registerTab('i18nAdminTab', 'i18nAdminModule');

class LayoutAdminModule extends AdminModule
{
	public function __construct()
	{
		$this->name = t('Layout');
		$this->priority = 2;
		$this->image = CoOrg::staticFile('images/layout.png', 'admin');
	}
}

class LayoutAdminTab
{
	public function __construct()
	{
		$this->url = CoOrg::createURL('admin/layout');
		$this->name = t('Manage widgets');
		$this->priority = 1;
	}
	
	public function isAllowed($user)
	{
		return Acl::isAllowed($user->username, 'admin-layout');
	}
}

Admin::registerModule('LayoutAdminModule');
Admin::registerTab('LayoutAdminTab', 'LayoutAdminModule');

class SiteAdminModule extends AdminModule
{
	public function __construct()
	{
		$this->name = t('Site Configuration');
		$this->priority = 1;
		$this->image = CoOrg::staticFile('images/system.png', 'admin');
	}
}

class GeneralConfigurationAdminTab
{
	public function __construct()
	{
		$this->url = CoOrg::createURL('admin/system');
		$this->priority = 1;
		$this->name = t('General Configuration');
	}
	
	public function isAllowed($user)
	{
		return Acl::isAllowed($user->username, 'admin');
	}
}

Admin::registerModule('SiteAdminModule');
Admin::registerTab('GeneralConfigurationAdminTab', 'SiteAdminModule');

?>
