<?php

class AdminMenuControllerTest extends CoOrgControllerTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/menu.dataset.xml';
	}
	
	public function testIndex()
	{
		$this->login('dvorak');
		$this->request('admin/menu');
		
		$this->assertVarSet('menus');
		$this->assertVarSet('newMenu');
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/menu');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSave()
	{
		$this->login('dvorak');
		$this->request('admin/menu/save', array('name' => 'MeTitle',
		                                        'description' => 'Desc'));
		
		$this->assertRedirected('admin/menu/edit/MeTitle');
		$this->assertFlashNotice('Menu created');
	}
	
	public function testSaveNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/menu/save', array('name' => 'MeTitle',
		                                        'description' => 'Desc'));
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSaveFailure()
	{
		$this->login('dvorak');
		$this->request('admin/menu/save', array('name' => 'main',
		                                        'description' => 'I do exist'));

		$this->assertRendered('index');
		$this->assertVarSet('newMenu');
		$this->assertVarSet('menus');
		$this->assertFlashError('Menu was not saved');
	}
	
	public function testEdit()
	{
		$this->login('dvorak');
		$this->request('admin/menu/edit/main');
		
		$this->assertRendered('edit');
		$this->assertVarSet('menu');
		$this->assertVarSet('providerActionCombos');
		$this->assertVarSet('adminlanguage');
		$this->assertVarIs('adminlanguage', 'en');
	}
	
	public function testEditNonDefaultLanguage()
	{
		$this->login('dvorak');
		$this->request('admin/menu/edit/main/nl');
		
		$this->assertRendered('edit');
		$this->assertVarSet('menu');
		$this->assertVarSet('providerActionCombos');
		$this->assertVarSet('adminlanguage');
		$this->assertVarIs('adminlanguage', 'nl');
	}
	
	public function testEditOtherLanguage()
	{
		$this->login('dvorak');
		$this->request('nl/admin/menu/edit/main');
		$this->assertVarSet('adminlanguage');
		$this->assertVarIs('adminlanguage', 'nl');
	}
	
	public function testEditNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/menu/edit/main');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testEditNotFound()
	{
		$this->login('dvorak');
		$this->request('admin/menu/edit/mainsdsd');
		
		$this->assertFlashError('Menu not found');
	}
	
	public function testUpdate()
	{
		$this->login('dvorak');
		$this->request('admin/menu/update', array('name'=>'main',
		                            'description'=> 'My Fancy new description'));
		
		$this->assertRedirected('admin/menu/edit/main');
		$menu = Menu::get('main');
		$this->assertEquals('My Fancy new description', $menu->description);
		$this->assertFlashNotice('Menu is updated');
	}
	
	public function testUpdateOtherLanguage()
	{
		$this->login('dvorak');
		$this->request('admin/menu/update', array('name'=>'main',
		                            'description'=> 'My Fancy new description',
		                            'language' => 'nl'));
		
		$this->assertRedirected('admin/menu/edit/main/nl');
	}
	
	public function testUpdateNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/menu/update', array('name'=>'main',
		                            'description'=> 'My Fancy new description'));
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testUpdateNotFound()
	{
		$this->login('dvorak');
		$this->request('admin/menu/update', array('name'=>'pneut',
		                            'description'=> 'My Fancy new description',
		                            'language' => 'nl'));
		$this->assertRedirected('admin/menu');
		$this->assertFlashError('Menu not found');
	}
	
	private function login($username)
	{
		$s = new UserSession($username, $username);
		$s->save();
	}
}

?>
