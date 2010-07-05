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
 * @Acl allow admin-advanced
*/
class AdminMollomController extends AdminBaseController
{
	protected $_adminModule = 'SiteAdminModule';
	protected $_adminTab = 'MollomAdminTab';

	public function index()
	{
		$this->mollomConfig = MollomConfig::get();
		$this->render('admin/mollom');
	}
	
	public function save($publicKey, $privateKey)
	{
		$config = MollomConfig::get();
		$config->publicKey = $publicKey;
		$config->privateKey = $privateKey;
		
		try
		{
			$config->save();
			$this->notice(t('Mollom configuration saved'));
			$this->redirect('admin/mollom');
		}
		catch (ValidationException $e)
		{
			$this->error(t('Mollom configuration not saved'));
			$this->mollomConfig = $config;
			$this->render('admin/mollom');
		}
	}
}

?>
