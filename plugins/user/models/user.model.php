<?php

/**
 * @primaryproperty username String(t('Username'), 24); required
 * @property email Email(t('Email')); required
 * @property firstName String(t('First Name'), 64);
 * @property lastName String(t('Last Name'), 64);
 * @shadowproperty password String(t('Password')); required only('insert')
 * @shadowproperty passwordConfirmation String(t('Password confirmation')); required only('insert')
 * @shadowproperty oldPassword String(t('Password')); required only('updatePassword')
 * @internalproperty passwordHash String('Password hash', 128); required
 * @internalproperty passwordHashKey String('Pasword hash key', 32); required
*/
class User extends DBModel
{
	public function __construct($username, $email)
	{
		parent::__construct();
		$this->username = $username;
		$this->email = $email;
	}
	
	public function checkPassword($password)
	{
		return $this->createHashedPassword($password) ==
		       $this->property('passwordHash')->get();
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
		
		if ($row = $q->fetch(PDO::FETCH_ASSOC))
		{
			$user = new User($row['username'], $row['email']);
			$user->property('firstName')->set($row['firstName']);
			$user->property('lastName')->set($row['lastName']);
			$user->property('passwordHash')->set($row['passwordHash']);
			$user->property('passwordHashKey')->set($row['passwordHashKey']);
			$user->setSaved();
			return $user;
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
				$this->username_error = '%n is already taken';
				$error = true;
			}
			
			if ($this->emailExists($this->email))
			{
				$this->email_error = '%n is already taken';
				$error = true;
			}
			if ($this->property('password')->get() != 
			    $this->property('passwordConfirmation')->get())
			{
				$this->passwordConfirmation_error = 'Passwords are not equal';
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
			if ($this->property('username')->changed() && 
			    $this->usernameExists($this->username))
			{
				$this->username_error = '%n is already taken';
				$error = true;
			}
			
			if ($this->property('email')->changed() && 
			    $this->emailExists($this->email))
			{
				$this->email_error = '%n is already taken';
				$error = true;
			}
			if ($this->property('password')->get() != null)
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
			if (!$this->checkPassword($this->property('oldPassword')->get()))
			{
				$this->oldPassword_error = 'Password is wrong';
				throw new ValidationException($this);
			}
			if ($this->property('password')->get() !=
			    $this->property('passwordConfirmation')->get())
			{
				$this->passwordConfirmation_error = 'Passwords do not match';
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
		if ($this->property('password')->get() != null)
		{
			$this->property('passwordHash')->set(
			   $this->createHashedPassword($this->property('password')->get()));
		}
		parent::update();
	}
	
	protected function beforeInsert()
	{
		$this->property('passwordHashKey')->set(
		                       md5(uniqid('azerty1234', true)) . 
		                       md5(uniqid('qwerty1989', true)));

		$this->property('passwordHash')->set(
		                          $this->createHashedPassword(
		                                   $this->property('password')->get()));

	}
	
	protected function insert()
	{
		parent::insert();
		$group = new UserGroup('__'.$this->username);
		$group->save();
		
		$group->add($this->username);
	}
	
	protected function createHashedPassword($password)
	{
		return hash('sha512', $this->property('passwordHashKey')->get().$password);
	}
}

?>
