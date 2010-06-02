<?php

require_once 'coorg/properties/property.interface.php';
require_once 'coorg/properties/string.class.php';
require_once 'coorg/properties/email.class.php';
require_once 'coorg/properties/integer.class.php';
require_once 'coorg/properties/date.class.php';
require_once 'coorg/properties/bool.class.php';

class Model
{
	private static $_modelInfo = array();

	private $_properties = array();
	
	protected function __construct()
	{
		$class = get_class($this);
		if (!array_key_exists($class, self::$_modelInfo))
		{
			self::$_modelInfo[$class] = array('properties' => self::parseProperties($class));
		}

		foreach (self::$_modelInfo[$class]['properties'] as $name=>$propertyInfo)
		{
			$info = $propertyInfo;
			$info['property'] = clone $propertyInfo['property'];
			$this->_properties[$name] = $info;
		}
	}
	
	public function __get($key)
	{
		$uPos = strrpos($key, '_');
		if ($uPos !== false)
		{
			$fnc = substr($key, $uPos + 1);
			$name = substr($key, 0, $uPos);
			if ($fnc == 'error')
			{
				$fnc = 'errors';
			}
		}
		else
		{
			$name = $key;
			$fnc = 'get';
		}
		
		if (array_key_exists($name, $this->_properties))
		{
			$propertyInfo = $this->_properties[$name];

			if ($propertyInfo['protected'] || 
			    ($propertyInfo['writeonly'] && $fnc == 'get'))
			{
				// Check if calling function inherits from Model.
				$bt = debug_backtrace();
				if (! $bt[1]['object'] instanceof $propertyInfo['class'])
				{
					throw new Exception('You are not allowed to do this.');
				}
			}
			
			return $propertyInfo['property']->$fnc();
		}
		else
		{
			throw new Exception('Attribute not found.');
		}
	}
	
	public function __set($key, $value)
	{
		$uPos = strrpos($key, '_');
		if ($uPos !== false)
		{
			$fnc = substr($key, $uPos + 1);
			$name = substr($key, 0, $uPos);
		}
		else
		{
			$name = $key;
			$fnc = 'set';
		}
		
		if (array_key_exists($name, $this->_properties))
		{
			// Fix Access
			$this->_properties[$name]['property']->$fnc($value);
		}
		else
		{
			throw new Exception('Attribute not found.');
		}
	}
	
	protected function validate($type)
	{
		$error = false;
		foreach ($this->_properties as $k=>$p)
		{
			if (!$p['property']->validate($type))
			{
				$error = true;
			}
		}
		if ($error) throw new ValidationException($this);
	}
	
	protected function dbproperties()
	{
		return array_filter($this->_properties, array('Model', 'filterDB'));
	}
	
	protected function primaries()
	{
		return array_filter($this->_properties, array('Model', 'filterPrimary'));
	}
	
	protected function properties()
	{
		return $this->_properties;
	}
	
	protected function autoincrements()
	{
		$ais = array();
		foreach ($this->_properties as $k=>$p)
		{
			if ($p['auto-increment'])
			{
				$ais[$k] = $p;
			}
		}
		return $ais;
	}

	static private function parseProperties($class)
	{
		$propertyInfo = array();
		$reflClass = new ReflectionClass($class);
		$docComment = $reflClass->getDocComment();
	
		$lines = explode("\n", $docComment);
		foreach ($lines as $line)
		{
			$line = trim($line);
			if ($line == '') continue;
			if ($line[0] == '*')
			{
				$line = trim(substr($line, 1));
			}
			$pCommand = substr($line, 0, strlen('@property'));
			$pDesc = substr($line, strlen('@property'));
			if ($pCommand == '@property')
			{
				$primary = false;
				$writeonly = false;
				$protected = false;
				$autoincrement = false;
				
				$desc = explode(';', $pDesc, 3);
				$descFirst = explode(' ', trim($desc[count($desc)-2]), 2);
				if (count($desc) == 3)
				{
					$options = explode(' ', $desc[0]);
				}
				else
				{
					$options = array();
				}
			
				$extras = explode(' ', $desc[count($desc)-1]);
				$name = $descFirst[count($descFirst)-2];
				$construct = $descFirst[count($descFirst) - 1];
				for ($i = 0; $i < count($options); $i++)
				{
					$option = $options[$i];
					if ($option == 'primary' || $option == 'writeonly' ||
					    $option == 'protected' || $option == 'autoincrement')
					{
						$$option = true;
					}
				}

				$p = eval('return new Property' . $construct . ';');
				foreach ($extras as $e)
				{
					$e = trim($e);
					if ($e == '') continue;
					if ($e[strlen($e)-1] != ')')
					{
						$e .= '()';
					}
					eval('$p->'.$e.';');
				}

				$propertyInfo[$name] = array('property' => $p,
				                             'primary' => $primary,
				                             'writeonly' => $writeonly,
				                             'protected' => $protected,
				                             'auto-increment' => $autoincrement,
				                             'class' => $class);
			}
		}
		return $propertyInfo;
	}

