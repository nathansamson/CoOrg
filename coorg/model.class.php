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

require_once 'coorg/properties/property.interface.php';
require_once 'coorg/properties/string.class.php';
require_once 'coorg/properties/email.class.php';
require_once 'coorg/properties/integer.class.php';
require_once 'coorg/properties/date.class.php';
require_once 'coorg/properties/bool.class.php';
require_once 'coorg/properties/enum.class.php';
require_once 'coorg/properties/url.class.php';
require_once 'coorg/properties/file.class.php';

class Model
{
	protected static $_modelInfo = array();
	protected static $_relations = array();

	private $_classedProperties = array();
	private $_properties = array();
	private $_variants = array();
	private $_extensions = array();
	private $_collections = array();
	
	protected function __construct()
	{
		$class = get_class($this);
		if (!array_key_exists($class, self::$_modelInfo))
		{
			self::$_modelInfo[$class] = self::parseProperties($class);
		}

		foreach (self::$_modelInfo[$class]['classes'] as $aClass)
		{
			$this->_classedProperties[$aClass] = array();
			foreach (self::$_modelInfo[$aClass]['properties'] as $name=>$propertyInfo)
			{
				$info = $propertyInfo;
				$info['property'] = clone $propertyInfo['property'];
				$this->_classedProperties[$aClass][$name] = $info;
				$this->_properties[$name] = $info;
			}
		}
		
		foreach (self::$_modelInfo[$class]['extensions'] as $name => $extension)
		{
			$ext  = clone $extension;
			$ext->connect($this);
			$this->_extensions[$name] = $ext;
		}
		foreach (self::$_modelInfo[$class]['classes'] as $aClass)
		{
			foreach (self::$_modelInfo[$aClass]['variants'] as $name => $variantInfo)
			{
				$property = $this->_properties[$variantInfo['property']]['property'];
				$variantClass = $variantInfo['class'];
				$var = call_user_func(array($variantClass, 'instance'), $property, $variantInfo['args']);
				$property->attachVariant($var);
				$this->_variants[$name] = array('propertyName' => $variantInfo['property'] ,
					                            'variant' => $var);
			}
			foreach (self::$_modelInfo[$aClass]['collections'] as $name => $collectionInfo)
			{
				$collClass = $collectionInfo['class'];
				$this->_collections[$name] = call_user_func(array($collClass, 'instance'), $collectionInfo, $this);
			}
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
		
		$prop = false;
		$var = false;
		if (($prop = array_key_exists($name, $this->_properties)) ||
		    ($var = array_key_exists($name, $this->_variants)))
		{
			if ($prop)
			{
				$propertyInfo = $this->_properties[$name];
			}
			else if ($var)
			{
				$variantInfo = $this->_variants[$name];
				$propertyInfo = $this->_properties[$variantInfo['propertyName']];
			}
			else
			{
				die('This cant happen');
			}

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
			
			if ($prop)
			{
				return $propertyInfo['property']->$fnc();
			}
			else
			{
				return $variantInfo['variant']->$fnc();
			}
		}
		else if (array_key_exists($name, $this->_collections))
		{
			$coll = $this->_collections[$name]; 
			$coll->activate();
			return $coll;
		}
		else
		{
			throw new Exception('Attribute "'.$name.'" not found.');
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
		
		$prop = false;
		$var = false;
		if (($prop = array_key_exists($name, $this->_properties)) ||
		    ($var = array_key_exists($name, $this->_variants)))
		{
			//TODO: Fix Access
			if ($prop)
			{
				$this->_properties[$name]['property']->$fnc($value);
			}
			else if ($var)
			{
				if ($fnc == 'set')
				{
					$this->_variants[$name]['variant']->$fnc($value);
				}
				else
				{
					$this->_properties[$this->_variants[$name]['propertyName']]['property']->$fnc($value);
				}
			}
			else
			{
				die('Cant never happen');
			}
		}
		else
		{
			throw new Exception('Attribute "'.$name.'" not found.');
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
		foreach ($this->_variants as $variant)
		{
			$i = $variant['variant']->get();
			if ($i != null && $type == 'insert' && $i instanceof DBModel && !$i->inDB())
			{
				try
				{
					$i->validate('insert');
				}
				catch (ValidationException $e)
				{
					$error = true;
				}
			}
		}
		if ($error) throw new ValidationException($this);
	}
	
	protected function dbproperties($class)
	{
		if ($class != null)
		{
			$props = $this->_classedProperties[$class];
			$props = array_merge($props, $this->primaries($class));
		}
		else
		{
			$props = $this->_properties;
		}
		return array_filter($props, array('Model', 'filterDB'));
		
	}
	
	protected function primaries($class)
	{
		$classes = self::$_modelInfo[$class]['classes'];
		$pri = array();
		foreach ($classes as $class)
		{
			$pri = array_merge($pri, array_filter($this->_classedProperties[$class], array('Model', 'filterPrimary')));
		}
		return $pri;
	}
	
	protected function properties()
	{
		return $this->_properties;
	}
	
	protected function extensions()
	{
		return $this->_extensions;
	}
	
	protected function autoincrements($class)
	{
		$ais = array();
		foreach ($this->_classedProperties[$class] as $k=>$p)
		{
			if ($p['auto-increment'])
			{
				$ais[$k] = $p;
			}
		}
		return $ais;
	}
	
	protected function variants()
	{
		return $this->_variants;
	}

	static private function parseProperties($class)
	{
		$propertyInfo = array();
		$extensions = array();
		$variants = array();
		$relations = array();
		$collections = array();
		
		$parentClass = get_parent_class($class);
		if ($parentClass != 'Model' && $parentClass != 'DBModel')
		{
			if (!array_key_exists($parentClass, self::$_modelInfo))
			{
				self::$_modelInfo[$parentClass] = self::parseProperties($parentClass);
			}
			$parents = self::$_modelInfo[$parentClass]['classes'];
			$parents[] = $class;
		}
		else
		{
			$parents = array($class);
		}
		
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
			
			$pExplode = explode(' ', $line, 2);
			$pCommand = $pExplode[0];
			if (count($pExplode) == 2)
			{ 
				$pDesc = $pExplode[1];
			}
			else
			{
				$pDesc = '';
			}
			if ($pCommand == '@property')
			{
				$primary = false;
				$writeonly = false;
				$protected = false;
				$autoincrement = false;
				$nodb = false;
				
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
					    $option == 'protected' || $option == 'autoincrement' ||
					    $option == 'nodb')
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
				                             'nodb' => $nodb,
				                             'class' => $class);
			}
			else if ($pCommand == '@extends')
			{
				$params = explode(' ', $pDesc);
				$extClass = array_shift($params);
				array_unshift($params, $class);
				$ext = new $extClass($params);
				
				foreach ($ext->properties() as $name => $p)
				{
					$options = array('primary', 'writeonly', 'protected',
					                 'auto-increment', 'nodb');
					foreach ($options as $opt)
					{
						if (!array_key_exists($opt, $p))
						{
							$p[$opt] = false;
						}
					}
					$propertyInfo[$name] = $p;
				}
				$extensions[] = $ext;
			}
			else if ($pCommand == '@variant')
			{
				$params = explode(' ', $pDesc);
				$variants[$params[0]] = array('class' => ucfirst($params[1]).'Variant',
				                              'property' => $params[2],
				                              'args' => array());
			}
		}
		
		foreach (self::$_relations as $relation)
		{
			$part = $relation->relationpart($class);
			if ($part == null) continue;
			foreach ($part->variants() as $name => $variant)
			{
				$variants[$name] = $variant;
			}
			foreach ($part->collections() as $name => $collection)
			{
				$collections[$name] = $collection;
			}
		}
		
		return array('properties' => $propertyInfo,
		             'extensions' => $extensions,
		             'variants' => $variants,
		             'collections' => $collections,
		             'classes' => $parents);
	}

