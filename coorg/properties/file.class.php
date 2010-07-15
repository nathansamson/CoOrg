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

class PropertyFile extends Property
{
	private $_dataPath;

	public function __construct($name, $dataPath)
	{
		parent::__construct($name);
		$this->_dataPath = $dataPath;
	}
	
	public function set($val)
	{
		parent::set($val);
		if ($val instanceof FileUpload)
		{
			$this->_value->setStoreManager(CoOrg::getDataManager($this->_dataPath));
		}
	}
	
	public function get()
	{
		if (is_string($this->_value))
		{
			$dataP = CoOrg::getDataPath($this->_dataPath);
			return CoOrg::config()->get('path').$dataP.'/'.$this->_value;
		}
		else if ($this->_value && $this->_value->isValid())
		{
			return CoOrg::config()->get('path').$this->_value->temppath();
		}
		else if ($this->_oldValue)
		{
			$dataP = CoOrg::getDataPath($this->_dataPath);
			return CoOrg::config()->get('path').$dataP.'/'.$this->_oldValue;
		}
		else
		{
			return null;
		}
	}
	
	public function raw()
	{
		// Abusing raw();
		return $this->get();
	}
	
	public function postsave()
	{
		if ($this->_value instanceof FileUpload && $this->_value->error() == UPLOAD_ERR_OK)
		{
			$dataM = CoOrg::getDataManager($this->_dataPath);
			if ($this->_oldValue)
			{
				$file = $dataM->get($this->_oldValue);
				if ($file) $file->delete();
			}
			$this->_value->store();
		}
		parent::postsave();
	}
	
	public function changed()
	{
		if ($this->_value && !is_string($this->_value))
		{
			return true;
		}
		else
		{
			return parent::changed();
		}
	}
	
	public function extension()
	{
		return CoOrgFile::getExtension(basename($this->get()));
	}
	
	public function validate($for)
	{
		if ($this->isrequired($for) && $this->_value == null)
		{
			$this->error(t('You have to upload a file'));
			return false;
		}
		else if ($this->isrequired($for) && $this->_value instanceof FileUpload)
		{
			if ($this->_value->error() == UPLOAD_ERR_NO_FILE)
			{
				// See if there is a session or an old file
				if (!$this->_oldValue && !$this->_value->temppath())
				{
					$this->error(t('You have to upload a file'));
					return false;
				}
			}
		}
		
		if (is_string($this->_value) || $this->_value == '')
		{
			return true;
		}
		else
		{
			if ($this->_value->error() == UPLOAD_ERR_OK)
			{
				return true;
			}
			else if ($this->_value->error() == UPLOAD_ERR_NO_FILE)
			{
				return true;
			}
			else if ($this->_value->error() == UPLOAD_ERR_PARTIAL)
			{
				$this->error(t('The file transfer was not complete, please try again'));
				return false;
			}
			else if ($this->_value->error() == UPLOAD_ERR_INI_SIZE || $this->_value->error() == UPLOAD_ERR_FORM_SIZE)
			{
				$this->error(t('The filesize is too large'));
				return false;
			}
			else
			{
				$this->error(t('The file upload failed, please try again'));
				return false;
			}
		}
	}
	
	protected function toDB($value)
	{
		if ($value instanceof FileUpload && $value->error() != UPLOAD_ERR_NO_FILE)
		{
			return $value->storedname();
		}
		else if ($value instanceof FileUpload)
		{
			if ($value != $this->_oldValue)
			{
				return $this->toDB($this->_oldValue);
			}
		}
		else
		{
			return $value;
		}
	}
}

class PropertyImage extends PropertyFile
{
	private $_maxX;
	private $_maxY;

	public function __construct($name, $dataPath, $maxX = 99999999, $maxY = 99999999)
	{
		parent::__construct($name, $dataPath);
		$this->_maxX = $maxX;
		$this->_maxY = $maxY;
	}

	public function validate($for)
	{
		$v = parent::validate($for);
		
		if ($v == true)
		{
			if ($this->_value instanceof FileUpload && $this->_value->error() == UPLOAD_ERR_OK)
			{
				$size = @getimagesize($this->_value->temppath());
				if ($size == false)
				{
					$this->_value->invalidUpload();
					$this->error(t('This is not a valid image file (only png, jpeg and gif are supported)'));
					return false;
				}
				else if (!self::isSupported($size[2]))
				{
					$this->_value->invalidUpload();
					$this->error(t('This is not a valid image file (only png, jpeg and gif are supported)'));
					return false;
				}
				else if ($size[0] > $this->_maxX || $size[1] > $this->_maxY)
				{
					$this->_value->invalidUpload();
					$this->error(t('The file resolution is too large, maximum is %x x %y', array('x'=>$this->_maxX, 'y'=>$this->_maxY)));
					return false;
				}
			}
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function extension()
	{
		$info = @getimagesize($this->_value->temppath());
		if ($info != false)
		{
			return image_type_to_extension($info[2], false);
		}
	}
	
	public static function isSupported($type)
	{
		return (($type == IMAGETYPE_PNG) || ($type == IMAGETYPE_JPEG) ||
		        ($type == IMAGETYPE_JPEG2000) || ($type == IMAGETYPE_GIF));
	}
}

?>
