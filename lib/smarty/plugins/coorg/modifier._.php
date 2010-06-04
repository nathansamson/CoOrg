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

function smarty_modifier__($text)
{
	if (func_num_args() > 1)
	{
		$keys = array();
		preg_match_all('/\%([a-zA-Z]*)/', $text, $matches);
		$i = 0;
		foreach ($matches[1] as $key)
		{
			if (!array_key_exists($key, $keys))
			{
				$i++;
				$keys[$key] = func_get_arg($i);
			}
		}
		return t($text, $keys);
	}
	else
	{
		return t($text);
	}
}
