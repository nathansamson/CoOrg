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

class Normalize
{
	private $_toNormalize;
	private $_normalized;
	private $_fixed;
	private $_object;
	private $_class;

	public function __construct($primaries)
	{
		$this->_class = array_shift($primaries);
		$this->_toNormalize = array_shift($primaries);
		$this->_normalized = array_shift($primaries);
		$this->_fixed = $primaries;
	}
	
	public function hasMethod($m)
	{
		return false;
	}
	
	public function connect($o)
	{
		$this->_object = $o;
	}
	
	public function properties()
	{
		return array();
	}
	
	public function beforeInsert()
	{
		$n = $this->_normalized;
		$this->_object->$n = $this->findFree();
	}
	
	public function beforeUpdate()
	{
	}
	
	public function afterDelete()
	{
	}
	
	private function findFree()
	{
		$args = array();
		$wheres = array();
		foreach ($this->_fixed as $fix)
		{
			$fixDB = $fix.'_db';
			$args[':'.$fix] = $this->_object->$fixDB;
			$wheres[] = $fix.'=:'.$fix;
		}
		$wheres[] = $this->_normalized .'=:_a';
		$q = DB::prepare('SELECT * FROM ' . $this->_class . ' WHERE '. implode(' AND ', $wheres));
		
		$f = $this->_toNormalize;
		$base = $this->normalize($this->_object->$f);
		$i = 0;
		$full = $base;
		$args[':_a'] = $full;
		$q->execute($args);
		while ($q->fetch())
		{
			$i++;
			$full = $base.$i;
			$args[':_a'] = $full;
			$q->execute($args);
		}
		return $full;
	}
	
	private function normalize($title)
	{
		return strtolower(str_replace(' ', '-', $title));
	}
}
