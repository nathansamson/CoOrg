<?php

class AdminMenuEntryControllerTest extends CoOrgControllerTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/menu.dataset.xml';
	}
	
	public function testSave()
	{
		$this->login('dvorak');
		$this->request('admin/menu/entry/save', array(
		                  'menu' => 'main',
		                  'language' => 'nl',
		                  'title' => 'Dutch Title',
		                  'entryID' => 'URLMenuEntryProvider',
		                  'data' => 'http://belgium.be'));

		$this->assertRedirected('admin/menu/edit/main/nl');
		$this->assertFlashNotice('Menu entry added');
		
		$menu = Menu::get('main');
		$entries = $menu->entries('nl');
		$this->assertEquals('Dutch Title', $entries[count($entries)-1]->title);
		$this->assertEquals('http://belgium.be', $entries[count($entries)-1]->url);
	}
	
	public function testSaveNotAllowed()
	{
		$this->login('azerty');
		$this->request('admin/menu/entry/save', array(
		                  'menu' => 'main',
		                  'language' => 'nl',
		                  'title' => 'Dutch Title',
		                  'entryID' => 'URLMenuEntryProvider',
		                  'data' => 'http://belgium.be'));
		
		$this->assertRedirected('/');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSaveFailure()
	{
		$this->login('dvorak');
		$this->request('admin/menu/entry/save', array(
		                  'menu' => 'main',
		                  'language' => 'nl',
		                  'title' => 'Dutch Title',
		                  'entryID' => 'InvalidProvider',
		                  'data' => 'http://belgium.be'));

		$this->assertRendered('edit');
		$this->assertVarSet('newEntry');
		$this->assertVarSet('providerActionCombos');
		$this->assertVarSet('menu');
		$this->assertVarSet('adminlanguage');
		$this->assertVarIs('adminlanguage', 'nl');
		$this->assertFlashError('Entry was not saved');
	}
	
	public function testSaveNotFound()
	{
		$this->login('dvorak');
		$this->request('admin/menu/entry/save', array(
		                  'menu' => 'pneut',
		                  'language' => 'nl',
		                  'title' => 'Dutch Title',
		                  'entryID' => 'InvalidProvider',
		                  'data' => 'http://belgium.be'));
		$this->assertRedirected('admin/menu/nl');
		$this->assertFlashError('Menu not found');
	}
	
	public function testDelete()
	{
		$this->login('dvorak');
		$m = Menu::get('main');
		$e = $m->entries('nl');
		
		$this->request('admin/menu/entry/delete', array('entry' => $e[0]->ID));
		$this->assertFlashNotice('Entry is deleted');
		$this->assertRedirected('admin/menu/edit/main/nl');
		
		$this->assertEquals(1, count($m->entries('nl')));
	}
	
	public function testDeleteNotFound()
	{
		$this->login('dvorak');
		$this->request('admin/menu/entry/delete', array('entry' => 234545)); // Pretty sure that does not exists...
		
		$this->assertRedirected('admin/menu');
		$this->assertFlashError('Menu entry not found');
	}
	
	public function testDeleteNotAllowed()
	{
		$this->login('azerty');
		
		$m = Menu::get('main');
		$e = $m->entries('nl');
		
		$this->request('admin/menu/entry/delete', array('entry' => $e[0]->ID));
		$this->assertRedirected('/');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testMove()
	{
		$this->login('dvorak');
		$m = Menu::get('main');
		$e = $m->entries('nl');
		
		$this->request('admin/menu/entry/move', array('entry' => $e[0]->ID,
		                                              'newsequence' => 1));
		$this->assertFlashNotice('Entry is moved');
		$this->assertRedirected('admin/menu/edit/main/nl');
		
		$e = $m->entries('nl');
		$this->assertEquals(2, count($e));
		$this->assertEquals('Iets anders', $e[0]->title);
		$this->assertEquals('Iets', $e[1]->title);
	}
	
	public function testMoveNotAllowed()
	{
		$this->login('azerty');
		
		$m = Menu::get('main');
		$e = $m->entries('nl');
		
		$this->request('admin/menu/entry/move', array('entry' => $e[0]->ID, 'newsequence' => 0));
		$this->assertRedirected('/');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testMoveNotFound()
	{
		$this->login('dvorak');
		$this->request('admin/menu/entry/move', array('entry' => 234545, 'newsequence' => 0)); // Pretty sure that does not exists...
		
		$this->assertRedirected('admin/menu');
		$this->assertFlashError('Menu entry not found');
	}
	
	private function login($username)
	{
		$s = new UserSession($username, $username);
		$s->save();
	}
}
?>
