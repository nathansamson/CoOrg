<?php

class MockMenuEntryProvider implements IMenuEntryProvider
{
	public static function name()
	{
		return 'Mock';
	}
	
	public static function url($action, $language, $data)
	{
		return ':/mock/'.$action.'/'.$data;
	}
	
	public static function listActions()
	{
		return array('me' => 'Me',
		             'and' => 'And',
		             'my' => 'My',
		             'guitar' => 'Guitar');
	}
	
	public static function listData($action, $language)
	{
		return array(
			'one' => 'One',
			'two' => 'Two',
			'three' => 'Three',
			'four' => 'Four',
			'language' => $language
		);
	}
}
Menu::registerEntryProvider('MockMenuEntryProvider');

class MenuTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/menu.dataset.xml';
	}
	
	public function testCreateMenu()
	{
		$menu = new Menu();
		$menu->name = 'Navigation';
		$menu->save();
	}
	
	public function testCreateMenuNameExists()
	{
		$menu = new Menu();
		$menu->name = 'MeName';
		$menu->save();
		
		$duplicate = new Menu();
		$duplicate->name = 'MeName';
		try
		{
			$duplicate->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
		}
	}
	
	public function testUpdateMenuName()
	{
		$menu = new Menu();
		$menu->name = 'Some Type';
		$menu->description = 'My short description';
		$menu->save();
		
		$q = DB::prepare('SELECT * FROM Menu');
		$q->execute();
		
		$menu = Menu::get('Some Type');
		$this->assertNotNull($menu);
		$menu->name = 'Some Typo';
		$menu->save();
		
		$this->assertNull(Menu::get('Some Type'));
		$menu = Menu::get('Some Typo');
		$this->assertEquals('My short description', $menu->description);
	}
	
	public function testUpdateMenuEntriesAfterNameChange()
	{
		$menu = Menu::get('main');
		$menu->name = 'Some Name';
		$menu->save();
		
		$menu = Menu::get('Some Name');
		$this->assertEquals(3, count($menu->entries('en')));
	}
	
	public function testRemoveEntriesAfterDelete()
	{
		$menu = Menu::get('main');
		$menu->delete();
		
		$menu = new Menu;
		$menu->name = 'main';
		$menu->save();
		
		$this->assertEquals(0, count($menu->entries('en')));
	}
	
	public function testUpdateMenu()
	{
		$menu = new Menu;
		$menu->name = 'MeMenu';
		$menu->save();
		
		$menu = Menu::get('MeMenu');
		$menu->description = 'Me Description';
		$menu->save();
		
		Menu::get('MeMenu');
		$this->assertEquals('Me Description', $menu->description);
	}
	
	public function testUpdateNameExists()
	{
		$menu = new Menu;
		$menu->name = 'Taken';
		$menu->save();
		
		$menu = new Menu;
		$menu->name = 'Not taken';
		$menu->save();
		
		$menu = Menu::get('Not taken');
		$menu->name = 'Taken';
		try
		{
			$menu->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
		}
	}
	
	public function testListOfPossibleURLProviders()
	{
		$providers = Menu::getProviders();
		
		$this->assertEquals('Mock', $providers[0]::name());
		$this->assertEquals('Blog', $providers[1]::name());
		$this->assertEquals('URL', $providers[2]::name());
	}
}

?>