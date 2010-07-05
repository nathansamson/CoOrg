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

class BlogCommentController extends Controller
{
	protected $_blog;
	protected $_comment;

	/**
	 * @before findBlog $blogID $blogDate $blogLanguage
	*/
	public function save($blogID, $blogDate, $blogLanguage, $comment,
	                     $name, $email, $website)
	{
		if ($this->_blog->allowComments())
		{
			$blogComment = new BlogComment;
			$blogComment->title = 'RE: ' . $this->_blog->title;
			$blogComment->comment = $comment;
			$anon = null;
			if (UserSession::get())
			{
				$blogComment->author = UserSession::get()->user();
				$blogComment->spamStatus = PropertySpamStatus::OK;
			}
			else
			{
				$anon = new AnonProfile;
				$anon->name = $name;
				$anon->email = $email;
				$anon->website = $website;
				$anon->IP = Session::IP();
				$blogComment->anonAuthor = $anon;
				$message = new MollomMessage;
				$message->title = $blogComment->title;
				$message->body = $blogComment->comment;
				$message->authorName = $name;
				$message->authorEmail = $email;
				$message->authorWebsite = $website;
				$blogComment->spamStatus = $message->check();
				$blogComment->spamSessionID = Session::get('mollom/sessionid');
			}
			if ($blogComment->spamStatus != PropertySpamStatus::SPAM)
			{
				try
				{
					$this->_blog->comments[] = $blogComment;
					if ($blogComment->spamStatus == PropertySpamStatus::OK)
					{
						$this->notice(t('Your comment has been posted'));
					}
					else
					{
						$config = CoOrg::config();
						
						if (time() - $config->get('blog/last-moderation-mail') > 60*60*24*$config->get('blog/moderation-time'))
						{
							$config->set('blog/last-moderation-mail', time());
							$config->save();
							$site = $config->get('site/title');
							$receiver = $config->get('blog/moderation-email');
							$mail = $this->mail();
							$mail->title = $blogComment->title;
							$mail->body = $blogComment->comment;
							$mail->date = $blogComment->timePosted;
							$mail->messageURL = CoOrg::createFullURL(array(
							        'blog/show',
							        $this->_blog->year,
							        $this->_blog->month,
							        $this->_blog->day,
							        $this->_blog->ID),
							        CoOrg::getDefaultLanguage(),
							        'comment'.$blogComment->ID
							        );
							$mail->moderationURL = CoOrg::createFullURL(array('admin/comment/queue'));
							$mail->totalModerationQueue = Comment::moderationQueueLength();
							$mail->site = $site;
							$mail->to($receiver)
							     ->subject(t('%site: New comment to moderate', array('site' => $site)))
							     ->send('mails/newcomment');
						}
					
						$this->notice(t('Your comment will be moderated, and will appear on a later time on the site'));	
					}
					$this->redirect('blog/show',
							        $this->_blog->year,
							        $this->_blog->month,
							        $this->_blog->day,
							        $this->_blog->ID);
				}
				catch (ValidationException $e)
				{
					$this->error(t('Your comment was not posted'));
					$this->blogComment = $blogComment;
					$this->blog = $this->_blog;
					if ($anon)
					{
						$this->anonProfile = $anon;
					}
					$this->spamOptions = BlogControllerHelper::spamOptions();
					$this->render('show');
				}
			}
			else
			{
				$this->notice(t('Your comment has been marked as spam, and will not appear'));
				$this->redirect('blog/show',
							        $this->_blog->year,
							        $this->_blog->month,
							        $this->_blog->day,
							        $this->_blog->ID);
			}
		}
		else
		{
			$this->error(t('Comments are not allowed for this blog'));
			$this->redirect('blog/show',
				            $this->_blog->year,
				            $this->_blog->month,
				            $this->_blog->day,
				            $this->_blog->ID);
		}
	}
	
	/**
	 * @before findComment $ID
	*/
	public function edit($ID)
	{
		if ($this->hasCommentAccess())
		{
			$this->blog = $this->_blog;
			$this->blogCommentEdit = $this->_comment;
			$this->blogComment = new BlogComment;
			if ($anon = $this->_comment->anonAuthor)
			{
				$this->anonProfileEdit = $anon;
			}
			$this->spamOptions = BlogControllerHelper::spamOptions();
			$this->render('show');
		}
		else
		{
			$this->error(t('You are not allowed to edit this comment'));
			$this->redirect('blog/show',
				            $this->_blog->year,
				            $this->_blog->month,
				            $this->_blog->day,
				            $this->_blog->ID);
		}
	}
	
