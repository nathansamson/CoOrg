<?php

function user_install_db()
{
	$q = 'CREATE TABLE User(
				username VARCHAR(24) PRIMARY KEY,
				email VARCHAR(256) UNIQUE NOT NULL,
				firstName VARCHAR(64),
				lastName VARCHAR(64),
				passwordHash VARCHAR(128),
				passwordHashKey VARCHAR(64)
			)';

	$s = DB::prepare($q);
	$s->execute();
}

function user_delete_db()
{
	$s = DB::prepare('DROP TABLE IF EXISTS User');
	$s->execute();
}

?>
