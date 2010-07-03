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
 * @property primary; username String(t('Username'), 24); required
 * @property email Email(t('Email')); required
 * @property firstName String(t('First Name'), 64);
 * @property lastName String(t('Last Name'), 64);
 * @property writeonly; password String(t('Password')); required only('insert')
 * @property writeonly; passwordConfirmation String(t('Password confirmation')); required only('insert')
 * @property writeonly; oldPassword String(t('Password')); required only('updatePassword')
 * @property writeonly; forceNewPassword Bool(''); required only('forceNewPassword')
 * @property protected; passwordHash String('Password hash', 128); required
 * @property protected; passwordHashKey String('Pasword hash key', 64); required
 * @property protected; lockKey String('Reigstration lock key', 64); required only('insert')
 * @property protected; resetPasswordKey String('Password reset key', 64);
*/
class User extends DBModel
{
	public function __construct($username = null, $email = null)
	{
		parent::__construct();
		$this->username = $username;
		$this->email = $email;
	}
	
	public function isLocked()
	{
		return $this->lockKey != null;
	}
	
	public function unlockForce()
	{
		$this->lockKey = null;
		// Do not automatically save here because it will change other keys to
	}
	
	public function unlock($key)
	{
		if ($this->lockKey == $key)
		{
			$this->lockKey = null;
			// Do not automatically save here because it will change other keys to
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function resetPassword()
	{
		$this->resetPasswordKey = $this->randomkey();
		return $this->resetPasswordKey;
	}
	
	public function generateNewPassword($key)
	{
		if ($this->resetPasswordKey == $key)
		{
			$this->resetPasswordKey == '';
			$newPass = $this->generatePassword();
			$this->forceNewPassword = true;
			$this->password = $newPass;
			$this->passwordConfirmation = $newPass;
			return $newPass;
		}
		else
		{
			return null;
		}
	}
	
	public function checkPassword($password)
	{
		return $this->createHashedPassword($password) ==
		       $this->passwordHash;
	}
	
	public function groups()
	{
		return UserGroupMember::getGroupsWithUser($this->username);
	}
	
	public function grant($key)
	{
		$acl = Acl::set('__'.$this->username, $key, true);
	}
	
	public function revoke($key)
	{
		$acl = Acl::set('__'.$this->username, $key, false);
	}
	
	public static function getUserByName($username)
	{
		$q = DB::prepare('SELECT * FROM User WHERE username=:username');
		$q->execute(array(':username' => $username));
		
		if ($row = $q->fetch())
		{
			return self::fetch($row, 'User');
		}
		else
		{
			return null;
		}
	}
	
	public static function getUserByEmail($email)
	{
		$q = DB::prepare('SELECT * FROM User WHERE email=:email');
		$q->execute(array(':email' => $email));
		
		if ($row = $q->fetch())
		{
			return self::fetch($row, 'User');
		}
		else
		{
			return null;
		}
	}
	
	public static function get($user)
	{
		return self::getUserByName($user);
	}
	
	public static function users()
	{
		return new UserPager('SELECT * FROM User ORDER BY username');
	}
	
	protected function validate($for)
	{
		parent::validate($for);
		
		if ($for == 'insert')
		{
			$error = false;
			if ($this->usernameExists($this->username))
			{
				$this->username_error = t('%n is already taken');
				$error = true;
			}
			
			if ($this->emailExists($this->email))
			{
				$this->email_error = t('%n is already taken');
				$error = true;
			}
			if ($this->password != $this->passwordConfirmation)
			{
				$this->passwordConfirmation_error = t('Passwords are not equal');
				$error = true;
			}
			if ($error)
			{
				throw new ValidationException($this);
			}
		}
		else if ($for == 'update')
		{
			$error = false;
			if ($this->username_changed && 
			    $this->usernameExists($this->username))
			{
				$this->username_error = t('%n is already taken');
				$error = true;
			}
			
			if ($this->email_changed && 
			    $this->emailExists($this->email))
			{
				$this->email_error = t('%n is already taken');
				$error = true;
			}
			if ($this->password != null && !$this->forceNewPassword)
			{
				$this->validate('updatePassword');
			}
			else if ($this->password != null)
			{
				$this->validate('forceNewPassword');
			}
			if ($error)
			{
				throw new ValidationException($this);
			}
		}
		else if ($for == 'updatePassword' || $for == 'forceNewPassword')
		{
			if ($for == 'updatePassword')
			{
				if (!$this->checkPassword($this->oldPassword))
				{
					$this->oldPassword_error = t('Password is wrong');
					throw new ValidationException($this);
				}
			}
			if ($this->password != $this->passwordConfirmation)
			{
				$this->passwordConfirmation_error = t('Passwords do not match');
				throw new ValidationException($this);
			}
		}
	}
	
	protected function usernameExists($username)
	{
		$q = DB::prepare('SELECT username FROM User WHERE LOWER(username)=LOWER(:username)');
		$q->execute(array(':username' => $username));
		
		return ($q->fetch() != false);
	}
	
	protected function emailExists($email)
	{
		$q = DB::prepare('SELECT email FROM User WHERE LOWER(email)=LOWER(:email)');
		$q->execute(array(':email' => $email));
		
		return ($q->fetch() != false);
	}
	
	protected function update()
	{
		if ($this->password != null)
		{
			$this->passwordHash =
			   $this->createHashedPassword($this->password);
		}
		parent::update();
	}
	
	protected function beforeInsert()
	{
		$this->passwordHashKey = md5(uniqid('azerty1234', true)) . 
		                         md5(uniqid('qwerty1989', true));

		$this->passwordHash = $this->createHashedPassword($this->password);
		$this->lockKey = $this->randomkey();

	}
	
	protected function insert()
	{
		parent::insert();
		$group = new UserGroup('__'.$this->username);
		$group->save();
		
		$group->add($this->username);
		return $this->lockKey;
	}
	
	protected function createHashedPassword($password)
	{
		return hash('sha512', $this->passwordHashKey.$password);
	}
	
	protected function randomkey()
	{
		return md5(uniqid('#kadro23ela', true)) . 
		       md5(uniqid('as90pa#zan', true));
	}
	
	protected function generatePassword()
	{
		$s = '';
		while (strlen($s) <= 12)
		{
			$o = mt_rand(32, 127);
			$c = chr($o);
			$allowed = array('@', '#', '&', '!', '%', '*', '$', '?');
			if (($o >= ord('a') && $o <= ord('z')) ||
			    ($o >= ord('A') && $o <= ord('Z')) ||
			    ($o >= ord('0') && $o <= ord('9')) ||
			    in_array($c, $allowed))
			{
				$s .= $c;
			}
		}
		return $s;
	}
}

?>
