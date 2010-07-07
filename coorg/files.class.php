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

abstract class CoOrgBaseFile
{
	private $_basePath;
	protected $_path;
	private $_manager;
	
	public function __construct($basePath, $path, $manager)
	{
		$this->_basePath = $basePath;
		$this->_path = $path;
		$this->_manager = $manager;
	}
	
	public function name()
	{
		return basename($this->fullpath());
	}
	
	public function path()
	{
		return dirname($this->_path);
	}
	
	public abstract function delete();
	
	protected function construct($file)
	{
		return $this->_manager->get($file);
	}
	
	protected function fullpath()
	{
		return $this->_basePath.'/'.$this->_path;
	}
}

class CoOrgFile extends CoOrgBaseFile
{
	public function content($newContent = null)
	{
		if ($newContent)
		{
			file_put_contents($this->fullpath(), $newContent);
		}
		else
		{
			return file_get_contents($this->fullpath());
		}
	}
	
	public function extension()
	{
		$dotPos = strrpos($this->name(), '.');
		if ($dotPos != false) /* != and not !== because .hiddenfiles is not an extension */
		{
			return substr($this->name(), $dotPos + 1);
		}
		else
		{
			return null;
		}
	}
	
	public function delete()
	{
		unlink($this->fullpath());
	}
}

class CoOrgDirectory extends CoOrgBaseFile
{
	public function files()
	{
		$files = scandir($this->fullpath());
		$f = array();
		foreach ($files as $file)
		{
			if ($file == '.' || $file == '..') continue;
			$f[] = $this->construct($this->path().'/'.$this->name().'/'.$file);
		}
		return $f;
	}
	
	public function delete()
	{
		foreach($this->files() as $file)
		{
			$file->delete();
		}
		
		rmdir($this->fullpath());
	}
}

class DataManager
{
	private $_basePath;

	public function __construct($basePath)
	{
		$this->_basePath = $basePath;
	}
	
	public function get($file)
	{
		if (is_file($this->_basePath.'/'.$file))
		{
			return new CoOrgFile($this->_basePath, $file, $this);
		}
		else if (is_dir($this->_basePath.'/'.$file))
		{
			return new CoOrgDirectory($this->_basePath, $file, $this);
		}
		else
		{
			return null;
		}
	}
}

?>
