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

class UserPasswordController extends Controller
{
	public function reset()
	{
		$this->resetPassword = new ResetPassword;
		$this->resetCaptcha = MollomCaptcha::create();
		$this->render('resetpassword');
	}
	
	/**
	 * @post
	*/
	public function confirmreset($username, $email, $response,
	                             $image = null, $audio = null, $refresh = null)
	{
		$newCaptcha = false;
		if (!($image || $audio || $refresh))
		{
			$error = '';
			if ($username != null)
			{
				$user = User::getUserByName($username);
				if (! $user) $error = t('Username not found');
			}
			else if ($email != null)
			{
				$user = User::getUserByEmail($email);
				if (! $user) $error = t('Email not found');
			}
			else
			{
				$error = t('You have to give your username or email');
			}
			if (! $error)
			{
				$captcha = MollomCaptcha::check($response);
				if ($captcha)
				{
					$error = t('Resetting your password failed');
				}
			}
			else
			{
				$captcha = MollomCaptcha::refresh();
			}
		}
		else
		{
			$error = false;
			if ($image)
			{
				$captcha = MollomCaptcha::refresh('image');
			}
			else if ($audio)
			{
				$captcha = MollomCaptcha::refresh('audio');
			}
			else
			{
				$captcha = MollomCaptcha::refresh();
			}
			$newCaptcha = true;
		}
		// Still all ok
		if (!$newCaptcha && ! $error)
		{
			// prepare reset;
			$mail = $this->mail();
			$mail->username = $user->username;
			$site = CoOrg::config()->get('site/title');
			$key = $user->resetPassword();
			$user->save();
			$mail->site = $site;
			$mail->renewURL = CoOrg::createFullURL(array('user/password/renew', $user->username, $key));
			$mail->to($user->email)
			     ->subject(t('%site: Your account information', array('site' => $site)))
			     ->send('mails/passwordreset');
			$this->notice(t('A mail has been sent to you. Please follow the directions to set a new password for your account.'));
			$this->redirect('/');
		}
		else
		{
			$reset = new ResetPassword;
			$reset->username = $username;
			$reset->email = $email;
			$this->resetPassword = $reset;
			$this->resetCaptcha = $captcha;
			if ($error)
			{
				$this->error($error);
			}
			$this->render('resetpassword');
		}
	}
	
	public function renew($username, $resetKey)
	{
		$user = User::getUserByName($username);
		$password = $user->generateNewPassword($resetKey);
		if ($password)
		{
			$user->save();
			
			$site = CoOrg::config()->get('site/title');
			$mail = $this->mail();
			$mail->username = $user->username;
			$mail->newpassword = $password;
			$mail->loginURL = CoOrg::createFullURL(array('user/login'));
			$mail->site = $site;
			$mail->to($user->email)
			     ->subject(t('%site: Your new password', array('site' => $site)))
			     ->send('mails/passwordrenew');
			$this->notice('A mail has been sent to you, containing your new password');
			$this->redirect('/');
		}
		else
		{
			$this->error(t('Invalid key'));
			$this->redirect('/');
		}
	}
}

?>
