<?php

require_once 'PHPUnit/Framework.php';

require_once 'coorg/coorg.class.php';
require_once 'coorg/testing/model.test.class.php';
require_once 'coorg/testing/coorg.test.class.php';
require_once 'coorg/testing/coorgsmarty.test.class.php';
require_once 'coorg/testing/header.test.class.php';
require_once 'coorg/testing/mail.test.class.php';

DB::open('sqlite::memory:');

function prepare()
{
	$q = DB::prepare('DROP TABLE IF EXISTS Mock');
	$q->execute();

	$q = DB::prepare('CREATE TABLE Mock(
	 name VARCHAR(256) PRIMARY KEY,
	 description VARCHAR(65000),
	 email VARCHAR(256) NOT NULL,
	 rot13name VARCHAR(64) NOT NULL,
	 conditional INT)');
	$q->execute();
	
	$q = DB::prepare('DROP TABLE IF EXISTS Photos');
	$q->execute();

	$q = DB::prepare('CREATE TABLE Photos(
	 name VARCHAR(64) PRIMARY KEY,
	 photobook VARCHAR(64),
	 sequence INT)');
	$q->execute();
}

prepare();

?>
