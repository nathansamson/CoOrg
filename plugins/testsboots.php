<?php

require_once 'PHPUnit/Framework.php';

require_once 'coorg/coorg.class.php';
require_once 'coorg/testing/model.test.class.php';
require_once 'coorg/testing/coorg.test.class.php';
require_once 'coorg/testing/coorgsmarty.test.class.php';
require_once 'coorg/testing/header.test.class.php';
require_once 'coorg/testing/state.test.class.php';
require_once 'coorg/testing/mail.test.class.php';

$configFile = 'config/tests.config.php';
if (array_key_exists('COORG_CONFIGFILE', $_SERVER))
{
	$configFile = $_SERVER['COORG_CONFIGFILE'];
}
define('COORG_TEST_CONFIG', $configFile);

$config = new Config($configFile);
$config->set('enabled_plugins', array('admin', 'menu', 'user', 'blog', 'page'));
$config->set('site/title', 'The Site');
DB::open($config->get('dbdsn'), $config->get('dbuser'), $config->get('dbpass'));

CoOrg::init($config, 'coorg/testing/plugins-app', 'plugins'); // Load the models


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
