<?php

require_once 'coorg/properties/property.interface.php';
require_once 'coorg/properties/string.class.php';
require_once 'coorg/properties/email.class.php';
require_once 'coorg/properties/integer.class.php';

class Model
{
	private $_properties = array();
	private $_primaries = array();
	private $_shadowProperties = array();
	private $_internalProperties = array();
	protected $_saved = false;
	
	public function __construct()
	{
		$reflClass = new ReflectionClass($this);
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
			if (preg_match('/^@(internal|shadow|primary)?property/', $line))
			{
				$shadow = false;
				$primary = false;
				$internal = false;
				if (strpos($line, '@property') === 0)
				{
					$pDesc = trim(substr($line, strlen('@property')));
				}
				else if (strpos($line, '@shadowproperty') === 0)
				{
					$shadow = true;
					$pDesc = trim(substr($line, strlen('@shadowproperty')));
				}
				else if (strpos($line, '@internalproperty') === 0)
				{
					$internal = true;
					$pDesc = trim(substr($line, strlen('@internalproperty')));
				}
				else if (strpos($line, '@primaryproperty') === 0)
				{
					$primary = true;
					$pDesc = trim(substr($line, strlen('@primaryproperty')));
				}
				else
				{
					var_dump($line);
					die();
				}
				$desc = explode(';', $pDesc, 2);
				$descFirst = explode(' ', $desc[0], 2);
				
				$extras = explode(' ', $desc[1]);
				$name = $descFirst[0];
				$construct = $descFirst[1];
				
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
				if ($shadow)
				{
					$this->_shadowProperties[$name] = $p;
				}
				else if ($internal)
				{
					$this->_internalProperties[$name] = $p;
				}
				else
				{
					$this->_properties[$name] = $p;
				}
				if ($primary)
				{
					$this->_primaries[$name] = $p;
				}
			}
		}
	}

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
			return $this->_properties[$name]->$fnc();
		}
		else if (array_key_exists($name, $this->_shadowProperties) && $fnc != 'get')
		{
			return $this->_shadowProperties[$name]->$fnc();
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
			$this->_properties[$name]->$fnc($value);
		}
		else if (array_key_exists($name, $this->_shadowProperties))
		{
			$this->_shadowProperties[$name]->$fnc($value);
		}
		else
		{
			throw new Exception('Attribute not found.');
		}
	}
	
	protected function property($name)
	{
		$allProperties = array_merge($this->_properties,
		                             $this->_shadowProperties,
		                             $this->_internalProperties);
		
		if (array_key_exists($name, $allProperties))
		{
			return $allProperties[$name];
		}
		else
		{
			throw new Exception('Property "'.$name.'" not found');
		}
	}
	
	protected function dbproperties()
	{
		return array_merge($this->_properties, $this->_internalProperties);
	}

	protected function update()
	{
		$qs = 'UPDATE ' . $this->tableName() . ' SET ';
		$sets = array();
		foreach ($this->dbproperties() as $k => $p)
		{
			if ($p->changed())
			{
				if ($p->db() != null)
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
		$qs .= implode(', ', $sets);
		
		$qs .= ' WHERE ';
		
		$pwheres = array();
		foreach ($this->_primaries as $pk => $pp)
		{
			$pwheres[] = $pk . '=:p'.$pk;
		}
		$qs .= implode(' AND ', $pwheres);
		
		$q = DB::prepare($qs);
		foreach ($properties as $k)
		{
			$q->bindValue(':'.$k, $this->property($k)->db());
		}
		foreach ($this->_primaries as $pk => $pp)
		{
			$q->bindValue(':p'.$pk, $pp->old());
		}
		$q->execute();
	}

	protected function insert()
	{
		$properties = array();
		foreach ($this->dbproperties() as $k => $p)
		{
			if ($p->db() != null)
			{
				$properties[] = $k;
			}
		}
	
		$qs = 'INSERT INTO ' . $this->tableName() . ' (';
		$qs .= implode(',', $properties) . ')';
		$qs .= ' VALUES(:'.implode(',:', $properties). ')';
		
		$q = DB::prepare($qs);
		foreach ($properties as $p)
		{
			$q->bindValue(':'.$p, $this->property($p)->db());
		}
		$q->execute();
	}
	
	protected function validate($type)
	{
		$error = false;
		foreach (array_merge($this->_properties, $this->_shadowProperties) as $p)
		{
			if (!$p->validate($type))
			{
				$error = true;
			}
		}
		if ($error) throw new ValidationException();
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
		foreach ($this->_properties as $p)
		{
			$p->setUnchanged();
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
}

?>
