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

abstract class One2Many implements IRelation
{
	public function relationpart($modelClass)
	{
		$info = $this->info();
		if ($modelClass == $info['from'])
		{
			$r = new OneRelation;
			$r->toClass = $info['to'];
			$r->name = $info['localAs'];
			$r->localKeys = $info['local'];
			$r->foreignKeys = $info['foreign'];
			return $r;
		}
		else if ($modelClass == $info['to'] && $info['foreignAs'])
		{
			$r = new ManyRelation;
			$r->toClass = $info['from'];
			$r->name = $info['foreignAs'];
			$r->localKeys= $info['foreign'];
			$r->foreignKeys = $info['local'];
			if (array_key_exists('orderBy', $info))
			{
				$r->orderBy = $info['orderBy'];
			}
			return $r;
		}
		return null;
	}
	
	abstract protected function info();
}
?>
