<?php

class UserControllerTest extends CoOrgControllerTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/user.dataset.xml';
	}

	public function setUp()
	{
		parent::setUp();
		$user = new User('Initial User', 'somemail@email.com');
		$user->password = 'pass';
		$user->passwordConfirmation = 'pass';
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
	
	public function testExecuteLoginFailure()
	{
		$this->request('user/executeLogin', array('username' => 'No User',
		                                          'password' => 'pass'));

		$this->assertFlashError('You are not logged in');
		$this->assertVarSet('session');
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
}

?>