	static public function filterDB($f)
	{
		return !($f['writeonly'] || $f['nodb']);
	}

	static public function filterPrimary($f)
	{
		return $f['primary'];
	}
	
	static public function registerRelation($class)
	{
		self::$_relations[] = $class;
	}
	
	static protected function callStatic($class, $fnc, $arguments)
	{
		if (! array_key_exists($class, self::$_modelInfo))
		{
			self::$_modelInfo[$class] = self::parseProperties($class);
		}
		$modelInfo = self::$_modelInfo[$class];
		foreach ($modelInfo['extensions'] as $ext)
		{
			if ($ext->hasMethod($fnc))
			{
				return call_user_func_array(array($ext, $fnc), $arguments);
			}
		}
	}
}

class DBModel extends Model
{
	protected $_saved = false;
	protected $_inDB = false;
	
	public function batchSave($batch)
	{
		foreach ($batch as $key => $b)
		{
			if (!$b->_saved)
			{
				try
				{
					$b->prepareInsert();
				}
				catch (ValidationException $e)
				{
					$invalid = true;
				}
			}
			else
			{
				unset($batch[$key]);
			}
		}
		if ($invalid) throw new ValidationException(null);
		$rs = array();
		foreach ($batch as $b)
		{
			$rs[] = $b->insert();
			$b->_inDB = true;
		}
		
		foreach ($batch as $b)
		{
			$b->afterInsert();
		}
		return $rs;
	}
	
