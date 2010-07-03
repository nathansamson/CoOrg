<?php

class UserControllerTest extends CoOrgControllerTest
{
	const dataset = 'user.dataset.xml';

	public function setUp()
	{
		parent::setUp();
		$user = new User('Initial User', 'somemail@email.com');
		$user->password = 'pass';
		$user->passwordConfirmation = 'pass';
		$key = $user->save();
		$user->password = '';
		$user->passwordConfirmation = '';
		$user->unlock($key);
		$user->save();
	}

	public function testCreate()
	{
		$this->request('user/create');
		
		$this->assertVarSet('user');
		$this->assertRendered('create');
	}
	
	public function testSave()
	{
		$this->request('user/save', array('username' => 'New User',
		                                  'email' => 'myemail@email.com',
		                                  'password' => 'myPassword',
		                                  'passwordConfirmation' => 'myPassword'));

		$this->assertRedirected('');
		$this->assertFlashNotice('We have sent an email to confirm your registration');
		$this->assertMailSent('myemail@email.com', 'Complete your registration',
		                      'mails/registration',
		                      array('username' => 'New User',
		                            'activationURL' => '**?**',
		                            'site' => 'The Site'));
		$this->assertNotNull(UserProfile::get('New User'));
	}
	
	public function testSaveFailure()
	{
		$this->request('user/save', array('username' => 'New User',
		                                  'email' => 'myemail@email.com',
		                                  'password' => 'myPassword',
		                                  'passwordConfirmation' => 'myPassword2'));

		$this->assertRendered('create');
		$this->assertVarSet('user');
		$this->assertFlashError('We could not complete your registration');
	}
	
	public function testLogin()
	{
		$this->request('user/login');
		$this->assertVarSet('session');
		$this->assertRendered('login');
	}
	
	public function testExecuteLogin()
	{
		$this->request('user/executeLogin', array('username' => 'Initial User',
		                                          'password' => 'pass'));

		$this->assertFlashNotice('You are now logged in');
		$this->assertRedirected('');
		$this->assertNotNull(UserSession::get());
	}
	
	public function testExecuteLoginWithRedirect()
	{
		$this->request('user/executeLogin', array('username' => 'Initial User',
		                                          'password' => 'pass',
		                                          'redirect' => 'some/sort/of/url'));

		$this->assertFlashNotice('You are now logged in');
		$this->assertRedirected('some/sort/of/url');
		$this->assertNotNull(UserSession::get());
	}
	
	public function testExecuteLoginFailure()
	{
		$this->request('user/executeLogin', array('username' => 'No User',
		                                          'password' => 'pass'));

		$this->assertFlashError('You are not logged in');
		$this->assertVarSet('session');
		$this->assertRendered('login');
	}
	
	public function testExecuteLoginFailureRedirect()
	{
		$this->request('user/executeLogin', array('username' => 'No User',
		                                          'password' => 'pass',
		                                          'redirect' => 'some/sort/of/url'));

		$this->assertFlashError('You are not logged in');
		$this->assertVarSet('session');
		$this->assertVarSet('redirect');
		$this->assertVarIs('redirect', 'some/sort/of/url');
		$this->assertRendered('login');
	}
	
	public function testLogout()
	{
		$this->assertNull(UserSession::get());
		$session = new UserSession('Initial User', 'pass');
		$session->save();

		$this->assertNotNull(UserSession::get());
	
		$this->request('user/logout');
		$this->assertFlashNotice('You are now logged out');
		$this->assertRedirected('');
		
		$this->assertNull(UserSession::get());
	}
	
	public function testActivate()
	{
		$user = new User;
		$user->username = 'new user';
		$user->email = 'email@email.com';
		$user->password = 'email';
		$user->passwordConfirmation = 'email';
		$key = $user->save();

		$this->request('user/activate/new user/'.$key);
		$this->assertRedirected('user/login');
		$this->assertFlashNotice('Your account is now activated, you can login');
		
		$user = User::getUserByName('new user');
		$this->assertFalse($user->isLocked());
	}
	
	public function testActivateWrongKey()
	{
		$user = new User;
		$user->username = 'new user';
		$user->email = 'email@email.com';
		$user->password = 'email';
		$user->passwordConfirmation = 'email';
		$key = $user->save();

		$this->request('user/activate/new user/wrong-key');
		$this->assertRedirected('');
		$this->assertFlashError('Invalid activation key');
		
		$user = User::getUserByName('new user');
		$this->assertTrue($user->isLocked());
	}
	
	public function testActivateWronUser()
	{
		$this->request('user/activate/me-does-not-exists/wrong-key');
		$this->assertRedirected('');
		$this->assertFlashError('Invalid username');
	}
	
	public function testActivateActivatedUser()
	{
		$this->request('user/activate/azerty/wrong-key');
		$this->assertRedirected('');
		$this->assertFlashError('Invalid username');
	}
}

?>
