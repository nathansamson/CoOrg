<?php

echo "Welcome to CoOrg installation\n";

echo "We will not ask you any questions so see that your config file is already OK.";


require_once 'coorg/config.class.php';
require_once 'coorg/db.class.php';
$config = new Config('config/config.php');

// copied from PHP docs page somewhere in the comments
function uuidv4() {
	return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),

		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	);
}


$config->set('site/uuid', uuidv4());
$config->save();

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
