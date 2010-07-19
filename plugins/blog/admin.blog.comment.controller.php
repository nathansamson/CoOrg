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

/**
 * @Acl allow blog-moderator
*/
class AdminBlogCommentController extends AdminBaseController
{
	private $_comment;
	protected $_adminModule = 'BlogAdminModule';
	protected $_adminTab = 'BlogCommentsModerateAdminTab';
	protected $_helper;

	public function __construct()
	{
		$this->_helper = new AdminCommentsControllerHelper($this, 'BlogComment');
	}

	public function index($page = 1)
	{
		$qPager = Comment::getModerationQueue('BlogComment');
		$this->queue = $qPager->execute($page, 20);
		$this->qPager = $qPager;
		$this->spamOptions = CommentsControllerHelper::spamOptions();
		$this->render('admin/moderation-q');
	}
	
	/**
	 * @post
	 * @before findComment $commentID
	*/
	public function spam($commentID, $feedback, $from)
	{
		$this->_helper->spam($from, $this->_comment, $feedback);
	}
	
	/**
	 * @post
	 * @before findComment $commentID
	*/
	public function notspam($commentID, $from)
	{
		$this->_helper->notspam($from, $this->_comment);
	}
	
	protected function findComment($ID)
	{	
		$this->_comment = BlogComment::get($ID);
		return $this->_comment;
	}
}

?>
