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
		$this->assertEquals('tidelodoe', $pages[1]->ID);
	}
	
	public function testLanguages()
	{
		$page = Page::get('tidelodoo', 'en');
		$languages = $page->languages();
		$this->assertEquals(2, count($languages));
		
		$this->assertEquals('fr', $languages[0]->language);
		$this->assertEquals('Français', $languages[0]->name);
		$this->assertEquals('toedeloedoe', $languages[0]->pageID);
		$this->assertEquals('nl', $languages[1]->language);
		$this->assertEquals('Nederlands', $languages[1]->name);
		$this->assertEquals('tidelodoe', $languages[1]->pageID);
		
		
		$page = Page::get('toedeloedoe', 'fr');
		$languages = $page->languages();
		$this->assertEquals(2, count($languages));
		
		$this->assertEquals('en', $languages[0]->language);
		$this->assertEquals('English', $languages[0]->name);
		$this->assertEquals('tidelodoo', $languages[0]->pageID);
		$this->assertEquals('nl', $languages[1]->language);
		$this->assertEquals('Nederlands', $languages[1]->name);
		$this->assertEquals('tidelodoe', $languages[1]->pageID);
		
		$page = Page::get('tidelodoe', 'nl');
		$languages = $page->languages();
		$this->assertEquals(2, count($languages));
		
		$this->assertEquals('en', $languages[0]->language);
		$this->assertEquals('English', $languages[0]->name);
		$this->assertEquals('tidelodoo', $languages[0]->pageID);
		$this->assertEquals('fr', $languages[1]->language);
		$this->assertEquals('Français', $languages[1]->name);
		$this->assertEquals('toedeloedoe', $languages[1]->pageID);
	}
	
	public function testCreateTranslation()
	{
		$l = new Language;
		$l->language = 'de';
		$l->name = 'German';
		$l->save();
		$l = new Language;
		$l->language = 'es';
		$l->name = 'Espanjol';
		$l->save();
		$page = Page::get('tidelodoo', 'en');
		$untranslated = $page->untranslated();
		$this->assertEquals(2, count($untranslated));
		$this->assertEquals('es', $untranslated[0]->language);
		$this->assertEquals('de', $untranslated[1]->language);
		
		$p = new Page;
		$p->title = 'Joedialtitoe';
		$p->language = 'de';
		$p->author = 'nathan';
		$p->content = 'German text';
		$p->originalLanguage = 'fr';
		$p->originalID = 'toedeloedoe';
		$p->save();
		
		$page = Page::get($p->ID, 'de');
		$languages = $page->languages();
		$this->assertEquals(3, count($languages));
		
		$page = Page::get('toedeloedoe', 'fr');
		$languages = $page->languages();
		$this->assertEquals(3, count($languages));
		
		$this->assertEquals('de', $languages[0]->language);
		$this->assertEquals('German', $languages[0]->name);
		$this->assertEquals($p->ID, $languages[0]->pageID);
		
		$page = Page::get('tidelodoe', 'nl');
		$languages = $page->languages();
		$this->assertEquals(3, count($languages));
		
		$this->assertEquals('de', $languages[0]->language);
		$this->assertEquals('German', $languages[0]->name);
		$this->assertEquals($p->ID, $languages[0]->pageID);
		
		$page = Page::get('tidelodoo', 'en');
		$languages = $page->languages();
		$this->assertEquals(3, count($languages));
		
		$this->assertEquals('de', $languages[0]->language);
		$this->assertEquals('German', $languages[0]->name);
		$this->assertEquals($p->ID, $languages[0]->pageID);
		
		$untranslated = $page->untranslated();
		$this->assertEquals(1, count($untranslated));
		$this->assertEquals('es', $untranslated[0]->language);
	}
	
	public function testCreateAlreadyTranslated()
	{
		$p = new Page;
		$p->title = 'Ole Ola';
		$p->language = 'fr';
		$p->author = 'nathan';
		$p->content = 'French text';
		$p->originalLanguage = 'en';
		$p->originalID = 'tidelodoo';
		
		try
		{
			$p->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals($p->title_error, 'This page is already translated');
		}
	}
	
	public function testDeleteTranslation()
	{
		$page = Page::get('tidelodoo', 'en');
		$page->delete();
		
		$p = new Page;
		$p->title = 'Tidelodoo';
		$p->language = 'en';
		$p->author = 'nathan';
		$p->content = 'English text';
		$p->save();
		
		$page = Page::get('toedeloedoe', 'fr');
		$languages = $page->languages();
		$this->assertEquals(1, count($languages));
		
		$this->assertEquals('nl', $languages[0]->language);
		$this->assertEquals('Nederlands', $languages[0]->name);
		$this->assertEquals('tidelodoe', $languages[0]->pageID);
		
		$page = Page::get('tidelodoe', 'nl');
		$languages = $page->languages();
		$this->assertEquals(1, count($languages));
		
		$this->assertEquals('fr', $languages[0]->language);
		$this->assertEquals('Français', $languages[0]->name);
		$this->assertEquals('toedeloedoe', $languages[0]->pageID);
	}
	
	private static function today()
	{
		return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	}
}

?>
