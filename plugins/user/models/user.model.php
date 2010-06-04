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
 * @property protected; passwordHash String('Password hash', 128); required
 * @property protected; passwordHashKey String('Pasword hash key', 64); required
 * @property protected; lockKey String('Reigstration lock key', 64); required only('insert')
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
			if ($this->password != null)
			{
				$this->validate('updatePassword');
			}
			if ($error)
			{
				throw new ValidationException($this);
			}
		}
		else if ($for == 'updatePassword')
		{
			if (!$this->checkPassword($this->oldPassword))
			{
				$this->oldPassword_error = t('Password is wrong');
				throw new ValidationException($this);
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
		$this->lockKey = md5(uniqid('#kadro23ela', true)) . 
		                 md5(uniqid('as90pa#zan', true));

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
}

?>
