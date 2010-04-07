<?php

include_once 'coorg/model.class.php';
include_once 'coorg/model.test.class.php';
include_once 'coorg/coorg.class.php';

$config = new Config('config/tests.config.php');
$config->set('enabled_plugins', array('user'));

DB::open('sqlite::memory:');
CoOrg::init($config, null, 'plugins'); // Load the models


function prepare()
{
	$tables = array(
		'User' => 'CREATE TABLE User(
				username VARCHAR(24) PRIMARY KEY,
				email VARCHAR(256) UNIQUE NOT NULL,
				firstName VARCHAR(64),
				lastName VARCHAR(64),
				passwordHash VARCHAR(128),
				passwordHashKey VARCHAR(64)
			)'
	);
	
	foreach ($tables as $k => $sql)
	{
		$q = DB::prepare('DROP TABLE IF EXISTS ' . $k);
		$q->execute();
	}
	
	foreach ($tables as $k => $sql)
	{
		$q = DB::prepare($sql);
		$q->execute();
	}
}

prepare();

?>
