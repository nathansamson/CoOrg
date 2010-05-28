<?php

class MenuEntryModelTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/menu.dataset.xml';
	}

	public function testCreate()
	{
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'Latst blogs';
		$entry->language = 'en';
		$entry->url = ':/blog/latest';
		$entry->provider = 'blog';
		$entry->action = 'latest';
		$entry->save();
		
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'Blog no 7';
		$entry->language = 'en';
		$entry->url = ':/blog/show/7';
		$entry->provider = 'blog';
		$entry->action = 'show';
		$entry->data = '7';
		$entry->save();
		
		$menu = Menu::get('main');
		$entries = $menu->entries('en');
		$this->assertEquals(5, count($entries));
		$this->assertEquals(3, $entries[3]->sequence);
		$this->assertEquals(':/blog/latest', $entries[3]->url);
		$this->assertEquals(4, $entries[4]->sequence);
		$this->assertEquals(':/blog/show/7', $entries[4]->url);
	}
	
	public function testMoveSequence()
	{
		$menu = Menu::get('main');
		$nlEntries = $menu->entries('nl');
		$entries = $menu->entries('en');
		
		$entries[0]->sequence = 2; // Move to the last place.
		$entries[0]->save();
		
		$this->assertEquals($nlEntries, $menu->entries('nl'));
		$entries = $menu->entries('en');
		
		$this->assertEquals(':some/otherthing/p/', $entries[0]->url);
		$this->assertEquals(0, $entries[0]->sequence);
		
		$this->assertEquals(':some/lastthing/p/', $entries[1]->url);
		$this->assertEquals(1, $entries[1]->sequence);
		
		$this->assertEquals(':some/thing/p/', $entries[2]->url);
		$this->assertEquals(2, $entries[2]->sequence);
		
		$entries[0]->sequence = 1; // Move on to the back
		$entries[0]->save();
		
		$entries = $menu->entries('en');
		$this->assertEquals(':some/lastthing/p/', $entries[0]->url);
		$this->assertEquals(0, $entries[0]->sequence);
		
		$this->assertEquals(':some/otherthing/p/', $entries[1]->url);
		$this->assertEquals(1, $entries[1]->sequence);
		
		$this->assertEquals(':some/thing/p/', $entries[2]->url);
		$this->assertEquals(2, $entries[2]->sequence);
		
		$this->assertEquals($nlEntries, $menu->entries('nl'));
	}

	public function testUpdate()
	{
		$menu = Menu::get('main');
		$e = $menu->entries('en');
		$e[0]->url = ':/other/url';
		$e[0]->save();
		
		$e = $menu->entries('en');
		$this->assertEquals(':/other/url', $e[0]->url);
	}
	
	public function testDelete()
	{
		$menu = Menu::get('main');
		$e = $menu->entries('en');
		$e[1]->delete();
		
		$entries = $menu->entries('en');
		$this->assertEquals(':some/thing/p/', $entries[0]->url);
		$this->assertEquals(0, $entries[0]->sequence);
		
		$this->assertEquals(':some/lastthing/p/', $entries[1]->url);
		$this->assertEquals(1, $entries[1]->sequence);
		
		$this->assertEquals(2, count($menu->entries('nl')));
	}
}

?>
