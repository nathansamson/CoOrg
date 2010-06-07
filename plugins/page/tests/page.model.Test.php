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

class PageTest extends CoOrgModelTest
{
	const dataset = 'page.dataset.xml';
	
	public function testCreate()
	{
		$page = new Page;
		$page->language = 'en';
		$page->title = 'My First Blog Ever';
		$page->content = 'This is My First Blog Ever';
		$page->author = 'nathan';
		$page->save();
		$this->assertEquals(self::today(), $page->created);
		$this->assertNull($page->updated);
		
		$page = Page::get($page->ID, 'en');
		$this->assertEquals('My First Blog Ever', $page->title);
		$this->assertEquals('This is My First Blog Ever', $page->content);
		$this->assertEquals(self::today(), $page->created);
	}
	
	public function testCreateIDInUse()
	{
		$page = new page;
		$page->language = 'en';
		$page->title = 'Test Blog';
		$page->content = 'A';
		$page->author = 'nathan';
		$page->save();
		$id1 = $page->ID;
		
		$page = new page;
		$page->language = 'en';
		$page->title = 'Test Blog';
		$page->content = 'B';
		$page->author = 'nathan';
		$page->save();
		$id2 = $page->ID;
		
		$page1 = Page::get($id1, 'en');
		$page2 = Page::get($id2, 'en');
		$this->assertEquals('Test Blog', $page1->title);
		$this->assertEquals('Test Blog', $page2->title);
		$this->assertEquals('A', $page1->content);
		$this->assertEquals('B', $page2->content);
	}
	
	public function testCreateSameIDDifferentLanguage()
	{
		$page = new page;
		$page->language = 'en';
		$page->title = 'Test Blog';
		$page->content = 'A';
		$page->author = 'nathan';
		$page->save();
		$id1 = $page->ID;
		
		$page = new page;
		$page->language = 'nl';
		$page->title = 'Test Blog';
		$page->content = 'B';
		$page->author = 'nathan';
		$page->save();
		$id2 = $page->ID;
		
		$this->assertEquals($id1, $id2);
		$page1 = Page::get($id1, 'en');
		$page2 = Page::get($id2, 'nl');
		$this->assertEquals('Test Blog', $page1->title);
		$this->assertEquals('Test Blog', $page2->title);
		$this->assertEquals('A', $page1->content);
		$this->assertEquals('B', $page2->content);
	}
	
	public function testUpdate()
	{
		$page = new Page;
		$page->language = 'en';
		$page->title = 'Test Blog';
		$page->content = 'A';
		$page->author = 'nathan';
		$page->save();
		$id1 = $page->ID;
		
		$page->title = 'Some Other Title because I made a Typo';
		$page->content = 'New Content';
		$page->lastEditor = 'someoneelse';
		$page->save();
		$this->assertEquals($id1, $page->ID);
		
		$page = Page::get($id1, 'en');
		$this->assertEquals('Some Other Title because I made a Typo', $page->title);
		$this->assertEquals('New Content', $page->content);
		$this->assertEquals(self::today(), $page->updated);
	}
	
	public function testDelete()
	{
		$page = Page::get('some-page', 'en');
		$page->delete();
		
		$this->assertNull(Page::get('some-page', 'en'));
		
		$page = new Page;
		$page->language = 'en';
		$page->title = 'Some Page';
		$page->content = 'meduladoedo';
		$page->author = 'nathan';
		$page->save();
		
		$page = Page::get('some-page', 'en');
		$this->assertEquals('meduladoedo', $page->content);
	}
	
	public function testPages()
	{
		$pager = Page::pages('en');
		$pages = $pager->execute(1, 10);
		$this->assertEquals(3, count($pages));
		
		$this->assertEquals('aabbcc', $pages[0]->ID);
		$this->assertEquals('some-page', $pages[1]->ID);
		$this->assertEquals('tidelodoo', $pages[2]->ID);
		
		$pager = Page::pages('nl');
		$pages = $pager->execute(1, 10);
		$this->assertEquals(2, count($pages));
		
		$this->assertEquals('aabbcc', $pages[0]->ID);
		$this->assertEquals('tidelodoo', $pages[1]->ID);
	}
	
	private static function today()
	{
		return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	}
}

?>
