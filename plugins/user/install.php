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

function user_install_db()
{
	$q = 'CREATE TABLE User(
				username VARCHAR(24) PRIMARY KEY,
				email VARCHAR(256) UNIQUE NOT NULL,
				firstName VARCHAR(64),
				lastName VARCHAR(64),
				passwordHash VARCHAR(128),
				passwordHashKey VARCHAR(64),
				lockKey VARCHAR(64),
				resetPasswordKey VARCHAR(64)
			)';

	$s = DB::prepare($q);
	$s->execute();
	
	$q = 'CREATE TABLE UserProfile (
				username VARCHAR(24),
				firstName VARCHAR(20),
				lastName VARCHAR(20),
				birthDate DATE,
				gender CHAR(1),
				website VARCHAR(1024),
				intrests VARCHAR(1024),
				biography TEXT,
				avatar VARCHAR(128),
				FOREIGN KEY (username) REFERENCES User(username) ON DELETE CASCADE
			)';

	$s = DB::prepare($q);
	$s->execute();
	
	$q = 'CREATE TABLE UserGroup (
				name VARCHAR(26) PRIMARY KEY,
				system BOOL
			)';

	$s = DB::prepare($q);
	$s->execute();
	
	
	$q = 'CREATE TABLE UserGroupMember (
				groupID VARCHAR(26),
				userID VARCHAR(24),
				PRIMARY KEY (groupID, userID)
			)';

	$s = DB::prepare($q);
	$s->execute();
	
	
	$q = 'CREATE TABLE Acl (
				groupID VARCHAR(26),
				keyID VARCHAR(32),
				allowed BOOL,
				PRIMARY KEY (groupID, keyID)
			)';

	$s = DB::prepare($q);
	$s->execute();
}

function user_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS Acl');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS UserGroupMember');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS UserGroup');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS UserProfile');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS User');
	$s->execute();
}

?>
