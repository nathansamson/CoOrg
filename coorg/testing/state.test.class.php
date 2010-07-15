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

class Cookies implements ICookies
{

	public static function has($key)
	{
	}
	
	public static function get($key)
	{
	}
	
	public static function set($key, $value, $lifeTime = 0)
	{
	}
	
	public static function delete($key)
	{
	}
}

class MockFileUpload implements IFileUpload
{
	private $_storeManager;
	private $_storeName;
	private $_upload;
	private $_session;
	private $_invalid = false;

	public function __construct($name, $size, $error, $session = null)
	{
		// Go look in the session
		if ($session)
		{
			$this->_session = $session;
		}
		if ($error != UPLOAD_ERR_NO_FILE)
		{
			$this->_upload = array($name, $size, $error);
		}
	}
	
	public function error()
	{
		if ($this->_upload)
		{
			return $this->_upload[2];
		}
		else
		{
			return UPLOAD_ERR_NO_FILE;
		}
	}
	
	public function temppath()
	{
		if ($this->_upload && $this->_upload[2] == UPLOAD_ERR_OK && !$this->_invalid)
		{
			return $this->_upload[0];
		}
		else if ($this->_session)
		{
			return 'data/.session/'.$this->_session;
		}
		else
		{
			return null;
		}
	}
	
	public function storedname()
	{
		return $this->_storeName;
	}
	
	public function setStoreName($path)
	{
		$this->_storeName = $path;
	}
	
	public function setAutoStore($baseName, $extension = null)
	{
		$this->_storeName = $baseName . '.' . $extension;
	}
	
	public function setStoreManager($manager)
	{
		$this->_storeManager = $manager;
	}
	
	public function persist()
	{
	}
	
	public function store()
	{
	}
	
	public function isValid()
	{
		if ($this->_upload && $this->_upload[2] == UPLOAD_ERR_OK && !$this->_invalid)
		{
			return true;
		}
		else if ($this->_session)
		{
			return true;
		}
		else
		{	
			return false;
		}
	}
	
	public function invalidUpload()
	{
		$this->_invalid = true;
	}
}


class Session implements ISession
{
	private static $_keys = array();
	private static $_uploads = array();
	public static $referrer = '';
	public static $site =  '';

	public static function has($key)
	{
		return array_key_exists($key, self::$_keys);
	}
	
	public static function get($key)
	{
		return self::$_keys[$key];
	}
	
	public static function set($key, $value)
	{
		self::$_keys[$key] = $value;
	}
	
	public static function delete($key)
	{
		unset(self::$_keys[$key]);
	}
	
	public static function destroy()
	{
		self::$_keys = array();
	}
	
	public static function IP()
	{
		return '0.0.0.0';
	}
	
	public static function getReferrer()
	{
		return self::$referrer;
	}
	
	public static function getSite()
	{
		return self::$site;
	}
	
	public static function setFileUpload($name, $filename, $filesize, $error)
	{
		self::$_uploads[$name] = array('file' => $filename,
		                               'filesize' => $filesize,
		                               'error' => $error);
	}
	
	public static function getFileUpload($name)
	{
		if (array_key_exists($name, self::$_uploads))
		{
			return new MockFileUpload(self::$_uploads[$name]['file'],
				                  self::$_uploads[$name]['filesize'],
				                  self::$_uploads[$name]['error']);
		}
		else
		{
			return new MockFileUpload(null, null, UPLOAD_ERR_NO_FILE);
		}
	}
}

?>
