<?php

/**
 * @property username String('Username', 64); required
 * @shadowproperty password String('Password'); required
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
		if (!$user->checkPassword($this->property('password')->get()))
		{
			$this->password_error = 'Incorrect password';
			throw new ValidationException($this);
		}
	}
}

?>
