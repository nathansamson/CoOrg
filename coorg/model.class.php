<?php

require_once 'coorg/properties/property.interface.php';
require_once 'coorg/properties/string.class.php';
require_once 'coorg/properties/email.class.php';
require_once 'coorg/properties/integer.class.php';

class Model
{
	private $_properties = array();
	private $_primaries = array();
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
			if (substr($line, 0, strlen('@property')) == '@property' ||
			    substr($line, 0, strlen('@primaryproperty')) == '@primaryproperty')
			{
				if (substr($line, 0, strlen('@property')) == '@property')
				{
					$primary = false;
					$pDesc = trim(substr($line, strlen('@property')));
				}
				else
				{
					$primary = true;
					$pDesc = trim(substr($line, strlen('@primaryproperty')));
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
				$this->_properties[$name] = $p;
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
			$this->validate('update');
			$this->update();
			$this->setSaved();
		}
		else
		{
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
		
		return $this->_properties[$name]->$fnc();
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
		
		$this->_properties[$name]->$fnc($value);
	}

	protected function update()
	{
		$qs = 'UPDATE ' . $this->tableName() . ' SET ';
		$sets = array();
		foreach ($this->_properties as $k => $p)
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
			$q->bindValue(':'.$k, $this->_properties[$k]->db());
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
		foreach ($this->_properties as $k => $p)
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
			$q->bindValue(':'.$p, $this->_properties[$p]->db());
		}
		$q->execute();
	}
	
	protected function validate($type)
	{
		$error = false;
		foreach ($this->_properties as $p)
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
		return substr(get_class($this), 0, -strlen('Model'));
	}
	
	protected function setSaved()
	{
		$this->_saved = true;
		foreach ($this->_properties as $p)
		{
			$p->setUnchanged();
		}
	}
}

class ValidationException extends Exception
{
}

?>
