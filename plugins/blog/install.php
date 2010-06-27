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

function blog_install_db()
{
	$s = DB::prepare('CREATE TABLE Blog (
	   ID VARCHAR(256),
	   datePosted DATE,
	   language VARCHAR(6),
	   timePosted DATETIME,
	   timeEdited DATETIME,
	   title VARCHAR(256),
	   authorID VARCHAR(64),
	   text TEXT,
	   commentsAllowed BOOL,
	   commentsCloseDate DATETIME,
	   parentID VARCHAR(256),
	   parentLanguage VARCHAR(6),
	   PRIMARY KEY (ID, language, datePosted))
	');
	$s->execute();
	
	$s = DB::prepare('CREATE TABLE BlogComment (
	   ID INTEGER,
	   blogID VARCHAR(256),
	   blogLanguage VARCHAR(6),
	   blogDatePosted DATE,
	   FOREIGN KEY (blogID, blogLanguage, blogDatePosted) REFERENCES Blog(ID, language, datePosted) ON DELETE CASCADE,
	   FOREIGN KEY (ID) REFERENCES Comment(ID) ON DELETE CASCADE)
	');
	$s->execute();
}

function blog_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS BlogComment');
	$s->execute();
	$s = DB::prepare('DROP TABLE IF EXISTS Blog');
	$s->execute();
}

?>
