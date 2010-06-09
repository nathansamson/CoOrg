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
	
	$s = DB::prepare('CREATE TABLE PageLanguage (
	   page1ID VARCHAR(256),
	   page1Language VARCHAR(6),
	   page2ID VARCHAR(256),
	   page2Language VARCHAR(6),
	   FOREIGN KEY (page1ID, page1Language) REFERENCES Page(ID, language) ON DELETE CASCADE,
	   FOREIGN KEY (page2ID, page2Language) REFERENCES Page(ID, language) ON DELETE CASCADE
	)');
	$s->execute();
	
	$q = DB::prepare('CREATE VIEW PageLanguagesBidiV AS
		SELECT  page1ID AS page1ID, page1Language AS page1Language,
		       page2ID AS page2ID, page2Language AS page2Language
		     FROM PageLanguage
		 UNION
		SELECT page2ID AS page1ID, page2Language AS page1Language,
		       page1ID AS page2ID, page1Language AS page2Language
		     FROM PageLanguage
	');
	$q->execute();
}

function page_delete_db()
{
	$s = DB::prepare('DROP VIEW IF EXISTS PageLanguagesBidiV');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS PageLanguage');
	$s->execute();

	$s = DB::prepare('DROP TABLE IF EXISTS Page');
	$s->execute();
}

?>
