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

function menu_install_db()
{
	$s = DB::prepare('CREATE TABLE Menu (
		name VARCHAR(32) PRIMARY KEY,
		description VARCHAR(256)
	 )');
	 $s->execute();
	 
	 $s = DB::prepare('CREATE TABLE MenuEntry (
	 	ID INTEGER PRIMARY KEY AUTOINCREMENT,
	 	menu VARCHAR(32),
	 	language VARCHAR(6),
		sequence INTEGER,
	 	url VARCHAR(1024),
	 	title VARCHAR(64),
	 	submenu VARCHAR(32),
	 	provider VARCHAR(64),
	 	action VARCHAR(64),
	 	data VARCHAR(128),
	 	FOREIGN KEY (menu) REFERENCES Menu(name) ON UPDATE CASCADE ON DELETE CASCADE,
	 	FOREIGN KEY (submenu) REFERENCES Menu(name)
	  )');
	 $s->execute();
}

function menu_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS MenuEntry');
	$s->execute();
	$s = DB::prepare('DROP TABLE IF EXISTS Menu');
	$s->execute();
}

?>
