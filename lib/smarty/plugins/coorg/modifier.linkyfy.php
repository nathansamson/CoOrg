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

function smarty_modifier_linkyfy($text, $type)
{
	$args = func_get_args();
	array_shift($args);
	switch ($type)
	{
		case 'e':
			$url = htmlspecialchars($args[1]);
			$target = '_blank';	
			break;
		case 'b':
			$target = '_blank';
			array_shift($args);
		default:
			$url = call_user_func(array('CoOrg', 'createURL'), $args);	
	}
	return '<a href="'.$url.'"'.
	    ($target ? ' target="'.$target.'"' : '').
	    '>'.$text.'</a>';
}

?>
