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

function search_install_db()
{
	$s = DB::prepare('CREATE TABLE SearchIndex (
	   SID INTEGER PRIMARY KEY AUTOINCREMENT,
   	   field VARCHAR(32),
   	   term VARCHAR(128),
	   relevance INTEGER,
	   xSearchContent TEXT)
	');
	$s->execute();
	
	if (defined('COORG_UNITTEST'))
	{
		$s = DB::prepare('CREATE TABLE SearchBar (
		   title VARCHAR(64),
		   someOtherPrimary VARCHAR(64),
		   language VARCHAR(6),
		   body TEXT,
		   PRIMARY KEY(title, someOtherPrimary)
		)');
		$s->execute();
	
		$q = DB::prepare('CREATE TABLE SearchBarIndex (
			SID INTEGER PRIMARY KEY,
			title VARCHAR(64),
			someOtherPrimary VARCHAR(64),
			FOREIGN KEY(title, someOtherPrimary) REFERENCES SearchBar(title, someOtherPrimary) ON DELETE CASCADE,
			FOREIGN KEY(SID) REFERENCES SearchIndex(SID) ON DELETE CASCADE
		)');
		$q->execute();
	
		$s = DB::prepare('CREATE TABLE SearchFoo (
		   title VARCHAR(64),
		   someOtherPrimary VARCHAR(64),
		   datePrimary DATE,
		   language VARCHAR(6),
		   identity VARCHAR(512),
		   body TEXT,
		   barTitle VARCHAR(64),
		   barSomeOtherPrimary VARCHAR(64),
		   FOREIGN KEY(barTitle, barSomeOtherPrimary) REFERENCES SearchBar(title, someOtherPrimary) ON DELETE SET NULL,
		   PRIMARY KEY(title, someOtherPrimary, datePrimary)
		)');
		$s->execute();
	
		$q = DB::prepare('CREATE TABLE SearchFooIndex (
			SID INTEGER PRIMARY KEY,
			title VARCHAR(64),
			datePrimary DATE,
			someOtherPrimary VARCHAR(64),
			FOREIGN KEY(title, someOtherPrimary, datePrimary) REFERENCES SearchFoo(title, someOtherPrimary, datePrimary) ON DELETE CASCADE,
			FOREIGN KEY(SID) REFERENCES SearchIndex(SID) ON DELETE CASCADE
		)');
		$q->execute();
		
		$s = DB::prepare('CREATE TABLE SearchFooISA (
		   title VARCHAR(64),
		   someOtherPrimary VARCHAR(64),
		   datePrimary DATE,
		   someISAVar VARCHAR(32),
		   otherVar INTEGER,
		   FOREIGN KEY(title, someOtherPrimary, datePrimary) REFERENCES SearchFoo(title, someOtherPrimary, datePrimary) ON DELETE CASCADE,
		   PRIMARY KEY(title, someOtherPrimary, datePrimary)
		)');
		$s->execute();
		
		$s = DB::prepare('CREATE TABLE Tagging (
		   title VARCHAR(64) PRIMARY KEY,
		   body TEXT,
		   language VARCHAR(6)
		)');
		$s->execute();
		
		$q = DB::prepare('CREATE TABLE TaggingIndex (
			SID INTEGER PRIMARY KEY,
			title VARCHAR(64),
			FOREIGN KEY(title) REFERENCES Tagging(title) ON DELETE CASCADE,
			FOREIGN KEY(SID) REFERENCES SearchIndex(SID) ON DELETE CASCADE
		)');
		$q->execute();
	}
}

function search_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS TaggingIndex');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS Tagging');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS SearchFooISA');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS SearchFooIndex');
	$s->execute();
	
	$s = DB::prepare('DROP TABLE IF EXISTS SearchFoo');
	$s->execute();
	
	$s = DB::prepare('DROP TABLE IF EXISTS SearchBarIndex');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS SearchBar');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS SearchIndex');
	$s->execute();
}

?>
