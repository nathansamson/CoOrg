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

class Config
{
	private $_config;
	private $_file;

	public function __construct($filename)
	{
		$this->_file = $filename;
		$_config = array();
		include $filename;
		$this->_config = $_config;
	}
	
	public function save()
	{
		$output = "<?php\n";
		
		foreach ($this->_config as $key => $value)
		{
			$output .= '$_config[\'' . $key . '\'] = ' . $this->normalize($value) . ";\n";
		}
		
		$output .= "?>";
		
		if (is_writable($this->_file))
		{
			file_put_contents($this->_file, $output);
		}
		else
		{
			throw new ConfigFileNotWritableException($this->_file);
		}
	}
	
	public function get($key)
	{
		if ($this->has($key))
		{
			return $this->_config[$key];
		}
		else
		{
			return null;
		}
	}
	
	public function set($key, $value)
	{
		$this->_config[$key] = $value;
	}
	
	public function has($key)
	{
		return array_key_exists($key, $this->_config);
	}

	private function normalize($value)
	{
		if (is_array($value))
		{
			$s = 'array(';
			$elements = array();
			foreach ($value as $key => $element)
			{
				$elements[] = $this->normalize($key) .' => '. 
				              $this->normalize($element);
			}
			$s .= implode(',', $elements);
			$s .= ')';
			return $s;
		}
		else if (is_bool($value))
		{
			return ($value ? 'true' : 'false');
		}
		else if (is_int($value) || is_float($value))
		{
			return $value;
		}
		else if ($value === null)
		{
			return 'null';
		}
		else
		{
			$value = str_replace('\\', '\\\\', $value);
			$value = str_replace('\'', '\\\'', $value);
			return '\''.$value.'\'';
		}
	}
}

class ConfigFileNotWritableException extends Exception
{
	public function __construct($file)
	{
		parent::__construct('The config file ' . $file . ' is not writable.');
	}
}

?>
