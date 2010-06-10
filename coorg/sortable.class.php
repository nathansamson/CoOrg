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

class Sortable
{
	private $_retriever;
	private $_groupProps = array();
	private $_object = null;
	private $_class;

	public function __construct($args)
	{
		$this->_class = $args[0];
		$this->_retriever = $args[1];
		for ($i = 2; $i < count($args); $i++)
		{
			$this->_groupProps[] = $args[$i];
		}
	}

	public function hasMethod($name)
	{
		if ($this->_object == null && $name == $this->_retriever)
		{
			return true;
		}
		return false;
	}
	
	public function connect($object)
	{
		$this->_object = $object;
	}
	
	public function properties()
	{
		return array('sequence' =>
						array('property' => new PropertyInteger('Seq'),
		                      'primary' => false,
		                      'writeonly' => false,
		                      'protected' => false,
		                      'auto-increment' => false,
		                      'class' => $this->_class));
	}
	
	public function __call($name, $args)
	{
		if ($this->_object == null && $name == $this->_retriever)
		{
			return $this->retrieve($args);
		}
	}
	
	public function beforeInsert()
	{
		list($queryExpressions, $queryArgs) = $this->params();
		
		$q = DB::prepare('SELECT MAX(sequence) AS seq, COUNT(*) AS cnt FROM '.$this->_class.' WHERE '.
				              implode(' AND ', $queryExpressions));
		$q->execute($queryArgs);
		$result = $q->fetch();
		$max = (int)$result['seq'];
		
		if ($this->_object->sequence === null)
		{
			if ($result)
			{
				$q = DB::prepare('SELECT * FROM Photos WHERE photobook=:d');
				$q->execute(array(':d' => 'D'));
				if ($result['seq'] !== null)
				{
					$this->_object->sequence = $max + 1;
				}
				else
				{
					$this->_object->sequence = 0;
				}
			}
			else
			{
				$this->_object->sequence = 0;
			}
		}
		else
		{
			if ($this->_object->sequence < 0)
			{
				$this->_object->sequence = 0;
			}
			if ($this->_object->sequence > $max)
			{
				$this->_object->sequence = $max + 1;
			}
			$q = DB::prepare('UPDATE '.$this->_class.
			                  ' SET sequence=sequence+1'.' WHERE '.
				              implode(' AND ', $queryExpressions) .
				              ' AND sequence>=:insertedSequence');
			$queryArgs[':insertedSequence'] = $this->_object->sequence_db;
			$q->execute($queryArgs);
		}
	}
	
	public function beforeUpdate()
	{
		list($queryExpressions, $queryArgs) = $this->params();
		if ($this->_object->sequence < 0) $this->_object->sequence = 0;
		
		$q = DB::prepare('SELECT MAX(sequence) AS seq, COUNT(*) AS cnt FROM '.$this->_class.' WHERE '.
				              implode(' AND ', $queryExpressions));
		$q->execute($queryArgs);
		$result = $q->fetch();
		$max = (int)$result['seq'];
		if ($this->_object->sequence > $max) $this->_object->sequence = $max;
		
		if (!$this->_object->sequence_changed) return;
		
		if ($this->_object->sequence > $this->_object->sequence_old)
		{
			$q = DB::prepare('UPDATE '.$this->_class.
					              ' SET sequence=sequence-1'.' WHERE '.
						          implode(' AND ', $queryExpressions) .
						          ' AND sequence >  :oldSequence'.
						          ' AND sequence <= :newSequence');
		}
		else
		{
			$q = DB::prepare('UPDATE '.$this->_class.
					              ' SET sequence=sequence+1'.' WHERE '.
						          implode(' AND ', $queryExpressions) .
						          ' AND sequence <  :oldSequence'.
						          ' AND sequence >= :newSequence');
		}
		$queryArgs[':oldSequence'] = $this->_object->sequence_old;
		$queryArgs[':newSequence'] = $this->_object->sequence_db;
		$q->execute($queryArgs);
	}
	
	private function retrieve($args)
	{
		list($queryExpressions, $queryArgs) = $this->params($args);
		
		$q = DB::prepare('SELECT * FROM ' . $this->_class.
		   ' WHERE ' . implode(' AND ', $queryExpressions) . 
		   ' ORDER BY sequence');
		$q->execute($queryArgs);
		
		$res = array();
		foreach ($q->fetchAll() as $row)
		{
			$res[] = DBModel::fetch($row, $this->_class);
		}
		return $res;
	}
	
	private function params($args = array())
	{	
		if ($args == array())
		{
			foreach ($this->_groupProps as $p)
			{
				$args[] = $this->_object->$p;
			}
		}
		$groupParams = array();
		$queryArgs = array();
		foreach ($this->_groupProps as $key => $p)
		{
			$groupParams[] = $p . ' =:'.$p;
			$queryArgs[':'.$p] = $args[$key];
		}
		return array($groupParams, $queryArgs);
	}
}

?>