	/**
	 * @before findComment $ID
	*/
	public function update($ID, $comment, $name, $email, $website)
	{
		if ($this->hasCommentAccess())
		{
			$this->_comment->comment = $comment;
			if ($p = $this->_comment->anonAuthor)
			{
				$p->name = $name;
				$p->email = $email;
				$p->website = $website;
			}
			try
			{
				if ($p)
				{
					$p->save();
				}
				$this->_comment->save();
				
				$this->notice(t('Updated comment'));
				$this->redirect('blog/show',
					            $this->_blog->year,
					            $this->_blog->month,
					            $this->_blog->day,
					            $this->_blog->ID);
			}
			catch(ValidationException $e)
			{
				$this->error(t('Could not save comment'));
				$this->blog = $this->_blog;
				if ($p)
				{
					$this->anonProfileEdit = $p;
				}
				$this->blogCommentEdit = $this->_comment;
				$this->blogComment = new BlogComment;
				$this->spamOptions = BlogControllerHelper::spamOptions();
				$this->render('show');
			}
		}
		else
		{
			$this->error(t('You are not allowed to edit this comment'));
			$this->redirect('blog/show',
				            $this->_blog->year,
				            $this->_blog->month,
				            $this->_blog->day,
				            $this->_blog->ID);
		}
	}
	
	/**
	 * @before findComment $ID
	*/
	public function delete($ID, $comment)
	{
		if ($this->hasCommentAccess())
		{
			$this->_comment->delete();
			if ($p = $this->_comment->anonAuthor)
			{
				$p->delete();
			}
			
			$this->notice(t('Deleted comment'));
			$this->redirect('blog/show',
				            $this->_blog->year,
				            $this->_blog->month,
				            $this->_blog->day,
				            $this->_blog->ID);
		}
		else
		{
			$this->error(t('You are not allowed to delete this comment'));
			$this->redirect('blog/show',
				            $this->_blog->year,
				            $this->_blog->month,
				            $this->_blog->day,
				            $this->_blog->ID);
		}
	}
	
	/**
	 * @post
	 * @Acl allow blog-writer
	 * @before findComment $commentID
	*/
	public function spam($commentID, $feedback)
	{
		$this->_comment->spamStatus = PropertySpamStatus::SPAM;
		MollomMessage::feedback($this->_comment->spamSessionID, $feedback);
		$this->_comment->save();
		$this->notice('Comment marked as spam');
		$this->redirect('blog/show',
				            $this->_blog->year,
				            $this->_blog->month,
				            $this->_blog->day,
				            $this->_blog->ID);
	}
	
	/**
	 * @post
	 * @Acl allow blog-writer
	 * @before findComment $commentID
	*/
	public function notspam($commentID)
	{
		$this->_comment->spamStatus = PropertySpamStatus::OK;
		$this->_comment->save();
		$this->notice('Comment unmarked as spam');
		$this->redirect('blog/show',
				            $this->_blog->year,
				            $this->_blog->month,
				            $this->_blog->day,
				            $this->_blog->ID);
	}
	
	protected function findBlog($ID, $date, $language)
	{
		list($y, $m, $d) = explode('-', $date);
		$this->_blog = Blog::getBlog($y, $m, $d, $ID, $language);
		return true;
	}
	
	protected function findComment($ID)
	{
		$this->_comment = BlogComment::get($ID);
		if ($this->_comment)
		{
			$year = date('Y', $this->_comment->blogDatePosted);
			$month = date('m', $this->_comment->blogDatePosted);
			$day = date('d', $this->_comment->blogDatePosted);
			$this->_blog = Blog::getBlog($year, $month, $day,
			                             $this->_comment->blogID,
			                             $this->_comment->blogLanguage);
			return true;
		}
		else
		{
			return false;
		}
	}
	
	protected function hasCommentAccess()
	{
		$usersession = UserSession::get();
		if ($usersession)
		{
			if ($usersession->username == $this->_comment->authorID)
			{
				return true;
			}
			else
			{
				return Acl::isAllowed($usersession->username, 'blog-writer');
			}
		}
		else
		{
			return false;
		}
	}
}

?>
