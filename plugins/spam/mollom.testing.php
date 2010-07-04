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

class OutdatedServerListException extends Exception {}
class NoServerListException extends Exception {}
class KeyNotSetException extends Exception {}
class InternalException extends Exception {}

class Mollom
{
	private static $_publicKey;
	private static $_privateKey;
	private static $_serverList;
	
	private static $_verified = false;
	
	// Only available in test
	public static function clearAll()
	{
		self::$_verified = false;
		self::clear();
	}
	
	// Only available in test
	public static function clear()
	{
		self::$_publicKey = null;
		self::$_privateKey = null;
		self::$_serverList = null;
	}

	public static function setPublicKey($key)
	{
		self::$_publicKey = $key;
	}
	
	public static function setPrivateKey($key)
	{
		self::$_privateKey = $key;
	}
	
	public static function setServerList($list)
	{
		self::$_serverList = $list;
	}
	
	public static function getImageCaptcha($sessID = null)
	{
		self::check();
		if ($sessID == null)
		{
			self::$_verified = false;
			return array('session_id' => 'new-sessionid', 'url' => 'mollom.com/new-captcha');
		}
		else
		{
			if (self::$_verified)
			{
				$c = array('session_id' => $sessID, 'url' => 'mollom.com/invalid-captcha');
			}
			else
			{
				$c = array('session_id' => $sessID, 'url' => 'mollom.com/refresh-captcha');
			}
			self::$_verified = false;
			return $c;
		}
	}
	
	public static function getAudioCaptcha($sessID = null)
	{
		self::check();
		if ($sessID == null)
		{
			self::$_verified = false;
			return array('session_id' => 'new-sessionid', 'url' => 'mollom.com/new-audio-captcha');
		}
		else
		{
			if (self::$_verified)
			{
				$c = array('session_id' => $sessID, 'url' => 'mollom.com/invalid-audio-captcha');
			}
			else
			{
				$c = array('session_id' => $sessID, 'url' => 'mollom.com/refresh-audio-captcha');
			}
			self::$_verified = false;
			return $c;
		}
	}
	
	public static function checkCaptcha($sessID, $code)
	{
		self::check();
		if ($sessID != 'new-sessionid') return false;
		self::$_verified = true;
		if ($code == 'invalid')
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public static function checkContent($sessID = '', $title = '', $body = '',
	        $authorName = '', $authorURL = '', $authorEmail = '',
	        $authorOpenID = '', $authorID = '')
	{
		self::check();
		if (! $sessID) $sessID = 'new-session';
		if (strpos($body, 'SPAM') !== false)
		{
			$spam = 'spam';
		}
		else if (strpos($body, 'UNKNOWN') !== false)
		{
			$spam = 'unsure';
		}
		else
		{
			$spam = 'ham';
		}
		return array('spam' => $spam, 'session_id' => $sessID, 'quality' => 0.9);
	}
	
	public static function sendFeedback($sessID, $feedback)
	{
		self::check();
		return true;
	}
	
	public static function getServerList()
	{
		self::$_serverList = array('valid-server-list');
		return array('retrieved-list');
	}
	
	private static function check()
	{
		if (self::$_publicKey != 'valid-pub-key' ||
		    self::$_privateKey != 'valid-priv-key')
		{
			if (self::$_publicKey == '' || self::$_privateKey == '')
			{
				throw new KeyNotSetException('');
			}
			throw new InternalException('[Code 1000 probably]');
		}
		if (self::$_serverList == array())
		{
			throw new NoServerListException('');
		}
		else if (self::$_serverList != array('valid-server-list'))
		{
			throw new OutdatedServerListException('');
		}
	}
}

?>
