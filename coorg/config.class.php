<?php

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
			$output .= '$_config[\'' . $key . '\'] = ' . $this->normalize($value) . ';';
		}
		
		$output .= "?>\n";
		
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
			foreach ($value as $element)
			{
				$elements[] = $this->normalize($element);
			}
			$s .= implode(',', $elements);
			$s .= ')';
			return $s;
		}
		else if (is_bool($value))
		{
			return ($value ? 'true' : 'false');
		}
		else if ($value == null)
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
