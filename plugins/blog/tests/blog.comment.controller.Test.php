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

class BlogCommentControllerTest extends CoOrgControllerTest
{
	const dataset = 'blog.dataset.xml';

	public function testSave()
	{
		$this->login('nele');
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en',
			'comment' => 'My very first comment'));

		$this->assertFlashNotice('Your comment has been posted');
		$this->assertRedirected('blog/show/2010/4/10/some-other-blog');
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$comment = $blog->comments[0];
		$this->assertEquals('nele', $comment->authorID);
		$this->assertEquals('RE: Some Other Blog', $comment->title);
		$this->assertEquals('My very first comment', $comment->comment);
	}
	
	public function testSaveFailure()
	{
		$this->login('nele');
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en'));
		
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('blogComment');
		$c = CoOrgSmarty::$vars['blogComment'];
		$this->assertEquals('', $c->comment);
		$this->assertFlashError('Your comment was not posted');
	}
	
	public function testSaveCommentsNotAllowed()
	{
		$this->login('nele');
		$this->request('blog/comment/save', array(
			'blogID' => 'some-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en',
			'comment' => 'My very first comment'));

		$this->assertFlashError('Comments are not allowed for this blog');
		$this->assertRedirected('blog/show/2010/4/10/some-blog');
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-blog', 'en');
		$this->assertEquals(0, count($blog->comments));
	}
	
	public function testSaveAnonymous()
	{
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en',
			'comment' => 'My very first comment',
			'name' => 'My Anon',
			'email' => 'myemail@email.com'));
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$comment = $blog->comments[0];
		$this->assertNull($comment->author);
		$this->assertNotNull($comment->anonAuthor);
		$this->assertEquals('My Anon', $comment->anonAuthor->name);
		$this->assertEquals('myemail@email.com', $comment->anonAuthor->email);
		$this->assertEquals('0.0.0.0', $comment->anonAuthor->IP);
		$this->assertEquals('RE: Some Other Blog', $comment->title);
		$this->assertEquals('My very first comment', $comment->comment);
	}
	
	public function testSaveAnonymousFailure()
	{
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en',
			'comment' => 'My very first comment',
			'name' => 'My Anon',
			'email' => 'myemail'));
		
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('blogComment');
		$c = CoOrgSmarty::$vars['blogComment'];
		$this->assertEquals('My very first comment', $c->comment);
		$this->assertVarSet('anonProfile');
		$p = CoOrgSmarty::$vars['anonProfile'];
		$this->assertEquals('My Anon', $p->name);
		$this->assertFlashError('Your comment was not posted');
	}
	
	public function testEdit()
	{
		$this->login('nele');
		$this->request('blog/comment/edit/1');
		
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('blogComment');
		$this->assertVarSet('blogCommentEdit');
		$c = CoOrgSmarty::$vars['blogCommentEdit'];
		$this->assertEquals(1, $c->ID);
	}
	
	public function testEditAdmin()
	{
		$this->login('nathan');
		$this->request('blog/comment/edit/1');
		
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('blogComment');
		$this->assertVarSet('blogCommentEdit');
		$c = CoOrgSmarty::$vars['blogCommentEdit'];
		$blog = CoOrgSmarty::$vars['blog'];
		$this->assertEquals(1, $c->ID);
		$this->assertEquals('xyzer', $blog->ID);
	}
	
	public function testEditNotAllowed()
	{
		$this->login('nele');
		$this->request('blog/comment/edit/2');
		
		$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertRedirected('blog/show/2010/4/11/xyz');
	}
	
	public function testEditAdnonymous()
	{
		$this->login('nathan');
		$this->request('blog/comment/edit/3');
		
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('blogComment');
		$this->assertVarSet('blogCommentEdit');
		$c = CoOrgSmarty::$vars['blogCommentEdit'];
		$blog = CoOrgSmarty::$vars['blog'];
		$this->assertVarSet('anonProfileEdit');
		$p = CoOrgSmarty::$vars['anonProfileEdit'];
		$this->assertEquals(1, $p->ID);
		$this->assertEquals(3, $c->ID);
		$this->assertEquals('xyz', $blog->ID);
	}
	
	public function testEditAdnonymousNotAllowed()
	{
		$this->login('nele');
		$this->request('blog/comment/edit/2');
		
		$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertRedirected('blog/show/2010/4/11/xyz');
	}
	
	public function testUpdate()
	{
		$this->login('nele');
		$this->request('blog/comment/update', array(
		               'ID' => 1,
		               'comment' => 'My new text'
		               ));
		
		$this->assertFlashNotice('Updated comment');
		$this->assertRedirected('blog/show/2010/4/10/xyzer');
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$comment = $blog->comments[0];
		$this->assertEquals('My new text', $comment->comment);
	}
	
	public function testUpdateFailure()
	{
		$this->login('nele');
		$this->request('blog/comment/update', array(
		               'ID' => 1
		               ));
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('blogComment');
		$this->assertVarSet('blogCommentEdit');
		$c = CoOrgSmarty::$vars['blogCommentEdit'];
		$blog = CoOrgSmarty::$vars['blog'];
		$this->assertEquals(1, $c->ID);
		$this->assertEquals('xyzer', $blog->ID);
		$this->assertFlashError('Could not save comment');
	}
	
	public function testUpdateAdmin()
	{
		$this->login('nathan');
		$this->request('blog/comment/update', array(
		               'ID' => 1,
		               'comment' => 'My new text'
		               ));
		
		$this->assertFlashNotice('Updated comment');
		$this->assertRedirected('blog/show/2010/4/10/xyzer');
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$comment = $blog->comments[0];
		$this->assertEquals('My new text', $comment->comment);
	}
	
	public function testUpdateNotAllowed()
	{
		$this->login('nele');
		$this->request('blog/comment/update', array('ID' => '2'));
		
		$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertRedirected('blog/show/2010/4/11/xyz');
	}
	
	public function testUpdateAdnonymous()
	{
		$this->login('nathan');
		$this->request('blog/comment/update', array(
		               'ID' => 3,
		               'comment' => 'My comment @ XYZ (en)',
		               'name' => 'New Name',
		               'email' => 'email@email.com',
		               'website' => 'safe.com'
		               ));
		
		$this->assertFlashNotice('Updated comment');
		$this->assertRedirected('blog/show/2010/4/11/xyz');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$comment = $blog->comments[1];
		$this->assertEquals('My comment @ XYZ (en)', $comment->comment);
		$this->assertEquals('email@email.com', $comment->anonAuthor->email);
		$this->assertEquals('New Name', $comment->anonAuthor->name);
	}
	
	public function testUpdateAdnonymousFailure()
	{
		$this->login('nathan');
		$this->request('blog/comment/update', array(
		               'ID' => 3,
		               'comment' => 'My comment @ XYZ (en)',
		               'name' => 'New Name',
		               'email' => 'email',
		               'website' => 'safe.com'
		               ));
		
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('blogComment');
		$this->assertVarSet('blogCommentEdit');
		$c = CoOrgSmarty::$vars['blogCommentEdit'];
		$blog = CoOrgSmarty::$vars['blog'];
		$this->assertEquals(3, $c->ID);
		$this->assertEquals('xyz', $blog->ID);
		$this->assertFlashError('Could not save comment');
		$this->assertVarSet('anonProfileEdit');
		$p = CoOrgSmarty::$vars['anonProfileEdit'];
		$this->assertEquals(1, $p->ID);
	}
	
	public function testUpdateAdnonymousNotAllowed()
	{
		$this->login('nele');
		$this->request('blog/comment/update', array(
		               'ID' => 3,
		               'comment' => 'My comment @ XYZ (en)',
		               'name' => 'New Name',
		               'email' => 'email@email.com',
		               'website' => 'safe.com'
		               ));

		$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertRedirected('blog/show/2010/4/11/xyz');
	}
	
	public function testDelete()
	{
		$this->login('nele');
		$this->request('blog/comment/delete', array('ID' => 1));
		
		$this->assertFlashNotice('Deleted comment');
		$this->assertRedirected('blog/show/2010/4/10/xyzer');
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$this->assertEquals(0, count($blog->comments));
	}
	
	public function testDeleteAdmin()
	{
		$this->login('nathan');
		$this->request('blog/comment/delete', array('ID' => 1));
		
		$this->assertFlashNotice('Deleted comment');
		$this->assertRedirected('blog/show/2010/4/10/xyzer');
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$this->assertEquals(0, count($blog->comments));
	}
	
	public function testDeleteNotAllowed()
	{
		$this->login('nele');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(2, count($blog->comments));
		$this->request('blog/comment/delete', array('ID' => 2));
		
		$this->assertFlashError('You are not allowed to delete this comment');
		$this->assertRedirected('blog/show/2010/4/11/xyz');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(2, count($blog->comments));
	}
	
	public function testDeleteAnonymous()
	{
		$this->login('nathan');
		$this->request('blog/comment/delete', array('ID' => 3));
		
		$this->assertFlashNotice('Deleted comment');
		$this->assertRedirected('blog/show/2010/4/11/xyz');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(1, count($blog->comments));
		
		$profile = AnonProfile::get(1);
		$this->assertNull($profile);
	}
	
	public function testDeleteAnonymousNotAllowed()
	{
		$this->login('nele');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(2, count($blog->comments));
		$this->request('blog/comment/delete', array('ID' => 3));
		
		$this->assertFlashError('You are not allowed to delete this comment');
		$this->assertRedirected('blog/show/2010/4/11/xyz');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(2, count($blog->comments));
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}

?>
