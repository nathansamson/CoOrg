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
		$this->assertEquals(PropertySpamStatus::OK, $comment->spamStatus);
		$this->assertNull($comment->spamSessionID);
	}
	
	public function testSaveFailure()
	{
		$this->login('nele');
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en'));
		
		$this->assertRendered('show');
		$this->assertVarSet('spamOptions');
		$this->assertVarSet('blog');
		$this->assertVarSet('newComment');
		$c = CoOrgSmarty::$vars['newComment'];
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
		
		$this->assertFlashNotice('Your comment has been posted');
		$this->assertRedirected('blog/show/2010/4/10/some-other-blog');
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$comment = $blog->comments[0];
		$this->assertNull($comment->author);
		$this->assertNotNull($comment->anonAuthor);
		$this->assertEquals('My Anon', $comment->anonAuthor->name);
		$this->assertEquals('myemail@email.com', $comment->anonAuthor->email);
		$this->assertEquals('0.0.0.0', $comment->anonAuthor->IP);
		$this->assertEquals('RE: Some Other Blog', $comment->title);
		$this->assertEquals('My very first comment', $comment->comment);
		$this->assertEquals(PropertySpamStatus::OK, $comment->spamStatus);
		$this->assertNotNull($comment->spamSessionID);
	}
	
	public function testSaveAnonymousSpam()
	{
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en',
			'comment' => 'BODY SPAM',
			'name' => 'My Anon',
			'email' => 'myemail@email.com'));
		
		$this->assertFlashNotice('Your comment has been marked as spam, and will not appear');
		$this->assertRedirected('blog/show/2010/4/10/some-other-blog');
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$this->assertEquals(0, count($blog->comments));
	}
	
	public function testSaveAnonymousUnknown()
	{
		CoOrg::config()->set('blog/moderation-email', 'moderation@mail.com');
		CoOrg::config()->set('blog/moderation-time', 1);
		CoOrg::config()->set('blog/last-moderation-mail', time()-60*60*13); // Last mail sent 13 hours ago
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en',
			'comment' => 'UNKNOWN BODY',
			'name' => 'My Anon',
			'email' => 'myemail@email.com'));
		
		$this->assertFlashNotice('Your comment will be moderated, and will appear on a later time on the site');
		$this->assertRedirected('blog/show/2010/4/10/some-other-blog');
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$this->assertEquals(1, count($blog->comments));
		$comment = $blog->comments[0];
		$this->assertNull($comment->author);
		$this->assertNotNull($comment->anonAuthor);
		$this->assertEquals('My Anon', $comment->anonAuthor->name);
		$this->assertEquals('myemail@email.com', $comment->anonAuthor->email);
		$this->assertEquals('0.0.0.0', $comment->anonAuthor->IP);
		$this->assertEquals('RE: Some Other Blog', $comment->title);
		$this->assertEquals('UNKNOWN BODY', $comment->comment);
		$this->assertEquals(PropertySpamStatus::UNKNOWN, $comment->spamStatus);
		$this->assertNotNull($comment->spamSessionID);
		$this->assertEquals(0, count(Mail::$sentMails));
	}
	
	public function testSaveAnonymousUnknownWithModerationMail()
	{
		CoOrg::config()->set('blog/moderation-email', 'moderation@mail.com');
		CoOrg::config()->set('blog/moderation-time', 1);
		CoOrg::config()->set('blog/last-moderation-mail', time()-60*60*33); // Last mail sent 33 hours ago
		$this->request('blog/comment/save', array(
			'blogID' => 'some-other-blog',
			'blogDate' => '2010-04-10',
			'blogLanguage' => 'en',
			'comment' => 'UNKNOWN BODY',
			'name' => 'My Anon',
			'email' => 'myemail@email.com'));
		
		$this->assertFlashNotice('Your comment will be moderated, and will appear on a later time on the site');
		$this->assertRedirected('blog/show/2010/4/10/some-other-blog');
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$this->assertEquals(1, count($blog->comments));
		$this->assertMailSent('moderation@mail.com', 'The Site: New comment to moderate',
		                      'plugins/comments/views/default/mails/newcomment',
		                      array('totalModerationQueue' => '2', // This new one + 1 in DB
		                            'title' => 'RE: Some Other Blog',
		                            'body' => 'UNKNOWN BODY',
		                            'date' => '**?**',
		                            'messageURL' => 'http://www.test.info/blog/show/2010/4/10/some-other-blog#comment'.$blog->comments[0]->ID,
		                            'moderationURL' => 'http://www.test.info/admin/blog/comment',
		                            'site' => 'The Site'));
		$config = new Config(COORG_TEST_CONFIG);
		$this->assertLessThan(2, abs(time() - $config->get('blog/last-moderation-mail')));
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
		$this->assertVarSet('newComment');
		$c = CoOrgSmarty::$vars['newComment'];
		$this->assertEquals('My very first comment', $c->comment);
		$this->assertNotNull($c->anonAuthor);
		$this->assertEquals('My Anon', $c->anonAuthor->name);
		$this->assertFlashError('Your comment was not posted');
	}
	
	public function testEdit()
	{
		$this->login('nele');
		$this->request('blog/comment/edit/1');
		
		$this->assertRendered('show');
		$this->assertVarSet('blog');
		$this->assertVarSet('newComment');
		$this->assertVarSet('editComment');
		$c = CoOrgSmarty::$vars['editComment'];
		$this->assertEquals(1, $c->ID);
	}
	
	public function testEditAdmin()
	{
		$this->login('nathan');
		$this->request('blog/comment/edit/1');
		
		$this->assertRendered('show');
		$this->assertVarSet('spamOptions');
		$this->assertVarSet('newComment');
		$this->assertVarSet('editComment');
		$c = CoOrgSmarty::$vars['editComment'];
		$this->assertVarSet('blog');
		$this->assertEquals(1, $c->ID);
		$blog = CoOrgSmarty::$vars['blog'];
		$this->assertEquals('xyzer', $blog->ID);
	}
	
	public function testEditNotAllowed()
	{
		$this->login('nele');
		$this->request('blog/comment/edit/2');
		
		//$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertFlashError('You don\'t have the rights to view this page');
		//$this->assertRedirected('blog/show/2010/4/11/xyz');
		$this->assertRedirected('');
	}
	
	public function testEditAnonymous()
	{
		$this->login('nathan');
		$this->request('blog/comment/edit/3');
		
		$this->assertRendered('show');
		$this->assertVarSet('spamOptions');
		$this->assertVarSet('blog');
		$blog = CoOrgSmarty::$vars['blog'];
		$this->assertVarSet('newComment');
		$this->assertVarSet('editComment');
		$c = CoOrgSmarty::$vars['editComment'];
		$this->assertNotNull($c->anonAuthor);
		$p = $c->anonAuthor;
		$this->assertEquals(1, $p->ID);
		$this->assertEquals(3, $c->ID);
		$this->assertEquals('xyz', $blog->ID);
	}
	
	public function testEditAdnonymousNotAllowed()
	{
		$this->login('nele');
		$this->request('blog/comment/edit/2');
		
		//$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertFlashError('You don\'t have the rights to view this page');
		//$this->assertRedirected('blog/show/2010/4/11/xyz');
		$this->assertRedirected('');
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
		$this->assertVarSet('spamOptions');
		$this->assertVarSet('blog');
		$this->assertVarSet('newComment');
		$this->assertVarSet('editComment');
		$c = CoOrgSmarty::$vars['editComment'];
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
		
		//$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertFlashError('You don\'t have the rights to view this page');
		//$this->assertRedirected('blog/show/2010/4/11/xyz');
		$this->assertRedirected('');
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
		$this->assertVarSet('newComment');
		$this->assertVarSet('editComment');
		$c = CoOrgSmarty::$vars['editComment'];
		$blog = CoOrgSmarty::$vars['blog'];
		$this->assertEquals(3, $c->ID);
		$this->assertEquals('xyz', $blog->ID);
		$this->assertFlashError('Could not save comment');
		$this->assertNotNull($c->anonAuthor);
		$this->assertEquals(1, $c->anonAuthor->ID);
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

		//$this->assertFlashError(t('You are not allowed to edit this comment'));
		$this->assertFlashError('You don\'t have the rights to view this page');
		//$this->assertRedirected('blog/show/2010/4/11/xyz');
		$this->assertRedirected('');
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
		
		//$this->assertFlashError('You are not allowed to delete this comment');
		$this->assertFlashError('You don\'t have the rights to view this page');
		//$this->assertRedirected('blog/show/2010/4/11/xyz');
		$this->assertRedirected('');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(2, count($blog->comments));
	}
	
	public function testDeleteAnonymous()
	{
		$this->login('nathan');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(2, count($blog->comments));
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
		
		//$this->assertFlashError('You are not allowed to delete this comment');
		$this->assertFlashError('You don\'t have the rights to view this page');
		//$this->assertRedirected('blog/show/2010/4/11/xyz');
		$this->assertRedirected('');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertEquals(2, count($blog->comments));
	}
	
	public function testMarkAsSpam()
	{
		$this->login('nathan');
		
		$this->request('blog/comment/spam', array(
			'commentID' => 2,
			'feedback' => 'profanity'));
		$this->assertFlashNotice('Comment marked as spam');
		$this->assertRedirected('blog/show/2010/4/11/xyz');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$comment = $blog->comments[0];
		$this->assertEquals(2, $comment->ID);
		$this->assertEquals(PropertySpamStatus::SPAM, $comment->spamStatus);
	}
	
	public function testMarkAsSpamNotAllowed()
	{
		$this->login('nele');
		
		$this->request('blog/comment/spam', array(
			'commentID' => 2,
			'feedback' => 'profanity'));
		
		//$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	public function testMarkAsSpamNoSpamSessionID()
	{
		$this->login('nathan');
		
		$this->request('blog/comment/spam', array(
			'commentID' => 1,
			'feedback' => 'spam'));
		$this->assertFlashNotice('Comment marked as spam');
		$this->assertRedirected('blog/show/2010/4/10/xyzer');
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$comment = $blog->comments[0];
		$this->assertEquals(1, $comment->ID);
		$this->assertEquals(PropertySpamStatus::SPAM, $comment->spamStatus);
	}
	
	public function testUnmarkSpam()
	{
		$this->login('nathan');
		
		$this->request('blog/comment/notspam', array(
			'commentID' => 3));
		$this->assertFlashNotice('Comment unmarked as spam');
		$this->assertRedirected('blog/show/2010/4/11/xyz');
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$comment = $blog->comments[1];
		$this->assertEquals(3, $comment->ID);
		$this->assertEquals(PropertySpamStatus::OK, $comment->spamStatus);
	}
	
	public function testUnmarkSpamNotAllowed()
	{
		$this->login('nele');
		
		$this->request('blog/comment/notspam', array(
			'commentID' => 3));
		
		$this->assertFlashError('You don\'t have the rights to view this page');
		$this->assertRedirected('');
	}
	
	private function login($u)
	{
		$s = new UserSession($u, $u);
		$s->save();
	}
}

?>
