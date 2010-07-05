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
 * @Acl allow admin-user
*/
class AdminUserController extends AdminBaseController
{
	protected $_adminModule = 'UserAdminModule';

	public function index($page = 1)
	{
		$this->_adminTab = 'UserAdminTab';
		$users = User::users();
		$this->users = $users->execute($page, 20);
		$this->userPager = $users;
		$this->render('index');
	}
	
	public function edit($username, $from = null)
	{
		$user = User::getUserByName($username);
		if ($user)
		{
			$this->user = $user;
			$this->from = $from;
			$this->render('edit');
		}
		else
		{
			$this->error(t('User not found'));
			$this->notfound();
		}
	}
	
	public function save($username, $email, $from,
	                     $password = null, $passwordConfirmation = null)
	{
		$user = User::getUserByName($username);
		$user->email = $email;
		
		if ($password)
		{
			$user->forceNewPassword = true;
			$user->password = $password;
			$user->passwordConfirmation = $passwordConfirmation;
		}
		
		try
		{
			$user->save();
			$this->notice('Updated user');
			$this->redirect($from);
		}
		catch (ValidationException $e)
		{	
			$this->user = $user;
			$this->from = $from;
			$this->error('Failed updating user');
			$this->render('edit');
		}
	}
	
	/**
	 * @post
	*/
	public function unlock($username, $from)
	{
		$user = User::getUserByName($username);
		$user->unlockForce();
		$user->save();
		
		$this->notice(t('Unlocked user'));
		$this->redirect($from);
	}
}
