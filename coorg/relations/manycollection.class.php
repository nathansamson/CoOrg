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

class ManyCollection implements ArrayAccess, Iterator, Countable
{
	private $_list = null;
	private $_current;
	private $_instance;
	private $_from;
	private $_localKey;
	private $_foreignKey;
	
	private function __construct($instance, $from, $localKey, $foreignKey)
	{
		$this->_instance = $instance;
		$this->_from = $from;
		$this->_localKey = $localKey;
		$this->_foreignKey = $foreignKey;
	}
	
	public static function instance($args, $instance)
	{
		return new ManyCollection($instance, $args['from'], $args['local'], 
		                          $args['foreign']);
	}
	
	public function activate()
	{
		if ($this->_list !== null) {return false;}
		$join = get_parent_class($this->_from);
		$selectFrom = $this->_from;
		if ($join != 'DBModel') $selectFrom .= ' NATURAL JOIN '. $join;
		$q = DB::prepare('SELECT * FROM '.$selectFrom .' WHERE '.
		                   $this->_foreignKey . '=:local');
		$key = $this->_localKey;
		$q->execute(array(':local' => $this->_instance->$key));
		$this->_list = array();
		$this->rewind();
		foreach ($q->fetchAll() as $row)
		{
			$this->_list[] = DBModel::fetch($row, $this->_from);
		}
		return true;
	}
	
	/* Array */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->_list);
	}
	
	public function offsetUnset($offset)
	{
		$i = $this->_list[$offset];
		$i->delete();
		unset($this->_list[$offset]);
	}
	
	public function offsetSet($offset, $value)
	{
		if ($offset === null)
		{
			$foreignKey = $this->_foreignKey;
			$localKey = $this->_localKey;
			$value->$foreignKey = $this->_instance->$localKey;
			$value->save();
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
}

?>
