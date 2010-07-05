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

/**
 * @Acl allow admin
*/
class AdminSystemController extends AdminBaseController
{
	protected $_adminModule = 'SiteAdminModule';
	protected $_adminTab = 'GeneralConfigurationAdminTab';

	public function index()
	{
		$this->config = new SiteConfig;
		$this->render('config');
	}
	
	public function update($title, $subtitle, $siteAuthor, $siteContactEmail,
	                       $friendlyURL, $UUID, $sitePath,
	                       $databaseConnection, $databaseUser, $databasePassword)
	{
		$config = new SiteConfig;
		$config->title = $title;
		$config->subtitle = $subtitle;
		$config->siteAuthor = $siteAuthor;
		$config->siteContactEmail = $siteContactEmail;
		$config->friendlyURL = $friendlyURL;
		$config->UUID = $UUID;
		$config->sitePath = $sitePath;
		$config->databaseConnection = $databaseConnection;
		$config->databaseUser = $databaseUser;
		$config->databasePassword;
		
		try
		{
			$config->save();
			
			$this->notice(t('Saved site configuration'));
			$this->redirect('admin/system');
		}
		catch (ValidationException $e)
		{
			$this->config = $config;
			$this->error(t('Failed saving configuration'));
			$this->render('config');
		}
	}
}
