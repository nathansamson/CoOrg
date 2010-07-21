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


abstract class CaptchaCommentsControllerHelper extends CommentsControllerHelper
{
	private $_captcha = null;
	private $_captchaResponse = null;
	
	public function show()
	{
		parent::show();
		if (!UserSession::get()) $this->commentCaptcha = MollomCaptcha::create();
	}
	
	public function setCaptchaResponse($response)
	{
		$this->_captchaResponse = $response;
	}
	
	protected function beforeSuccess($commentOn, $comment)
	{
	}
	
	protected function renderErrorSave($commentOn)
	{
		if ($this->_captcha)
		{
			$this->commentCaptcha = $this->_captcha;
		}
		else if (!UserSession::get())
		{
			$this->commentCaptcha = Mollom::refresh();
		}
		parent::renderErrorSave($commentOn);
	}
	
	public function refreshCaptcha($refresh = null, $audio = null, $image = null)
	{
		if (!$refresh)
		{
			if ($audio)
			{
				$this->_captcha = MollomCaptcha::refresh('audio');
			}
			else if ($image)
			{
				$this->_captcha = MollomCaptcha::refresh('image');
			}
		}
		else
		{
			$this->_captcha = MollomCaptcha::refresh();
		}
	}
	
	protected function checkSpamStatus($comment, $publicProfile)
	{
		if (!$this->_captcha)
		{
			$this->_captcha = MollomCaptcha::check($this->_captchaResponse);
			if ($this->_captcha)
			{
				return false;
			}
			else
			{
				$comment->spamStatus = PropertySpamStatus::OK;
			}
		}
		else
		{
			$this->commentCaptcha = $this->_captcha;
			return false;
		}
	}
}

?>
