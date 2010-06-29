<?php

class UserModelTest extends CoOrgModelTest
{
	const dataset = 'user.dataset.xml';

	public function testCreateUser()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$key = $user->save();
		
		$this->assertNotNull($key);
		$nathan = User::getUserByName('Nathan');
		$this->assertTrue($nathan->isLocked());
		$this->assertNotNull($nathan);
		$this->assertTrue($nathan->checkPassword('azerty'));
		$this->assertFalse($nathan->checkPassword('qwerty'));
		
		$this->assertNull(User::getUserByName('nathan'));
		
		$this->assertFalse($nathan->unlock('wrongkey'));
		$this->assertTrue($nathan->isLocked());
		
		$this->assertTrue($nathan->unlock($key));
		$this->assertFalse($nathan->isLocked());
		$nathan->save();
		
		$nathan = User::getUserByName('Nathan');
		$this->assertFalse($nathan->isLocked());
	}
	
	public function testUnlockForce()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$key = $user->save();
		
		$user = User::getUserByName('Nathan');
		$user->unlockForce();
		$this->assertFalse($user->isLocked());
		
		$nathan = User::getUserByName('Nathan');
		$this->assertTrue($nathan->isLocked());
		
		$user->save();
		
		$nathan = User::getUserByName('Nathan');
		$this->assertFalse($nathan->isLocked());
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
	
	public function testForceUpdatePassword()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
	
		$user->forceNewPassword = true; // useful for admins
		$user->password = 'qwerty';
		$user->passwordConfirmation = 'qwerty';
		$user->save();
		
		$user = User::getUserByName('Nathan');
		$this->assertTrue($user->checkPassword('qwerty'));
	}
	
	public function testForceUpdatePasswordWrongConfirmation()
	{
		$user = new User('Nathan', 'nathan@mail.be');
		$user->password = 'azerty';
		$user->passwordConfirmation = 'azerty';
		$user->save();
	
		$user->forceNewPassword = true; // useful for admins
		$user->password = 'qwerty';
		$user->passwordConfirmation = 'wrong...';
		
		try
		{
			$user->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Passwords do not match', $user->passwordConfirmation_error);
		}
	}
	
	public function testUsers()
	{
		$userPager = User::users();
		$users = $userPager->execute(1, 3);
		$this->assertEquals('azerty', $users[0]->username);
		$this->assertEquals('dvorak', $users[1]->username);
		$this->assertEquals('qwerty', $users[2]->username);
	}
}

?>
