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

class FileUpload implements IFileUpload
{
	private $_upload;
	private $_persist;
	private $_tempManager;
	
	private $_storeName;
	private $_storeManager;
	
	private $_invalid = false;
	
	private $_name;

	public function __construct($name, $tempManager)
	{
		$this->_tempManager = $tempManager;
		$this->_name = $name;
		if (array_key_exists($name, $_FILES) && $_FILES[$name]['error'] != UPLOAD_ERR_NO_FILE)
		{
			$this->_upload = $_FILES[$name];
		}
		else if ($tempManager->has($this->persistFile()))
		{
			$this->_persist = $tempManager->get($this->persistFile());
		}
	}
	
	public function error()
	{
		if ($this->_upload)
		{
			$this->_upload['error'];
		}
		else if ($this->_persist)
		{
			return UPLOAD_ERR_OK;
		}
		else
		{
			return UPLOAD_ERR_NO_FILE;
		}
	}
	
	public function temppath()
	{
		if ($this->_persist)
		{
			return $this->_persist->fullpath();
		}
		else if ($this->_upload && !$this->_invalid)
		{
			return $this->_upload['tmp_name'];
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
	
	public function persist()
	{
		if ($this->_upload && $this->_upload['error'] == UPLOAD_ERR_OK && !$this->_invalid)
		{
			if ($this->findOldPersist())
			{
				$this->_persist->delete();
				$this->_persist = null;
			}
			$this->_persist = $this->_tempManager->createFromUpload($this->temppath(), null, $this->_upload['name']);
			Session::set('.session-uploads/'.$this->_name, $this->_persist->uri());
		}
	}
	
	public function setStoreName($path)
	{
		$this->_storeName = $path;
	}
	
	public function setAutoStore($baseName, $extension = null)
	{
		$this->_storeName = $this->_storeManager->findFree($baseName, $extension);
	}
	
	public function setStoreManager($manager)
	{
		$this->_storeManager = $manager;
	}
	
	public function store()
	{
		if ($this->findOldPersist())
		{
			if ($this->_upload && !$this->_invalid)
			{
				$this->_storeManager->createFromUpload($this->_upload['tmp_name'], $this->_storeName);
			}
			else
			{
				$this->_storeManager->createFrom($this->_persist->fullpath(), $this->_storeName);
			}
			$this->_persist->delete();
			$this->_persist = null;
		}
		else if ($this->_upload)
		{
			$this->_storeManager->createFromUpload($this->temppath(), $this->_storeName);
		}
		else
		{
		}
	}
	
	public function isValid()
	{
		if ($this->_upload && $this->_upload['error'] == UPLOAD_ERR_OK && !$this->_invalid)
		{
			return true;
		}
		else if ($this->_persist)
		{
			return true;
		}
		return false;
	}
	
	public function invalidUpload()
	{
		$this->_invalid = true;
		$this->findOldPersist();
	}
	
	private function findOldPersist()
	{
		if ($this->_persist)
		{
			return true;
		}
		if ($this->_tempManager->has($this->persistFile()))
		{
			$this->_persist = $this->_tempManager->get($this->persistFile());
			return true;
		}
		return false;
	}
	
	private function persistFile()
	{
		if (Session::has('.session-uploads/'.$this->_name))
		{
			return Session::get('.session-uploads/'.$this->_name);
		}
		else
		{
			return null;
		}
	}
}

?>
