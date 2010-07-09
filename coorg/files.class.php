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
		return basename($this->_path);
	}
	
	public function path()
	{
		return dirname($this->_path);
	}
	
	public function uri()
	{
		return $this->_path;
	}
	
	public abstract function delete();
	
	protected function construct($file)
	{
		return $this->_manager->get($file);
	}
	
	public function fullpath()
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
		return CoOrgFile::getExtension($this->name());
	}
	
	public function delete()
	{
		unlink($this->fullpath());
	}
	
	public static function getExtension($name)
	{
		$dotPos = strrpos($name, '.');
		if ($dotPos != false) /* != and not !== because .hiddenfiles is not an extension */
		{
			return substr($name, $dotPos + 1);
		}
		else
		{
			return null;
		}
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
	private $_freeN = 0;

	public function __construct($basePath)
	{
		$this->_basePath = $basePath;
		if (!is_dir($this->_basePath))
		{
			mkdir($this->_basePath, 0777, true);
		}
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
	
	public function createFromUpload($upload, $destination = null, $base = null, $baseExtension = null)
	{
		if ($destination)
		{
			move_uploaded_file($upload, $this->_basePath.'/'.$destination);
			return $this->get($destination);
		}
		else
		{
			return $this->createFromUpload($upload, $this->findFree($base, $baseExtension));
		}
	}
	
	public function createFrom($source, $destination = null, $base = null, $baseExtension = null)
	{
		if ($destination)
		{
			copy($source, $this->_basePath.'/'.$destination);
			return $this->get($destination);
		}
		else
		{
			return $this->createFrom($source, $this->findFree($base, $baseExtension));
		}
	}
	
	public function findFree($base, $baseExtension = null)
	{
		$fileName = basename($base);
		$dir = dirname($base);
		if ($dir == '.')
		{
			$dir = '';
		}
		else
		{
			$dir .= '/';
		}
		
		if (!$baseExtension)
		{
			$dotPos = strrpos($fileName, '.');
			if ($dotPos != false) /* != and not !== because .hiddenfiles is not an extension */
			{
				$suffix = substr($fileName, $dotPos);
				$prefix = substr($fileName, 0, $dotPos);
			}
			else
			{
				$prefix = $fileName;
			}
		}
		else
		{
			$prefix = $fileName;
			$suffix = '.'.$baseExtension;
		}
		
		$name = $dir.$prefix.$suffix;
		while ($this->has($name))
		{
			$name = $dir.$prefix.$this->next().$suffix;
		}
		$this->_nextN = 0;
		return $name;
	}
	
	private function next()
	{
		if (defined('COORG_UNIT_TEST'))
		{
			$this->_nextN++;
			return $this->_nextN;
		}
		else
		{
			return rand();
		}
	}
	
	public function has($file)
	{
		if ($file == null) return false;
		return $this->get($file) != null;
	}
}

?>
