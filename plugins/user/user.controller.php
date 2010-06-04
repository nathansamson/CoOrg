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

class UserController extends Controller
{
	public function create()
	{
		$this->user = new User('', '');	
		$this->render('create');
	}
	
	/**
	 * @post
	*/
	public function save($username, $email, $password, $passwordConfirmation)
	{
		$user = new User($username, $email);
		$user->password = $password;
		$user->passwordConfirmation = $passwordConfirmation;
		
		try
		{
			$key = $user->save();
			$mail = $this->mail();
			$mail->username = $username;
			$mail->activationURL = CoOrg::createFullURL(array('user/activate', $username, $key));
			$mail->site = CoOrg::config()->get('site/title');
			$mail->to($email)->subject('Complete your registration')
			     ->send('mails/registration');
			$this->notice(t('We have sent an email to confirm your registration'));
			$this->redirect('/');
		}
		catch (ValidationException $e)
		{
			$this->error(t('We could not complete your registration'));
			$this->user = $user;
			$this->render('create');
		}
	}
	
	public function login()
	{
		$this->session = new UserSession('', '');
		$this->render('login');
	}
	
	/**
	 * @post
	*/
	public function executeLogin($username, $password, $redirect = '/')
	{
		$session = new UserSession($username, $password);
		
		try
		{
			$session->save();
			$this->notice(t('You are now logged in'));
			$this->redirect($redirect);
		}
		catch (ValidationException $e)
		{
			$this->error(t('You are not logged in'));
			$this->redirect = $redirect;
			$this->session = $session;
			$this->render('login');
		}
	}
	
	public function logout()
	{
		$session = UserSession::get();
		if ($session != null)
		{
			$session->delete();
		}
		
		$this->notice(t('You are now logged out'));
		$this->redirect('/');
	}
	
	public function activate($userID, $key)
	{
		$user = User::getUserByName($userID);
		if ($user && $user->isLocked())
		{
			if ($user->unlock($key))
			{
				$user->save();
				$this->notice('Your account is now activated, you can login');
				$this->redirect('user/login');
			}
			else
			{
				$this->error('Invalid activation key');
				$this->redirect('/');
			}
		}
		else
		{
			$this->error('Invalid username');
			$this->redirect('/');
		}
	}
}

?>
