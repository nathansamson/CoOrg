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

abstract class CommentsControllerHelper extends ControllerHelper
{
	private $_commentClass;
	private $_comment;
	protected $_commentRequests;

	public function __construct($controller, $commentClass)
	{
		parent::__construct($controller);
		$this->_commentClass = $commentClass;
	}
	
	public function show()
	{
		$comment = new $this->_commentClass;
		if (! UserSession::get())
		{
			$comment->anonAuthor = new AnonProfile;
		}
		$this->newComment = $comment;
		$this->newCommentCaptcha = MollomCaptcha::create();
		$this->spamOptions = self::spamOptions();
		$this->commentRequests = $this->_commentRequests;
	}
	
	public function save($subject, $body, $commentOn,
	                     $name = null, $email = null, $website = null)
	{
		$commentClass = $this->_commentClass;
		$comment = new $commentClass;
		$comment->title = $subject;
		$comment->comment = $body;
		
		if (UserSession::get())
		{
			$comment->author = UserSession::get()->user();
			$comment->spamStatus = PropertySpamStatus::OK;
		}
		else
		{
			$publicProfile = new AnonProfile;
			$publicProfile->name = $name;
			$publicProfile->email = $email;
			$publicProfile->website = $website;
			$publicProfile->IP = Session::IP();
			$comment->anonAuthor = $publicProfile;
			
			$this->checkSpamStatus($comment, $publicProfile);
		}
		if ($comment->spamStatus != PropertySpamStatus::SPAM)
		{
			try
			{
				$commentOn->comments[] = $comment;
				if ($comment->spamStatus == PropertySpamStatus::OK)
				{
					$this->notice(t('Your comment has been posted'));
				}
				$this->beforeSuccess($commentOn, $comment);
				$this->redirectOnSuccess($commentOn);
			}
			catch (ValidationException $e)
			{
				$this->error(t('Your comment was not posted'));
				$this->newComment = $comment;
				$this->commentRequests = $this->_commentRequests;
				$this->renderOnError($commentOn);
			}
		}
		else
		{
			$this->notice(t('Your comment has been marked as spam, and will not appear'));
			$this->redirectOnSuccess($commentOn);
		}
	}
	
	public function update($commentOn, $comment,
	                       $body, $name, $email, $website,
	                       $subject = null)
	{
		if ($subject)
		{
			$comment->subject = $subject;
		}
		$comment->comment = $body;
		if ($comment->anonAuthor)
		{
			$comment->anonAuthor->name = $name;
			$comment->anonAuthor->email = $email;
			$comment->anonAuthor->website = $website;
		}
		
		try
		{
			if ($comment->anonAuthor)
			{
				$comment->anonAuthor->save();
			}
			$comment->save();
			$this->notice('Updated comment');
			$this->redirectOnSuccess($commentOn);
		}
		catch (ValidationException $e)
		{
			$this->editComment = $comment;
			$this->newComment = new $this->_commentClass;
			$this->error(t('Could not save comment'));
			$this->commentRequests = $this->_commentRequests;
			$this->renderOnError($commentOn);
		}
	}
	
	public function delete($commentOn, $comment)
	{
		$comment->delete();
		if ($comment->anonAuthor)
		{
			$comment->anonAuthor->delete();
		}
		$this->notice(t('Deleted comment'));
		$this->redirectOnSuccess($commentOn);
	}
	
	public function spam($commentOn, $comment, $feedback)
	{
		$comment->spamStatus = PropertySpamStatus::SPAM;
		$comment->save();
		MollomMessage::feedback($comment->spamSessionID, $feedback);
		$this->notice(t('Comment marked as spam'));
		$this->redirectOnSuccess($commentOn);
	}
	
	public function notspam($commentOn, $comment)
	{
		$comment->spamStatus = PropertySpamStatus::OK;
		$comment->save();
		$this->notice(t('Comment unmarked as spam'));
		$this->redirectOnSuccess($commentOn);
	}
	
	abstract protected function renderOnError($commentOn);
	abstract protected function redirectOnSuccess($commentOn);
	abstract protected function checkSpamStatus($comment, $profile);
	abstract protected function beforeSuccess($commentOn, $comment);
	
	public static function spamOptions()
	{
		return array(
			'spam' => t('Spam'),
			'profanity' => t('Profanity'),
			'low-quality' => t('Low Quality'),
			'unwanted' => t('Annoying'));
	}
}

abstract class CaptchaCommentsControllerHelper extends CommentsControllerHelper
{
	//abstract protected function renderOnError($commentOn, $captcha = null);
	
	/**
	
	$comment->anonAuthor = $publicProfile;
			if (!$refreshCaptcha && !($audio || $image))
			{
				if (MollomCaptcha::check($captcha))
				{
					$comment->spamStatus = PropertySpamStatus::OK;
				}
				else
				{
					$this->newComment = $comment;
					$this->renderOnError($commentOn, MollomCaptcha::refresh());
					return;
				}
			}
			else
			{
				$this->newComment = $comment;
				if (!$refreshCaptcha)
				{
					if ($audio)
					{
						$this->renderOnError($commentOn, MollomCaptcha::refresh('audio'));
					}
					else
					{
						$this->renderOnError($commentOn, MollomCaptcha::refresh('image'));
					}
				}
				else
				{
					$this->renderOnError($commentOn, MollomCaptcha::refresh());
				}
			}
	
	*/
}

?>
