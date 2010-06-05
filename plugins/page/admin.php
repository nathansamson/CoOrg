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

class PageAdminModule
{
	public function __construct()
	{
		$this->name = t('Content');
		$this->url = CoOrg::createURL(array('admin', 'page'));
		$this->image = CoOrg::staticFile('images/page.png', 'page');
		$this->priority = 1;
	}
	
	public function isAllowed($user)
	{
		return Acl::isAllowed($user->username, 'admin-page-edit');
	}
}

Admin::registerModule('PageAdminModule');

?>