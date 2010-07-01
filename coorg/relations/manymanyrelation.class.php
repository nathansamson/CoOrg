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

class ManyManyRelation implements IRelationPart
{
	public $table;
	public $tableFrom;
	public $tableTo;
	public $from;
	public $to;
	public $fromLocal;
	public $toLocal;
	public $name;
	public $fromInstance = false;
	public $toInstance = false;

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
		return array($this->name => 
							array('class' => 'ManyManyCollection',
								  'table' => $this->table,
								  'tableFrom' => $this->tableFrom,
								  'tableTo' => $this->tableTo,
								  'from' => $this->from,
								  'to' => $this->to,
								  'fromLocal' => $this->fromLocal,
								  'toLocal' => $this->toLocal,
								  'name' => $this->name,
								  'fromInstance' => $this->fromInstance));
	}
}

?>
