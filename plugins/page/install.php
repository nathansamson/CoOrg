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

function page_install_db()
{
	$s = DB::prepare('CREATE TABLE Page (
	   ID VARCHAR(256),
   	   language VARCHAR(6),
	   created DATE,
	   updated DATE,
   	   author VARCHAR(64),
   	   lastEditor VARCHAR(64),
	   title VARCHAR(256),
	   content TEXT,
	   PRIMARY KEY (ID, language))
	');
	$s->execute();
}

function page_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS Page');
	$s->execute();
}

?>
