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

abstract class One2One implements IRelation
{
	public function relationpart($modelClass)
	{
		$info = $this->info();
		if ($modelClass == $info['from'])
		{
			$r = new OneRelation;
			$r->toClass = $info['to'];
			$r->name = $info['localAs'];
			$r->localKeys = $info['fromLocal'];
			$r->foreignKeys = $info['fromForeign'];
			return $r;
		}
		else if ($modelClass == $info['to'])
		{
			$r = new OneRelation;
			$r->toClass = $info['from'];
			$r->name = $info['foreignAs'];
			$r->localKeys= $info['toLocal'];
			$r->foreignKeys = $info['toForeign'];
			return $r;
		}
		return null;
	}
	
	abstract protected function info();
}
?>
