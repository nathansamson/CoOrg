<?php

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
