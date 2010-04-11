<?php

require_once 'PHPUnit/Framework.php';

require_once 'coorg/coorg.class.php';
require_once 'coorg/testing/model.test.class.php';
require_once 'coorg/testing/coorg.test.class.php';
require_once 'coorg/testing/coorgsmarty.test.class.php';
require_once 'coorg/testing/header.test.class.php';
require_once 'coorg/testing/state.test.class.php';

$config = new Config('config/tests.config.php');
$config->set('enabled_plugins', array('user'));

DB::open('sqlite::memory:');
CoOrg::init($config, null, 'plugins'); // Load the models


function prepare($plugins)
{
	foreach (array_reverse($plugins) as $p)
	{
		if (file_exists('plugins/'.$p.'/install.php'))
		{
			include_once 'plugins/'.$p.'/install.php';
			
			call_user_func($p.'_delete_db');
		}
	}
	
	foreach ($plugins as $p)
	{
		if (file_exists('plugins/'.$p.'/install.php'))
		{
			include_once 'plugins/'.$p.'/install.php';
			
			call_user_func($p.'_install_db');
		}
	}
}

prepare($plugins = $config->get('enabled_plugins'));

?>
