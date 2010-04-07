<?php

require_once 'coorg/db.class.php';
require_once 'coorg/config.class.php';

DB::open('sqlite::memory:');

function prepare()
{
	$q = DB::prepare('DROP TABLE IF EXISTS Mock');
	$q->execute();

	$q = DB::prepare('CREATE TABLE Mock(
	 name VARCHAR(256) PRIMARY KEY,
	 description VARCHAR(65000),
	 email VARCHAR(256) UNIQUE NOT NULL,
	 conditional INT)');
	$q->execute();
}

prepare();

?>
