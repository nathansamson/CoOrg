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

	public function index($page = 1)
	{
		$qPager = Comment::getModerationQueue('BlogComment');
		$this->queue = $qPager->execute($page, 20);
		$this->qPager = $qPager;
		$this->spamOptions = BlogControllerHelper::spamOptions();
		$this->render('admin/moderation-q');
	}
	
	/**
	 * @post
	 * @before findComment $commentID
	*/
	public function spam($commentID, $feedback, $from)
	{
		$this->_comment->spamStatus = PropertySpamStatus::SPAM;
		$this->_comment->save();
		
		MollomMessage::feedback($this->_comment->spamSessionID, $feedback);
		
		$this->notice(t('Comment marked as spam'));
		$this->redirect($from);
	}
	
	/**
	 * @post
	 * @before findComment $commentID
	*/
	public function notspam($commentID, $from)
	{
		$this->_comment->spamStatus = PropertySpamStatus::OK;
		$this->_comment->save();
		
		$this->notice(t('Comment not marked as spam'));
		$this->redirect($from);
	}
	
	protected function findComment($ID)
	{	
		$this->_comment = BlogComment::get($ID);
		return $this->_comment;
	}
}

?>
