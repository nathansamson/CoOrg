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
	protected $_commentHelper;
	
	public function __construct()
	{
		$this->_commentHelper = new BlogCommentControllerHelper($this, 'BlogComment');
	}

	public function unmoderated()
	{
		$this->commentPager = Comment::getModerationQueue('BlogComment');
		$this->render('unmoderated');
	}

	/**
	 * @before findBlog $blogID $blogDate $blogLanguage
	*/
	public function save($blogID, $blogDate, $blogLanguage, $comment,
	                     $name, $email, $website)
	{
		if ($this->_blog->allowComments())
		{
			$this->_commentHelper->save('RE: ' . $this->_blog->title, 
				                        $comment, $this->_blog, 
				                        $name, $email, $website);
		}
		else
		{
			$this->error(t('Comments are not allowed for this blog'));
			Header::redirect('blog/show', $this->_blog->year,
		                              $this->_blog->month,
		                              $this->_blog->day,
		                              $this->_blog->ID);
		}
	}
	
	/**
	 * @before findComment $ID
	 * @Acl owns $:_comment
	 * @Acl owns $:_blog
	*/
	public function edit($ID)
	{
		$this->_commentHelper->show();
		$this->editComment = $this->_comment;
		$this->blog = $this->_blog;
		$this->render('show');
	}
	
	/**
	 * @before findComment $ID
	 * @Acl owns $:_comment
	 * @Acl owns $:_blog
	*/
	public function update($ID, $comment, $name, $email, $website)
	{
		$this->_commentHelper->update($this->_blog, $this->_comment, $comment,
		                              $name, $email, $website);
	}
	
	/**
	 * @before findComment $ID
	 * @Acl owns $:_comment
	 * @Acl owns $:_blog
	*/
	public function delete($ID)
	{
		$this->_commentHelper->delete($this->_blog, $this->_comment);
	}
	
	/**
	 * @post
	 * @before findComment $commentID
	 * @Acl owns $:_blog
	*/
	public function spam($commentID, $feedback)
	{
		$this->_commentHelper->spam($this->_blog, $this->_comment, $feedback);
	}
	
	/**
	 * @post
	 * @before findComment $commentID
	 * @Acl owns $:_blog
	*/
	public function notspam($commentID)
	{
		$this->_commentHelper->notspam($this->_blog, $this->_comment);
	}
	
	protected function findBlog($ID, $date, $language)
	{
		list($y, $m, $d) = explode('-', $date);
		$this->_blog = Blog::getBlog($y, $m, $d, $ID, $language);
		return $this->_blog;
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
}

class BlogCommentControllerHelper extends SpamCommentsControllerHelper
{
	protected $_configName = 'blog';

	public function __construct($controller)
	{
		parent::__construct($controller, 'BlogComment');
		$this->_commentRequests = new stdClass;
		$this->_commentRequests->save = 'blog/comment/save';
		$this->_commentRequests->edit = 'blog/comment/edit';
		$this->_commentRequests->update = 'blog/comment/update';
		$this->_commentRequests->delete = 'blog/comment/delete';
		$this->_commentRequests->spam = 'blog/comment/spam';
		$this->_commentRequests->notspam = 'blog/comment/notspam';
		$this->_commentRequests->queue = 'admin/blog/comment';
	}

	public function showURL($commentOn, $comment)
	{
		return CoOrg::createFullURL(array(
				        'blog/show',
				        $commentOn->year,
				        $commentOn->month,
				        $commentOn->day,
				        $commentOn->ID),
				        CoOrg::getDefaultLanguage(),
				        'comment'.$comment->ID
				        );
	}

	public function renderOnError($commentOn, $captha = null)
	{
		$this->blog = $commentOn;
		$this->spamOptions = self::spamOptions();
		$this->render('show');
	}
	
	public function redirectOnSuccess($commentOn)
	{
		Header::redirect('blog/show', $commentOn->year,
		                              $commentOn->month,
		                              $commentOn->day,
		                              $commentOn->ID);
	}
}

?>
