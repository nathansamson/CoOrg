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
 * @property username String('Username', 64); required
 * @property password String('Password'); required
*/
class UserSession extends Model
{
	private $_user = null;

	private static $_currentSession = false;

	public function __construct($userID, $password)
	{
		parent::__construct();
		$this->username = $userID;
		$this->password = $password;
	}
	
	public function save()
	{
		$this->validate('insert');
		Session::set('userID', $this->username);
		self::$_currentSession = false;
	}
	
	public function delete()
	{
		Session::delete('userID');
		self::$_currentSession = false;
	}
	
	public function user()
	{
		if ($this->_user == null)
		{
			return User::getUserByName($this->username);
		}
		return $this->_user;
	}

	public static function get()
	{
		if (self::$_currentSession === false)
		{
			if (Session::has('userID'))
			{
				self::$_currentSession = new UserSession(Session::get('userID'), '');
			}
			else
			{
				self::$_currentSession = null;
			}
		}
		return self::$_currentSession;
	}
	
	protected function validate($t)
	{
		parent::validate($t);
		
		$user = $this->user();
		if ($user == null)
		{
			$this->username_error = 'Incorrect username';
			throw new ValidationException($this);
		}
		if (!$user->checkPassword($this->password))
		{
			$this->password_error = 'Incorrect password';
			throw new ValidationException($this);
		}
	}
}

?>
