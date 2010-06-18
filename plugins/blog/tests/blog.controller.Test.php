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

class BlogControllerTest extends CoOrgControllerTest
{
	const dataset = 'blog.dataset.xml';

	public function setUp()
	{
		parent::setUp();
		if (UserSession::get())
		{
			UserSession::get()->delete();
		}
	}

	public function testIndex()
	{
		$this->request('blog');
		
		$this->assertVarSet('blogs');
		$blogs = CoOrgSmarty::$vars['blogs'];
		$blogpager = CoOrgSmarty::$vars['blogpager'];
		$this->assertNull($blogpager->prev());
		$this->assertNull($blogpager->next());
		$this->assertEquals(5, count($blogs));
		$this->assertEquals('en', $blogs[0]->language);
		$this->assertRendered('latest');
	}
	
	public function testIndexWithPager()
	{
		for ($i = 0; $i < 40; $i++)
		{
			$b = new Blog;
			$b->title = 'Some Title ' . $i;
			$b->authorID = 'nathan';
			$b->text = 'Some Random Content (4) <-- Its random';
			$b->language = 'en';
			$b->save();
		}
	
		$this->request('blog/index/3');
		$this->assertVarSet('blogs');
		$blogs = CoOrgSmarty::$vars['blogs'];
		$blogpager = CoOrgSmarty::$vars['blogpager'];
		$this->assertEquals(2, $blogpager->prev());
		$this->assertEquals(4, $blogpager->next());
		$this->assertEquals(2, count($blogpager->pages(2)));
		$this->assertEquals(10, count($blogs));
		$this->assertEquals('en', $blogs[0]->language);
		$this->assertRendered('latest');
	}
	
	public function testIndexOtherLanguage()
	{
		$this->request('nl/blog');
		$this->assertVarSet('blogs');
		$blogs = CoOrgSmarty::$vars['blogs'];
		$this->assertEquals(1, count($blogs));
		$this->assertEquals('nl', $blogs[0]->language);
		$this->assertRendered('latest');
	}
	
	public function testCreate()
	{
		$this->login();
		$this->request('blog/create');
		$this->assertVarSet('blog');
		$this->assertEquals('en', CoOrgSmarty::$vars['blog']->language);
		$this->assertRendered('create');
	}
	
	public function testCreateNotLoggedIn()
	{
		$this->request('blog/create');
		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
	}
	
	public function testSave()
	{
		$this->login();
		$this->request('blog/save', array('title' => 'My Blog Title',
		                                  'text' => 'My blog contents'));

		$this->assertFlashNotice('Your blog item is saved');
		
		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$this->assertRedirected('blog/show/'.$year.'/'.$month.'/'.$day.'/my-blog-title');
	}
	
	public function testSaveNotLoggedIn()
	{
		$this->request('blog/save', array('title' => 'My Blog Title',
		                                  'text' => 'My blog contents'));

		$this->assertFlashError('You should be logged in to view this page');
		$this->assertRendered('login');
	}
	
	public function testSaveFailure()
	{
		$this->login();
		$this->request('blog/save', array('title' => '',
		                                  'text' => 'My blog contents'));

		$this->assertFlashError('Your blog item is not saved');
		$this->assertVarSet('blog');
		$this->assertRendered('create');
	}
	
	public function testShow()
	{
		$this->request('blog/show/2010/04/11/xyz');
		$this->assertVarSet('blog');
		$this->assertRendered('show');
	}
	
	public function testShowNotFound()
	{
		$this->request('blog/show/2010/04/11/not-found');
		$this->assertFlashError('Blog item is not found');
		$this->assertRendered('notfound');
	}
	
	public function testShowWithNonPaddedDates()
	{
		$this->request('blog/show/2010/4/9/blog-post');
		$this->assertVarSet('blog');
		$this->assertRendered('show');
	}
	
	public function testShowOtherLanguage()
	{
		$this->request('blog/show/2010/04/10/translated-blog/nl');
		$this->assertVarSet('blog');
		$this->assertRendered('show');
	}
	
	public function testEdit()
	{
		$this->login();
		$this->request('blog/edit/2010/4/9/blog-post');
		$this->assertVarSet('blog');
		$this->assertRendered('edit');
	}
	
	public function testEditNotFound()
	{
		$this->login();
		$this->request('blog/edit/2010/4/10/blog-post');
		$this->assertFlashError('Blog item is not found');
		$this->assertRendered('notfound');
	}
	
	public function testEditWrongLogin()
	{
		$this->login('nele');
		$this->request('blog/edit/2010/4/9/blog-post');
		$this->assertFlashError('Blog item is not found');
		$this->assertRendered('notfound');
	}
	
	public function testEditOtherLanguage()
	{
		$this->login();
		$this->request('blog/edit/2010/4/10/translated-blog/nl');
		$this->assertVarSet('blog');
		$this->assertRendered('edit');
	}
	
	public function testUpdate()
	{
		$this->login('nathan');
		$this->request('blog/update', array(
		                                'year' => '2010',
		                                'month' => '4',
		                                'day' => '9',
		                                'id' => 'blog-post',
		                                'title' => 'Some New Title',
		                                'text' => 'Some new Content'));

		$this->assertRedirected('blog/show/2010/4/9/blog-post');
		$this->assertFlashNotice('Your blog item is updated');
	}
	
