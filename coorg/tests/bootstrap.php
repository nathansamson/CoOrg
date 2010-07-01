<?php

require_once 'PHPUnit/Framework.php';

require_once 'coorg/testing/domainexists.test.php';
require_once 'coorg/coorg.class.php';
require_once 'coorg/testing/model.test.class.php';
require_once 'coorg/testing/coorg.test.class.php';
require_once 'coorg/testing/coorgsmarty.test.class.php';
require_once 'coorg/testing/header.test.class.php';
require_once 'coorg/testing/mail.test.class.php';

DB::open('sqlite::memory:');

function prepare()
{
	$q = DB::prepare('DROP TABLE IF EXISTS FooBars');
	$q->execute();

	$q = DB::prepare('DROP TABLE IF EXISTS Foo');
	$q->execute();
	
	$q = DB::prepare('DROP TABLE IF EXISTS Bar');
	$q->execute();

	$q = DB::prepare('DROP TABLE IF EXISTS IsAMockModel');
	$q->execute();

	$q = DB::prepare('DROP TABLE IF EXISTS MockModel');
	$q->execute();

	$q = DB::prepare('CREATE TABLE MockModel(
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
	
	
	$q = DB::prepare('DROP TABLE IF EXISTS SomeContainment');
	$q->execute();
	
	$q = DB::prepare('DROP TABLE IF EXISTS SomeContainer');
	$q->execute();

	$q = DB::prepare('CREATE TABLE SomeContainer(
	 ID VARCHAR(32) PRIMARY KEY,
	 content TEXT)');
	$q->execute();

	$q = DB::prepare('CREATE TABLE SomeContainment(
	 name VARCHAR(64) PRIMARY KEY,
	 containerID VARCHAR(32),
	 published BOOL,
	 sequence INTEGER,
	 FOREIGN KEY (containerID) REFERENCES SomeContainer(ID))');
	$q->execute();
	
	$q = DB::prepare('CREATE TABLE IsAMockModel(
	 name VARCHAR(256),
	 isaExtension VARCHAR(32),
	 containerID VARCHAR(32),
	 FOREIGN KEY (name) REFERENCES MockModel(name) ON DELETE CASCADE,
	 FOREIGN KEY (containerID) REFERENCES SomeContainer(ID))');
	$q->execute();
	
	$q = DB::prepare('CREATE TABLE Foo(
	 ID VARCHAR(20) PRIMARY KEY,
	 barID VARCHAR(10),
	 foofoo VARCHAR(20)
	)');
	$q->execute();
	
	$q = DB::prepare('CREATE TABLE Bar(
	 ID VARCHAR(10) PRIMARY KEY,
	 fooID VARCHAR(10),
	 barbar VARCHAR(20),
	 FOREIGN KEY (fooID) REFERENCES Foo(ID))');
	$q->execute();
	
	$q = DB::prepare('CREATE TABLE FooBars(
	 fooID VARCHAR(10),
	 barID VARCHAR(20),
	 FOREIGN KEY (fooID) REFERENCES Foo(ID),
	 FOREIGN KEY (barID) REFERENCES Bar(ID))');
	$q->execute();
}

prepare();

?>
