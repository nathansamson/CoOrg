<?php

$res = array();

class CoOrg
{
	public static function resreg($app, $file, $v)
	{
		global $res;
		$res[$app][$file] = $v;
	}
}

function update($p, $dir, $theme, &$dirUpdates)
{
	global $res;
	$res[$p] = array();
	
	$resFile = $dir.'/static/'.$theme.'/resources.coorg.php';
	if (file_exists($resFile))
	{
		include_once $dir.'/static/'.$theme.'/resources.coorg.php';
	}
	else if ($p == '/')
	{
		include_once 'static/'.$theme.'/resources.coorg.php';
	}
	else
	{
		return;
	}
	
	$updated = array();
	foreach ($res[$p] as $file => $version)
	{
		$nfile = $dir.'/static/'.$theme.'/'.$file;
		if (strlen($version) == '32') // md5 hashing
		{
			$newversion = md5(file_get_contents($nfile));
			if ($newversion != $version)
			{
				$updated[$file] = $newversion;
			}
		}
	}

	if ($updated)
	{
		$dirUpdates = true;
		print_r($updated);
	}
}

$updates = false;
$themes = array('default');
if (count($_SERVER['argv']) > 1)
{
	$args = $_SERVER['argv'];
	array_shift($args); // Path
	foreach ($args as $theme)
	{
		$themes[] = $theme;
	}
}
foreach ($themes as $theme)
{
	echo $theme . "\n------\n";
	update('/', '.', $theme, $updates);


	foreach (scandir('plugins') as $p)
	{
		if ($p[0] == '.') continue;
		if (!is_dir('plugins/'.$p)) continue;
		echo 'Scanning ' . $p."\n";
	
		update($p, 'plugins/'.$p, $theme, $updates);
	}
	echo "\n\n";
}

if ($updates)
{
	exit(1);
}
else
{
	exit(0);
}

?>
