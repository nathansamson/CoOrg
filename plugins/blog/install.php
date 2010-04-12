<?php

function blog_install_db()
{
	$s = DB::prepare('CREATE TABLE Blog (
	   ID VARCHAR(256),
	   datePosted DATE,
	   title VARCHAR(256),
	   authorID VARCHAR(64),
	   text VARCHAR(65000),
	   PRIMARY KEY (ID, datePosted))
	');
	$s->execute();
}

function blog_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS Blog');
	$s->execute();
}

?>
