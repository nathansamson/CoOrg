<?php

class UserModelTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/user.dataset.xml';
	}

	public function testCreateUser()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$nathan = User::getUserByName('Nathan');
		$this->assertNotNull($nathan);
		$this->assertTrue($nathan->checkPassword('azerty'));
		$this->assertFalse($nathan->checkPassword('qwerty'));
		
		$this->assertNull(User::getUserByName('nathan'));
	}
	
	public function testCreateCheckRequired()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Password is required', $user->password_error);
		}
		
		$user = new User('Nathan', '');
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Email is required', $user->email_error);
		}
		
		$user = new User('', 'email@email.com');
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Username is required', $user->username_error);
		}
	}
	
	public function testCreatePasswordConfirmation()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'qwerty';
		
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Passwords are not equal', $user->passwordConfirmation_error);
		}
	}
	
	public function testCreateUsernameUnique()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user = new User('nathan', 'nathan2@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Username is already taken', $user->username_error);
		}
	}
	
	public function testCreateEmailUnique()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user = new User('Some Other Guy', 'nathan@MAIL.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Email is already taken', $user->email_error);
		}
	}
	
	public function testUpdateRequired()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user->username = '';
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Username is required', $user->username_error);
		}
		$user->username = 'Google';
		$user->email = '';
		
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Email is required', $user->email_error);
		}
	}
	
	public function testUpdateUsernameUnique()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user = new User('Some Other Guy', 'nathan2@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user = User::getUserByName('Nathan');
		$user->username = 'Some other Guy';
		
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Username is already taken', $user->username_error);
		}
		
		$user = User::getUserByName('Nathan');
		$user->username = 'Nathan';
		$user->firstName = 'Nathan';
		$user->save();
		$this->assertNull($user->username_error); // Do not complain when username does not change
	}
	
	public function testUpdateEmailUnique()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user = new User('Some Other Guy', 'nathan2@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user = User::getUserByName('Nathan');
		$user->email = 'nathan2@MAIL.be';
		
		try
		{
			$user->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Email is already taken', $user->email_error);
		}
		
		$user = User::getUserByName('Nathan');
		$user->email = 'nathan@mail.be';
		$user->firstName = 'Nathan';
		$user->save();
		$this->assertNull($user->email_error); // Do not complain when email does not change
	}
	
	public function testUpdatePassword()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
		
		$user = User::getUserByName('Nathan');
		$user->password = 'qwerty';
		$user->passwordConfirmation = 'qwerty';
		$user->oldPassword = 'azerty';
		$user->save();
		
		$user = User::getUserByName('Nathan');
		$this->assertTrue($user->checkPassword('qwerty'));
		$this->assertFalse($user->checkPassword('azerty'));
	}
	
	public function testUpdatePasswordOldPasswordRequired()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
	
		$user = User::getUserByName('Nathan');
		$user->password = 'qwerty';
		$user->passwordConfirmation = 'qwerty';
		
		try
		{
			$user->save();	
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Password is required', $user->oldPassword_error);
		}
		
		$user->oldPassword_error = null;
		$user->oldPassword = 'sdsdsd'; // WRONG!
		try
		{
			$user->save();	
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Password is wrong', $user->oldPassword_error);
		}
	}
	
	public function testUpdatePasswordPasswordConfirmation()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
	
		$user = User::getUserByName('Nathan');
		$user->oldPassword = 'azerty';
		$user->password = 'qwerty';
		$user->passwordConfirmation = 'qwerty2';
		
		try
		{
			$user->save();	
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Passwords do not match', $user->passwordConfirmation_error);
		}
	}

}

?>