	public function testUpdateNotFound()
	{
		$this->login('nathan');
		$this->request('blog/update', array(
		                                'year' => '2010',
		                                'month' => '2',
		                                'day' => '9',
		                                'id' => 'blog-post',
		                                'title' => 'Some New Title',
		                                'text' => 'Some new Content'));

		$this->assertFlashError('Blog item is not found');
		$this->assertRendered('notfound');
	}
	
	public function testUpdateWrongLogin()
	{
		$this->login('nele');
		$this->request('blog/update', array(
		                                'year' => '2010',
		                                'month' => '4',
		                                'day' => '9',
		                                'id' => 'blog-post',
		                                'title' => 'Some New Title',
		                                'text' => 'Some new Content'));

		$this->assertFlashError('Blog item is not found');
		$this->assertRendered('notfound');
	}
	
	public function testUpdateOtherLanguage()
	{
		$this->login('nathan');
		$this->request('blog/update', array(
		                                'year' => '2010',
		                                'month' => '4',
		                                'day' => '10',
		                                'id' => 'translated-blog',
		                                'title' => 'Updated translation',
		                                'text' => 'Some new Content',
		                                'language' => 'nl'));

		$this->assertRedirected('blog/show/2010/4/10/translated-blog/nl');
		$this->assertFlashNotice('Your blog item is updated');
	}

	public function testTranslate()
	{
		$this->login('nathan');
		$this->request('blog/translate/2010/04/10/some-other-blog/en/nl');
		
		$this->assertVarSet('originalBlog');
		$this->assertVarSet('translatedBlog');
		$this->assertEquals('nl', CoOrgSmarty::$vars['translatedBlog']->language);
		$this->assertRendered('translate');
	}

	public function testTranslateNotFound()
	{
		$this->login('nathan');
		$this->request('blog/translate/2010/04/12/some-other-blog/en/nl');

		$this->assertFlashError('Blog item is not found');
		$this->assertRendered('notfound');
	}

	public function testTranslateWrongAuth()
	{
		$this->login('nele');
		$this->request('nl/blog/translate/2010/04/10/some-other-blog/en/nl');

		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}

	public function testTranslateSave()
	{
		$this->login('nathan');
		$this->request('nl/blog/translateSave', array('year'=>2010,
		                                       'month' => '04',
		                                        'day' => '10',
		                                        'id' => 'some-other-blog',
		                                        'fromLanguage' => 'en',
		                                        'title' => 'Vertaald',
		                                        'text' => 'Vertaalde tekst',
		                                        'language' => 'nl'));

		
		$this->assertFlashNotice(t('Your translation of the blog is saved'));
		$this->assertRedirected('blog/show/2010/04/10/vertaald');

		$this->assertNotNull(Blog::getBlog(2010, 4, 10, 'vertaald', 'nl'));
	}
	
	public function testTranslateSaveOtherLanguage()
	{
		$this->login('nathan');
		$this->request('blog/translateSave', array('year'=>2010,
		                                       'month' => '04',
		                                        'day' => '10',
		                                        'id' => 'some-other-blog',
		                                        'fromLanguage' => 'en',
		                                        'title' => 'Vertaald',
		                                        'text' => 'Vertaalde tekst',
		                                        'language' => 'nl'));

		
		$this->assertFlashNotice('Your translation of the blog is saved');
		$this->assertRedirected('blog/show/2010/04/10/vertaald/nl');

		$this->assertNotNull(Blog::getBlog(2010, 4, 10, 'vertaald', 'nl'));
	}

	public function testTranslateSaveError()
	{
		$this->login('nathan');
		$this->request('nl/blog/translateSave', array('year'=>2010,
		                                       'month' => '04',
		                                        'day' => '10',
		                                        'id' => 'some-other-blog',
		                                        'fromLanguage' => 'en',
		                                        'title' => 'Vertaald',
		                                        'text' => '',
		                                        'language' => 'nl'));

		$this->assertFlashError(t('Blog translation is not saved'));
		$this->assertVarSet('originalBlog');
		$this->assertVarSet('translatedBlog');
		$this->assertEquals('nl', CoOrgSmarty::$vars['translatedBlog']->language);
		$this->assertRendered('translate');
	}

	public function testTranslateSaveWrongAuth()
	{
		$this->login('nele');
		$this->request('nl/blog/translateSave', array('year'=>2010,
		                                       'month' => '04',
		                                        'day' => '10',
		                                        'id' => 'some-other-blog',
		                                        'fromLanguage' => 'en',
		                                        'title' => 'Vertaald',
		                                        'text' => 'Vertaalde tekst'));

		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	public function testLatestFeed()
	{
		$this->request('blog.atom/latest');
		
		$this->assertContentType('application/xml+atom');
		$this->assertVarSet('blogs');
		$blogs = CoOrgSmarty::$vars['blogs'];
		$this->assertEquals(5, count($blogs));
		$this->assertEquals('en', $blogs[0]->language);
		$this->assertRendered('latest', 'atom', null);
	}
	
	public function testArchive()
	{
		$this->request('br/blog/archive/2009');
		
		$this->assertVarSet('blogs');
		$this->assertVarIs('archiveYear', 2009);
		$this->assertRendered('archive');
	}
	
	public function testArchiveMonth()
	{
		$this->request('br/blog/archive/2009/6');
		
		$this->assertVarSet('blogs');
		$this->assertVarIs('archiveYear', 2009);
		$this->assertVarIs('archiveMonth', 6);
		$this->assertRendered('archive');
	}
	
	private function login($u = 'nathan')
	{
		$session = new UserSession($u, $u);
		$session->save();
	}
}

?>
