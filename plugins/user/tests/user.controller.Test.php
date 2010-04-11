<?php

class UserControllerTest extends CoOrgControllerTest
{
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

		$this->assertRedirected('/');
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
}

?>
