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

class Header implements IHeader
{
	public static function setErrorCode($code)
	{
		header('HTTP/1.1 ' . $code);
	}

	public static function setContentType($ct)
	{
		header('Content-Type: ' . $ct);
	}
	
	public static function redirect($to)
	{
		$args = func_get_args();
		$full = CoOrg::createURL($args);
		header('Location: '.$full);
	}
}

?>
