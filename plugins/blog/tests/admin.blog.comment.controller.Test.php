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

class AdminBlogCommentControllerTest extends CoOrgControllerTest
{
	const dataset = 'blog.dataset.xml';
	
	public function testIndex()
	{
		$this->login('uberadmin');
		
		$this->request('admin/blog/comment');

		$this->assertRendered('admin/moderation-q');
		$this->assertVarSet('queue');
		$this->assertVarSet('qPager');
		$this->assertVarSet('spamOptions');
	}
	
	public function testIndexNotAllowed()
	{
		$this->login('nathan');
		
		$this->request('admin/blog/comment');
		
		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	public function testSpam()
	{
		$this->login('uberadmin');
		
		$this->request('admin/blog/comment/spam', array('commentID' => '666',
		                                           'feedback' => 'spam',
		                                           'from' => 'blog/admin/comment/index/3'));
		
		$comment = BlogComment::get(666);
		$this->assertEquals(PropertySpamStatus::SPAM, $comment->spamStatus);
		$this->assertFlashNotice('Comment marked as spam');
		$this->assertRedirected('blog/admin/comment/index/3');
	}
	
	public function testSpamNotAllowed()
	{
		$this->login('nathan');
		
		$this->request('admin/blog/comment/spam', array('commentID' => 1, 'feedback' => 'spam'));
		
		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	public function testNotSpam()
	{
		$this->login('uberadmin');
		
		$this->request('admin/blog/comment/notspam', array('commentID' => '666',
		                                           'from' => 'blog/admin/comment/index/3'));

		$comment = BlogComment::get(666);
		$this->assertEquals(PropertySpamStatus::OK, $comment->spamStatus);
		$this->assertFlashNotice('Comment unmarked as spam');
		$this->assertRedirected('blog/admin/comment/index/3');
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}

?>
