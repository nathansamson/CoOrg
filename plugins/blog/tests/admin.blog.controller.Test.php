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

class AdminBlogControllerTest extends CoOrgControllerTest
{
	const dataset = 'blog.dataset.xml';

	public function testIndex()
	{
		$this->postBlogs();
		$this->login('nathan');
		$this->request('admin/blog');
		
		$this->assertRendered('admin/index');
		$this->assertVarSet('blogs');
		$this->assertEquals(15, count(CoOrgSmarty::$vars['blogs']));
		$this->assertVarSet('blogpager');
		$this->assertNull(CoOrgSmarty::$vars['blogpager']->prev());
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('nele');
		$this->request('admin/blog');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testIndexPage()
	{
		$this->postBlogs();
	
		$this->login('nathan');
		$this->request('admin/blog/index/7');
		$this->assertVarSet('blogs');
		$this->assertEquals(10, count(CoOrgSmarty::$vars['blogs']));
		$this->assertVarSet('blogpager');
		$this->assertNull(CoOrgSmarty::$vars['blogpager']->next());
	}
	
	public function testConfig()
	{
		$this->login('uberadmin');
		$this->request('admin/blog/config');
		
		$this->assertRendered('admin/config');
		$this->assertVarSet('blogConfig');
		$this->assertVarSet('openForOptions');
	}
	
	public function testConfigNotAllowed()
	{
		$this->login('nathan');
		$this->request('admin/blog/config');
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	public function testSaveConfig()
	{
		$this->login('uberadmin');
		$this->request('admin/blog/configsave', array(
		                    'enableComments' => 'on',
		                    'enableCommentsFor' => 14,
		                    'moderationEmail' => 'somemail@mail.com',
		                    'moderationTime' => 2
		));
		
		$this->assertFlashNotice('Saved blog configuration');
		$this->assertRedirected('admin/blog/config');
		$this->assertTrue(CoOrg::config()->get('blog/enableComments'));
		$this->assertEquals(14, CoOrg::config()->get('blog/enableCommentsFor'));
		$this->assertEquals(2, CoOrg::config()->get('blog/moderation-time'));
		$this->assertEquals('somemail@mail.com', CoOrg::config()->get('blog/moderation-email'));
	}
	
	public function testSaveConfigFailure()
	{
		$this->login('uberadmin');
		$this->request('admin/blog/configsave', array(
		                    'enableComments' => 'on',
		                    'enableCommentsFor' => '14',
		                    'moderationEmail' => 'invalid',
		                    'moderationTime' => 1
		));
		
		$this->assertFlashError('Blog configuration not saved');
		$this->assertRendered('admin/config');
		$this->assertVarSet('blogConfig');
		$this->assertVarSet('openForOptions');
	}
	
	public function testSaveConfigNotAllowed()
	{
		$this->login('nathan');
		$this->request('admin/blog/configsave', array('enableComments' => 'on'));
		
		$this->assertRedirected('');
		$this->assertFlashError('You don\'t have the rights to view this page');
	}
	
	private function postBlogs()
	{
		for ($i = 0; $i < 95; $i++)
		{
			$blog = new Blog;
			$blog->authorID = 'nathan';
			$blog->title = 'Ole Ola ' . $i;
			$blog->text = '...';
			$blog->language = 'en';
			$blog->save();
		}
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}

?>
