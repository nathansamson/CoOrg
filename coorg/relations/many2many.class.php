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

abstract class Many2Many implements IRelation
{
	public function relationpart($modelClass)
	{
		$info = $this->info();
		$r = new ManyManyRelation;
		$r->table = $info['table'];
		$r->tableFrom = $info['tableFrom'];
		$r->tableTo = $info['tableTo'];
		$r->from = $info['from'];
		$r->to = $info['to'];
		$r->fromLocal = $info['fromLocal'];
		$r->toLocal = $info['toLocal'];
		if ($modelClass == $info['from'])
		{
			$r->fromInstance = true;
			$r->name = $info['toAs'];
			return $r;
		}
		else if ($modelClass == $info['to'])
		{
			$r->toInstance = true;
			$r->name = $info['fromAs'];
			return $r;
		}
		else
		{
			return null;
		}
	}
	
	abstract protected function info();
}
?>
