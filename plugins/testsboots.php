<?php
define('COORG_UNITTEST', true);
require_once 'PHPUnit/Framework.php';

require_once 'coorg/testing/domainexists.test.php';
require_once 'coorg/coorg.class.php';
require_once 'coorg/testing/model.test.class.php';
require_once 'coorg/testing/coorg.test.class.php';
require_once 'coorg/testing/coorgsmarty.test.class.php';
require_once 'coorg/testing/header.test.class.php';
require_once 'coorg/testing/state.test.class.php';
require_once 'coorg/testing/mail.test.class.php';
require_once 'coorg/testing/files.test.class.php';

$configFile = 'config/tests.config.php';
if (array_key_exists('COORG_CONFIGFILE', $_SERVER))
{
	$configFile = $_SERVER['COORG_CONFIGFILE'];
}
copy($configFile, 'config/temp.config.tests.php');
$configFile = 'config/temp.config.tests.php';
define('COORG_TEST_CONFIG_CLEAN', $configFile.'.clean');
define('COORG_TEST_CONFIG', $configFile);

$config = new Config($configFile);
$config->set('mollom/public', 'valid-pub-key');
$config->set('mollom/private', 'valid-priv-key');
$config->set('mollom/serverlist', array('valid-server-list'));
$config->set('enabled_plugins', array('search', 'spam', 'admin', 'menu', 'user', 'comments', 'user-admin', 'blog', 'page', 'puntstudio-users'));
$config->set('site/title', 'The Site');
$config->save();
copy($configFile, 'config/temp.config.tests.php.clean');
DB::open($config->get('dbdsn'), $config->get('dbuser'), $config->get('dbpass'));

CoOrg::init($config, 'coorg/testing/plugins-app', 'plugins'); // Load the models


function prepare($plugins)
{
	foreach (array_reverse($plugins) as $p)
	{
		if (file_exists('plugins/'.$p.'/install.php'))
		{
			include_once 'plugins/'.$p.'/install.php';
			
			$f = str_replace('-', '_', $p);
			call_user_func($f.'_delete_db', true);
		}
	}
	
	foreach ($plugins as $p)
	{
		if (file_exists('plugins/'.$p.'/install.php'))
		{
			include_once 'plugins/'.$p.'/install.php';
			
			$f = str_replace('-', '_', $p);
			call_user_func($f.'_install_db', true);
		}
	}
}

prepare($plugins = $config->get('enabled_plugins'));

?>
