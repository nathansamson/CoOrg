<?php

abstract class Extracter
{
	protected $_file;

	public function __construct($file)
	{
		$this->_file = $file;
	}

	abstract public function extract();

	public static function create($file)
	{
		$extension = substr($file, strrpos($file, '.') + 1);
		if ($extension == 'php')
		{
			return new PHPExtracter($file);
		}
		else if ($extension == 'tpl')
		{
			return new TPLExtracter($file);
		}
		else
		{
			return new NullExtracter();
		}
	}
}

class NullExtracter extends Extracter
{
	public function __construct() {}
	
	public function extract() {return array();}
}

class TPLExtracter extends Extracter
{
	public function extract()
	{
		$c = file_get_contents($this->_file);
		
		preg_match_all('/\{\'(.*)\'\|_(:.*)*\}/', $c, $matches);
		preg_match_all('/\{.* label="(.*)".*\}/U', $c, $smatches);
		
		global $search;
		if ($search !== null)
		{
			if (in_array($search, $matches[1]))
			{
				echo $this->_file . "\n";
			}
		
			if (in_array($search, $smatches[1]))
			{
				echo $this->_file . "\n";
			}
		}
		
		return array_merge($matches[1], $smatches[1]);
	}
}

class PHPExtracter extends Extracter
{
	public function extract()
	{
		$c = file_get_contents($this->_file);
		
		preg_match_all('/[^a-zA-Z0-9]t\(([\'"])(.*)[\'"][\),]/U', $c, $matches);
		
		$strings = array();
		foreach ($matches[2] as $k=>$m)
		{
			if ($matches[1][$k] == '"')
			{
				$m = str_replace('\\"', '\"', $m);
				$m = str_replace('\n', "\n", $m);
			}
			else
			{
				$m = str_replace('\\\'', '\'', $m);
			}

			$strings[] = $m;
			
			global $search;
			if ($search == $m)
			{
				echo $this->_file . "\n";
			}
		}
		return $strings;
	}
}

class StringDictionary
{
	private $_dict;
	private $_file;
	private $_context;

	public function __construct($dir, $language, $context = null)
	{
		$_ = array();
		$file = $dir.'/'.$language.'.lang.php';
		$this->_file = $file;
		if (is_file($file)) include $file;
		$this->_dict = $_;
		$this->_context = $context;
	}
	
	public function update($strings)
	{
		$this->filterContexts($strings);
		$this->removeOld($strings);
		$this->addNew($strings);
	}
	
	public function save()
	{
		$o = "<?php\n";
		foreach ($this->_dict as $key => $trans)
		{
			$skey = str_replace('\'', '\\\'', $key);
			$strans = str_replace('\'', '\\\'', $trans);
			$o .= '$_[\''.$skey.'\'] = \''.$strans."';\n";
		}
		$o .= '?>';
		
		var_dump($this->_file);
		file_put_contents($this->_file, $o);
	}
	
	private function removeOld(&$strings)
	{
		foreach ($this->_dict as $original => $translated)
		{
			$p = array_search($original, $strings);
			if ($p !== false)
			{
				unset($strings[$p]);
			}
			else
			{
				unset($this->_dict[$original]);
			}
		}
	}
	
	private function addNew($strings)
	{
		foreach ($strings as $string)
		{
			if (!array_key_exists($string, $this->_dict))
			{
				$this->_dict[$string] = '';
			}
		}
	}
	
	private function filterContexts(&$strings)
	{
		global $all;
		$strings = array_unique($strings);
		foreach ($strings as $key => &$string)
		{
			if (preg_match('/^([a-zA-Z0-9]*)\|(.*)/', $string, $matches))
			{
				$context  = $matches[1];
				$string = $matches[2];
				if ($context != $this->_context)
				{
					unset($strings[$key]);
				}
			}
			else
			{
				if (array_key_exists($string, $all))
				{
					$all[$string][] = $this->_context;
				}
				else
				{
					$all[$string] = array($this->_context);
				}
			}
		}
	}
}

class Scanner
{
	private $_dict;
	private $_dir;
	
	public function __construct($dir, $dict)
	{
		$this->_dict = $dict;
		$this->_dir = $dir;
	}
	
	public function scan()
	{
		$files = $this->generateFileList($this->_dir);
		
		$strings = array();
		foreach ($files as $file)
		{
			$s = Extracter::create($file)->extract();
		
			$strings = array_merge($strings, $s);
		}
		
		$this->_dict->update($strings);
		$this->_dict->save();
	}
	
	private function generateFileList($dir)
	{
		$files = array();
		foreach (scandir($dir) as $sub)
		{
			if ($sub[0] == '.') {continue;}
			if ($sub == 'tests') {continue;}
			if (is_file($dir.'/'.$sub))
			{
				$files[] = $dir.'/'.$sub;
			}
			else if (is_dir($dir.'/'.$sub))
			{
				$files = array_merge($files, $this->generateFileList($dir.'/'.$sub));
			}
		}
		return $files;
	}
}

$all = array();
$language = $_SERVER['argv'][1];
$search = '';
for ($i = 2; $i < $_SERVER['argc']; $i++)
{
	$scanIt = $_SERVER['argv'][$i];

	if ($scanIt == 'coorg')
	{
		$sc = new Scanner('coorg/', new StringDictionary('coorg/lang', $language, 'coorg'));
	}
	else
	{
		$prefix = substr($scanIt, 0, strpos($scanIt, ':'));
		$suffix = substr($scanIt, strpos($scanIt, ':') + 1);
	
		$sc = new Scanner($prefix.'/'.$suffix, new StringDictionary($prefix.'/'.$suffix.'/lang', $language, $suffix));
	}
	$sc->scan();
}

foreach ($all as $string => $contexts)
{
	if (count($contexts) > 1)
	{
		echo "'$string' in contexts: " . implode(', ', $contexts)."\n\n";
	}
}
?>
