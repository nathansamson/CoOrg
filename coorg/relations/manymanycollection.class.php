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

class ManyManyCollection implements ArrayAccess, Iterator, Countable
{
	private $_list = null;
	private $_current;
	private $_instance;
	private $_table;
	private $_tableThisKeys;
	private $_tableOtherKeys;
	private $_otherName;
	private $_thisName;
	private $_thisKeys;
	private $_otherKeys;
	
	protected function __construct($instance, $table, $tableThisKeys,
	                               $tableOtherKeys, $thisName, $otherName,
	                               $thisKeys, $otherKeys)
	{
		$this->_instance = $instance;
		$this->_table = $table;
		if (! is_array($tableThisKeys))
		{
			$this->_tableThisKeys = array($tableThisKeys);
			$this->_thisKeys = array($thisKeys);
		}
		else
		{
			$this->_tableThisKeys = $tableThisKeys;
			$this->_thisKeys = $thisKeys;
		}
		
		if (! is_array($tableOtherKeys))
		{
			$this->_tableOtherKeys = array($tableOtherKeys);
			$this->_otherKeys = array($otherKeys);
		}
		else
		{
			$this->_tableOtherKeys = $tableOtherKeys;
			$this->_otherKeys = $otherKeys;
		}
		$this->_thisName = $thisName;
		$this->_otherName = $otherName;
	}
	
	public static function instance($args, $instance)
	{
		if ($args['fromInstance'])
		{
			return new ManyManyCollection($instance,
			           $args['table'], $args['tableFrom'], $args['tableTo'],
			           $args['from'], $args['to'],
			           $args['fromLocal'], $args['toLocal']);
		}
		else
		{
			return new ManyManyCollection($instance,
			           $args['table'], $args['tableTo'], $args['tableFrom'],
			           $args['to'], $args['from'],
			           $args['toLocal'], $args['fromLocal']);
		}
	}
	
	public function activate()
	{
		if ($this->_list !== null) {return false;}
		
		$this->_list = array();
		
		$joins = array();
		foreach ($this->_tableOtherKeys as $key => $other)
		{
			$joins[] = $this->_table.'.'.$other .'='.$this->_otherName.'.'.$this->_otherKeys[$key];
		}
		$wheres = array();
		$args = array();
		foreach ($this->_tableThisKeys as $key => $tableKey)
		{
			$wheres[] = $this->_table.'.'.$tableKey.'=:'.$tableKey;
			$keyName = $this->_thisKeys[$key].'_db';
			$args[':'.$tableKey] = $this->_instance->$keyName;
		}
		
		$query = 'SELECT '.$this->_otherName.'.* FROM ' . $this->_table.
		            ' JOIN ' . $this->_otherName.' ON '. implode(', ', $joins) . 
		            ' WHERE ' . implode (' AND ', $wheres);
		$q = DB::prepare($query);
		$q->execute($args);
		
		foreach ($q->fetchAll() as $row)
		{
			$this->_list[] = DBModel::fetch($row, $this->_otherName);
		}
		$this->rewind();
		return true;
	}
	
	/* Array */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->_list);
	}
	
	public function offsetUnset($offset)
	{
		$value = $this->_list[$offset];
		$wheres = array();
		foreach ($this->_tableThisKeys as $key=>$tableKey)
		{
			$keyName = $this->_thisKeys[$key].'_db';
			$args[':'.$tableKey] = $this->_instance->$keyName;
			$wheres[] = $tableKey.'=:'.$tableKey;
		}
		
		foreach ($this->_tableOtherKeys as $key=>$tableKey)
		{
			$keyName = $this->_otherKeys[$key].'_db';
			$args[':'.$tableKey] = $value->$keyName;
			$wheres[] = $tableKey.'=:'.$tableKey;
		}
		$q = DB::prepare('DELETE FROM '.$this->_table .
		                 ' WHERE '.implode(' AND ',$wheres));
		$q->execute($args);
		unset($this->_list[$offset]);
	}
	
	public function offsetSet($offset, $value)
	{
		if ($offset === null)
		{
			$args = array();
			$names = array();
			foreach ($this->_tableThisKeys as $key=>$tableKey)
			{
				$keyName = $this->_thisKeys[$key].'_db';
				$args[':'.$tableKey] = $this->_instance->$keyName;
				$names[] = $tableKey;
			}
			
			foreach ($this->_tableOtherKeys as $key=>$tableKey)
			{
				$keyName = $this->_otherKeys[$key].'_db';
				$args[':'.$tableKey] = $value->$keyName;
				$names[] = $tableKey;
			}
			$q = DB::prepare('INSERT INTO '.$this->_table.' ('.implode(',',$names).') '.
			                 'VALUES(:'.implode(',:',$names).')');
			$q->execute($args);
			$this->_list[] = $value;
			return;
		}
		throw new Exception('This is a readonly collection');
	}
	
	public function offsetGet($offset)
	{
		return $this->_list[$offset];
	}
	
	/* Countable */
	public function count()
	{
		return count($this->_list);
	}
	
	/* Iterator */
	public function current()
	{
		return $this->_list[$this->_current];
	}
	
	public function key()
	{
		return $this->_current;
	}
	
	public function next()
	{
		$this->_current++;
		if ($this->_current >= $this->count())
		{
			return null;
		}
		return $this->current();
	}
	
	public function rewind()
	{
		$this->_current = 0;
	}
	
	public function valid()
	{
		return $this->_current < $this->count();
	}
	
	protected function getSQLQuery()
	{
		$join = get_parent_class($this->_from);
		$selectFrom = $this->_from;
		if ($join != 'DBModel') $selectFrom .= ' NATURAL JOIN '. $join;
		
		$args = array();
		$wheres = array();
		foreach ($this->_localKeys as $key=>$local)
		{
			$foreign = $this->_foreignKeys[$key];
			$wheres[] = $foreign . '=:'.$local;
			$localDB = $local.'_db';
			$args[':'.$local] = $this->_instance->$localDB;
		}
		$where = implode(' AND ', $wheres);
		
		$q = 'SELECT * FROM '.$selectFrom .' WHERE '.$where;
		return array($q, $args);
	}
}
