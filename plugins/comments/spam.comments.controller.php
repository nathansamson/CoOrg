<?php

abstract class SpamCommentsControllerHelper extends CommentsControllerHelper
{	
	protected $_configName;
	abstract protected function showURL($commentOn, $comment);

	protected function checkSpamStatus($comment, $profile)
	{
		$message = new MollomMessage;
		$message->title = $comment->title;
		$message->body = $comment->comment;
		$message->authorName = $profile->name;
		$message->authorEmail = $profile->email;
		$message->authorWebsite = $profile->website;
		
		$comment->spamStatus = $message->check();
		$comment->spamSessionID = Session::get('mollom/sessionid');
	}
	
	protected function beforeSuccess($commentOn, $comment)
	{
		if ($comment->spamStatus == PropertySpamStatus::UNKNOWN)
		{
			$this->notice('Your comment will be moderated, and will appear on a later time on the site');
			
			$config = CoOrg::config();
			$secondsSinceLastMail = time() - $config->get($this->_configName . '/last-moderation-mail');
			$minSecondsBetweenMails = 60*60*24*$config->get($this->_configName . '/moderation-time');
			if ($secondsSinceLastMail > $minSecondsBetweenMails)
			{
				$config->set($this->_configName . '/last-moderation-mail', time());
				$config->save();
				$receiver = $config->get($this->_configName . '/moderation-email');
				$mail = $this->mail();
				$mail->title = $comment->title;
				$mail->body = $comment->comment;
				$mail->date = $comment->timePosted;
				$mail->messageURL = $this->showURL($commentOn, $comment);
				$mail->moderationURL = CoOrg::createFullURL(array('admin/comment/queue'));
				$mail->totalModerationQueue = Comment::moderationQueueLength();
				$site = $config->get('site/title');
				$mail->site = $site;
				$mail->to($receiver)
				     ->subject(t('%site: New comment to moderate', array('site' => $site)))
				     ->send(Controller::getTemplatePath('mails/newcomment', 'comments'));
			}
		}
	}
}

?>
