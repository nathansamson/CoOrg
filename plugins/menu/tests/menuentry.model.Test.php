<?php
/*
 * Copyright 2010 Nathan Samson <nathansamson at gmail dot com>
 *
 * This file is part of CoOrg.
 *
 * CoOrg is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

  * CoOrg is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with CoOrg.  If not, see <http://www.gnu.org/licenses/>.
*/

class MenuEntryModelTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/menu.dataset.xml';
	}

	public function testCreate()
	{
		CoOrg::config()->set('urlPrefix', ':language');
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'Latst blogs';
		$entry->language = 'en';
		$entry->entryID = 'BlogMenuEntryProvider/latest';
		$entry->save();
		$this->assertEquals('BlogMenuEntryProvider', $entry->provider);
		$this->assertEquals('latest', $entry->action);
		$this->assertEquals('en/blog', $entry->url);
		
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'Blog no 7';
		$entry->language = 'en';
		$entry->entryID = 'BlogMenuEntryProvider/show/2010-05-04/blog-no-7';
		$entry->save();
		$this->assertNotNull($entry->ID);
		$retrievedEntry = MenuEntry::get($entry->ID);
		$this->assertNotNull($retrievedEntry);
		$this->assertEquals('Blog no 7', $retrievedEntry->title);
		$this->assertEquals('BlogMenuEntryProvider', $retrievedEntry->provider);
		$this->assertEquals('show', $retrievedEntry->action);
		$this->assertEquals('2010-05-04/blog-no-7', $retrievedEntry->data);
		$this->assertEquals('en/blog/show/2010/05/04/blog-no-7', $retrievedEntry->url);
		$this->assertEquals(4, $retrievedEntry->sequence);
		
		$menu = Menu::get('main');
		$entries = $menu->entries('en');
		$this->assertEquals(5, count($entries));
		$this->assertEquals(3, $entries[3]->sequence);
		$this->assertEquals('en/blog', $entries[3]->url);
		$this->assertEquals(4, $entries[4]->sequence);
		$this->assertEquals('en/blog/show/2010/05/04/blog-no-7', $entries[4]->url);
		
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'External link';
		$entry->language = 'en';
		$entry->data = 'external.url.com';
		$entry->entryID = 'URLMenuEntryProvider';
		$entry->save();
		$this->assertEquals('http://external.url.com', $entry->url);
	}
	
	public function testCreateForOtherLanguage()
	{
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'Blog no 7';
		$entry->language = 'nl';
		$entry->entryID = 'BlogMenuEntryProvider/show/2010-05-04/blog-no-7';
		$entry->save();
		$this->assertEquals('nl/blog/show/2010/05/04/blog-no-7', $entry->url);
	}
	
	public function testCreateForNonEnglishDefault()
	{
		CoOrg::config()->set('urlPrefix', ':language');
		CoOrg::setDefaultLanguage('nl');
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'Blog no 7';
		$entry->language = 'nl';
		$entry->entryID = 'BlogMenuEntryProvider/show/2010-05-04/blog-no-7';
		$entry->save();
		$this->assertEquals('nl/blog/show/2010/05/04/blog-no-7', $entry->url);
	}
	
	public function testCreateForNonEnglishDefaultInEnglish()
	{
		CoOrg::config()->set('urlPrefix', ':language');
		CoOrg::setDefaultLanguage('nl');
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->title = 'Blog no 7';
		$entry->language = 'en';
		$entry->entryID = 'BlogMenuEntryProvider/show/2010-05-04/blog-no-7';
		$entry->save();
		$this->assertEquals('en/blog/show/2010/05/04/blog-no-7', $entry->url);
	}
	
	public function testCreateProviderDoesNotExist()
	{
		$entry = new MenuEntry;
		$entry->menu = 'main';
		$entry->language = 'en';
		$entry->title = 'metitle';
		$entry->entryID = 'NoProvider/action';
		try
		{
			$entry->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Provider not found', $entry->entryID_error);
		}
	}
	
	public function testMoveSequence()
	{
		$menu = Menu::get('main');
		$this->assertNotNull($menu);
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
		$this->assertNotNull($menu);
		$e = $menu->entries('en');
		$e[0]->url = ':/other/url';
		$e[0]->save();
		
		$e = $menu->entries('en');
		$this->assertEquals(':/other/url', $e[0]->url);
	}
	
	public function testDelete()
	{
		$menu = Menu::get('main');
		$this->assertNotNull($menu);
		$e = $menu->entries('en');
		$e[1]->delete();
		
		$entries = $menu->entries('en');
		$this->assertEquals(':some/thing/p/', $entries[0]->url);
		$this->assertEquals(0, $entries[0]->sequence);
		
		$this->assertEquals(':some/lastthing/p/', $entries[1]->url);
		$this->assertEquals(1, $entries[1]->sequence);
		
		$this->assertEquals(2, count($menu->entries('nl')));
	}
	
	public function testUrlProvider()
	{
		$this->assertEquals('http://someurl.com',
		                    UrlMenuEntryProvider::url('http://someurl.com', ''));
		$this->assertEquals('https://someurl.com',
		                    UrlMenuEntryProvider::url('https://someurl.com', ''));
		$this->assertEquals('http://someurl.com/?link=http://google.be',
		                    UrlMenuEntryProvider::url('someurl.com/?link=http://google.be', ''));
	}
}

?>
