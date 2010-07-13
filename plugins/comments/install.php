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

function comments_install_db($test = false)
{	 
	  $s = DB::prepare('CREATE TABLE AnonProfile (
	 	ID INTEGER PRIMARY KEY AUTOINCREMENT,
	 	name VARCHAR(32),
	 	email VARCHAR(256),
	 	website VARCHAR(1024),
	 	IP VARCHAR(39)
	  )');
	 $s->execute();

	 $s = DB::prepare('CREATE TABLE Comment (
	 	ID INTEGER PRIMARY KEY AUTOINCREMENT,
	 	authorID VARCHAR(24),
	 	anonAuthorID INTEGER,
	 	timePosted DATETIME,
	 	title VARCHAR(128),
	 	comment TEXT,
	 	spamStatus INTEGER,
	 	spamSessionID VARCHAR(256),
	 	FOREIGN KEY (authorID) REFERENCES User(username),
	 	FOREIGN KEY (anonAuthorID) REFERENCES AnonProfile(ID)
	  )');
	 $s->execute();
	 
	 if ($test)
	 {	 
	 	$s = DB::prepare('CREATE TABLE MeCommentMock (
	 		ID VARCHAR(32) PRIMARY KEY,
	 		text VARCHAR(128))');
		$s->execute();
		
		$s = DB::prepare('CREATE TABLE MeCommentMockComment (
			ID INTEGER,
			mockID VARCHAR(32),
			FOREIGN KEY(ID) REFERENCES Comment(ID),
			FOREIGN KEY(mockID) REFERENCES MeCommentMock(ID)
		)');
		$s->execute();
	 }
}

function comments_delete_db($test = false)
{
	$s = DB::prepare('DROP TABLE IF EXISTS MeCommentMockComment');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS MeCommentMock');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS Comment');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS AnonProfile');
	$s->execute();
}

?>
