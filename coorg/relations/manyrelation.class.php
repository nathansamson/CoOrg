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

class ManyRelation implements IRelationPart
{
	public $toClass;
	public $name;
	public $foreignKeys;
	public $localKeys;
	public $orderBy = null;
	public $filter = null;

	public function attributes()
	{
		return array();
	}
	
	public function variants()
	{
		return array();
	}
	
	public function collections()
	{
		if ($this->filter == null)
		{
			if ($this->orderBy == null)
			{
				return array($this->name => 
							array('class' => 'ManyCollection',
								  'from' => $this->toClass,
								  'foreign' => $this->foreignKeys,
								  'local' => $this->localKeys));
			}
			else
			{
				return array($this->name => 
							array('class' => 'OrderedManyCollection',
								  'from' => $this->toClass,
								  'foreign' => $this->foreignKeys,
								  'local' => $this->localKeys,
								  'orderBy' => $this->orderBy));
			}
		}
		else
		{
			return array($this->name => 
							array('class' => 'FilterCollection',
								  'from' => $this->toClass,
								  'foreign' => $this->foreignKeys,
								  'local' => $this->localKeys,
								  'orderBy' => $this->orderBy,
								  'filter' => $this->filter));
		}
	}
}

?>
