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
