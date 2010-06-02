<?php

class AdminControllerTest extends CoOrgControllerTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/admin.dataset.xml';
	}
	
	public function testIndex()
	{
		$s = new UserSession('dvorak', 'dvorak');
		$s->save();
		
		$this->request('admin');
		
		$this->assertRendered('index');
		$this->assertVarSet('modules');
	}
	
	public function testIndexNoAdmin()
	{
		$s = new UserSession('azerty', 'azerty');
		$s->save();
		
		$this->request('admin');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testIndexNotLoggedIn()
	{
		$this->request('admin');
		$this->assertRendered('login');
		$this->assertFlashError('You should be logged in to view this page');
	}
}

?>
