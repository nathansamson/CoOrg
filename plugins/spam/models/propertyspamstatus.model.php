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


class PropertySpamStatus extends PropertyEnum
{
	const OK = 1;
	const SPAM = 2;
	const UNKNOWN = 3;

	public function __construct($name)
	{
		parent::__construct($name,
		                    array(
		                    	self::OK,
		                    	self::SPAM,
		                    	self::UNKNOWN
		                    ));
	}
}

?>
