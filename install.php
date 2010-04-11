<?php

echo "Welcome to CoOrg installation\n";

echo "We will not ask you any questions so see that your config file is already OK.";


require_once 'coorg/config.class.php';
require_once 'coorg/db.class.php';
$config = new Config('config/config.php');

DB::open($config->get('dbdsn'), $config->get('dbuser'), $config->get('dbpass'));

foreach ($config->get('enabled_plugins') as $plugin)
{
	$path = 'plugins/'.$plugin.'/install.php';
	if (is_file($path))
	{
		include_once $path;
		
		call_user_func($plugin.'_install_db');
	}
}


foreach (scandir('app') as $plugin)
{
	if ($plugin[0] == '.') continue;

	$path = 'app/'.$plugin.'/install.php';
	if (is_file($path))
	{
		include_once $path;
		
		call_user_func($plugin.'_install_db');
	}
}

?>
