<?php

class UserSessionTest extends CoOrgModelTest
{	
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/user.dataset.xml';
	}

	public function setUp()
	{
		parent::setUp();
		Session::destroy();
		
		$user = new User('Nathan', 'nathan@email.com');
		$user->password = 'nathan';
		$user->passwordConfirmation = 'nathan';
		$key = $user->save();
		$user->password = '';
		$user->passwordConfirmation = '';
		$user->unlock($key);
		$user->save();
		
		$session = new UserSession('a', 'b');
		$session->delete(); // Ugly trick to clear the user session cache
	}
	
	public function testNotLoggedIn()
	{
		$this->assertNull(UserSession::get());
		$this->assertNull(UserSession::get()); // See if it is not changed after testing
	}
	
	public function testLoginAndLogout()
	{
		$session = new UserSession('Nathan', 'nathan');
		$session->save();
		
		$this->assertNotNull(UserSession::get());
		$this->assertEquals('Nathan', UserSession::get()->username);
		$user = UserSession::get()->user();
		$this->assertEquals('Nathan', $user->username);
		$this->assertEquals('nathan@email.com', $user->email);
		
		$session->delete();
		
		$this->assertNull(UserSession::get());
	}
	
	public function testUsernameNotGiven()
	{
		$session = new UserSession('', 'password');
		
		try
		{
			$session->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Username is required', $session->username_error);
		}
	}
	
	public function testUsernameDoesNotExists()
	{
		$session = new UserSession('Not A User', 'password');
		
		try
		{
			$session->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Incorrect username', $session->username_error);
		}
	}
	
	public function testPasswordNotGiven()
	{
		$session = new UserSession('Nathan', '');
		
		try
		{
			$session->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Password is required', $session->password_error);
		}
	}
	
	public function testPasswordWrong()
	{
		$session = new UserSession('Nathan', 'OleOla');
		
		try
		{
			$session->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Incorrect password', $session->password_error);
		}
	}
	
	public function testUserLocked()
	{
		$user = new User;
		$user->username = 'someuser';
		$user->email = 'someuser@somemail.com';
		$user->password = 'thepass';
		$user->passwordConfirmation = 'thepass';
		$key = $user->save();
		
		$session = new UserSession('someuser', 'thepass');
		try
		{
			$session->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('User is not activated', 
			                    $session->username_error);
		}
	}
}

?>
