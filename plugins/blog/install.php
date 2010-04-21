<?php

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
	   text VARCHAR(65000),
	   parentID VARCHAR(256),
	   parentLanguage VARCHAR(6),
	   PRIMARY KEY (ID, language, datePosted))
	');
	$s->execute();
}

function blog_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS Blog');
	$s->execute();
}

?>