	static public function filterDB($f)
	{
		return !$f['writeonly'];
	}

	static public function filterPrimary($f)
	{
		return $f['primary'];
	}
}

class DBModel extends Model
{
	protected $_saved = false;

	public function save()
	{
		if ($this->_saved)
		{
			$this->beforeUpdate();
			$this->validate('update');
			$this->update();
			$this->setSaved();
		}
		else
		{
			$this->beforeInsert();
			$this->validate('insert');
			$this->insert();
			$this->setSaved();
		}
	}

	protected function update()
	{
		$qs = 'UPDATE ' . $this->tableName() . ' SET ';
		$sets = array();
		$properties = array();
		foreach ($this->dbproperties() as $k => $p)
		{
			if ($p['property']->changed())
			{
				if ($p['property']->db() != null)
				{
					$properties[] = $k;
					$sets[] = $k .'=:' . $k; 
				}
				else
				{
					$sets[] = $k . ' = NULL';
				}
			}
		}
		if ($sets == array())
		{
			return; // Nothing to do.
		}
		$qs .= implode(', ', $sets);
		
		$qs .= ' WHERE ';
		
		$pwheres = array();
		foreach ($this->primaries() as $pk => $pp)
		{
			$pwheres[] = $pk . '=:p'.$pk;
		}
		$qs .= implode(' AND ', $pwheres);
		
		$q = DB::prepare($qs);
		foreach ($properties as $k)
		{
			$db = $k.'_db';
			$q->bindValue(':'.$k, $this->$db);
		}
		foreach ($this->primaries() as $pk => $pp)
		{
			$q->bindValue(':p'.$pk, $pp['property']->old());
		}
		$q->execute();
	}

	protected function insert()
	{
		$properties = array();
		
		foreach ($this->dbproperties() as $k => $p)
		{
			$db = $k.'_db';
			if ($this->$db != null)
			{
				$properties[] = $k;
			}
		}
	
		$qs = 'INSERT INTO ' . $this->tableName() . ' (';
		$qs .= implode(',', $properties) . ')';
		$qs .= ' VALUES(:'.implode(',:', $properties). ')';
		
		$q = DB::prepare($qs);
		foreach ($properties as $k=>$p)
		{
			$db = $p.'_db';
			$q->bindValue(':'.$p, $this->$db);
		}
		$q->execute();
		foreach ($this->autoincrements() as $k => $p)
		{
			$this->$k = DB::lastInsertID($this->tableName());
		}
	}
	
	public function delete()
	{
		$qs = 'DELETE FROM ' . $this->tableName() . ' WHERE ';
		
		$pwheres = array();
		foreach ($this->primaries() as $pk => $pp)
		{
			$pwheres[] = $pk . '=:p'.$pk;
		}
		$qs .= implode(' AND ', $pwheres);
		
		$q = DB::prepare($qs);
		foreach ($this->primaries() as $pk => $pp)
		{
			$q->bindValue(':p'.$pk, $pp['property']->old());
		}
		$q->execute();
	}
	
	protected function tableName()
	{
		if (strpos(get_class($this), 'Model') !== false)
		{
			return substr(get_class($this), 0, -strlen('Model'));
		}
		else
		{
			return get_class($this);
		}
	}
	
	protected function setSaved()
	{
		$this->_saved = true;
		foreach ($this->dbproperties() as $p)
		{
			$p['property']->setUnchanged();
		}
	}
	
	protected function beforeUpdate()
	{
	}
	
	protected function beforeInsert()
	{
	}
}

class ValidationException extends Exception
{
	public $instance = null;

	public function __construct($instance)
	{
		parent::__construct('Validating an object failed');
		$this->instance = $instance;
	}
}

?>