	private function prepareInsert()
	{
		$this->beforeInsert();
		foreach ($this->extensions() as $ext)
		{
			$ext->beforeInsert();
		}
		$this->validate('insert');
		foreach ($this->variants() as $name=>$variant)
		{
			$i = $variant['variant']->get();
			if ($i != null && $i instanceof DBModel && !$i->inDB())
			{
				$i->save();
				$variant['variant']->set($i);
			}
		}
	}

	public function save()
	{
		if ($this->_saved)
		{
			$this->beforeUpdate();
			foreach ($this->extensions() as $ext)
			{
				$ext->beforeUpdate();
			}
			$this->validate('update');
			$r = $this->update();
			$this->afterUpdate();
		}
		else
		{
			$this->prepareInsert();
			$r = $this->insert();
			$this->_inDB = true;
			$this->afterInsert();
		}
		$this->setSaved();
		return $r;
	}

	public function inDB()
	{
		return $this->_inDB;
	}

	protected function update()
	{
		foreach (self::$_modelInfo[get_class($this)]['classes'] as $class)
		{
			$qs = 'UPDATE ' . $class . ' SET ';
			$sets = array();
			$properties = array();
			foreach ($this->dbproperties($class) as $k => $p)
			{
				if ($p['property']->changed())
				{
					if ($p['property']->db() !== null)
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
				continue; // Nothing to do.
			}
			$qs .= implode(', ', $sets);
		
			$qs .= ' WHERE ';
		
			$pwheres = array();
			foreach ($this->primaries($class) as $pk => $pp)
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
			foreach ($this->primaries($class) as $pk => $pp)
			{
				$q->bindValue(':p'.$pk, $pp['property']->old());
			}
			$q->execute();
		}
	}

	protected function insert()
	{
		foreach (self::$_modelInfo[get_class($this)]['classes'] as $class)
		{
			$properties = array();
		
			foreach ($this->dbproperties($class) as $k => $p)
			{
				$db = $k.'_db';
				if ($this->$db !== null)
				{
					$properties[] = $k;
				}
			}
			
			$qs = 'INSERT INTO ' . $class . ' (';
			$qs .= implode(',', $properties) . ')';
			$qs .= ' VALUES(:'.implode(',:', $properties). ')';
		
			$q = DB::prepare($qs);
			foreach ($properties as $k=>$p)
			{
				$db = $p.'_db';
				$q->bindValue(':'.$p, $this->$db);
			}
			$q->execute();
			foreach ($this->autoincrements($class) as $k => $p)
			{
				$this->$k = DB::lastInsertID($class);
			}
		}
	}
	
	public function delete()
	{
		foreach (self::$_modelInfo[get_class($this)]['classes'] as $class)
		{
			$qs = 'DELETE FROM ' . $class . ' WHERE ';
		
			$pwheres = array();
			foreach ($this->primaries($class) as $pk => $pp)
			{
				$pwheres[] = $pk . '=:p'.$pk;
			}
			$qs .= implode(' AND ', $pwheres);
		
			$q = DB::prepare($qs);
			foreach ($this->primaries($class) as $pk => $pp)
			{
				$q->bindValue(':p'.$pk, $pp['property']->old());
			}
			$q->execute();
		}
		foreach ($this->extensions() as $ext)
		{
			$ext->afterDelete();
		}
		$this->_inDB = false;
	}
	
	protected function setSaved()
	{
		$this->_saved = true;
		foreach ($this->dbproperties(null) as $p)
		{
			$p['property']->postsave();
		}
	}
	
	protected function beforeUpdate()
	{
	}
	
	protected function afterUpdate()
	{
	}
	
	protected function beforeInsert()
	{
	}
	
	protected function afterInsert()
	{
	}
	
	protected function afterFetch() {}
	
	public static function fetch($row, $model)
	{
		if ($row == null) return null;
		$instance = new $model;
		foreach (self::$_modelInfo[$model]['classes'] as $class)
		{
			foreach (self::$_modelInfo[$class]['properties'] as $pName => $pInfo)
			{
				if (!($pInfo['writeonly'] || $pInfo['nodb']))
				{
					$instance->$pName = $row[$pName];
				}
			}
		}
		$instance->afterFetch();
		$instance->setSaved();
		$instance->_inDB = true;
		return $instance;
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
