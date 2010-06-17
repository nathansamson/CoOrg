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

function update($p, $dir)
{
	global $res;
	$res[$p] = array();
	
	if (file_exists($dir.'/resources.coorg.php'))
	{
		include_once $dir.'/resources.coorg.php';
	}
	else if ($p == '/')
	{
		include_once 'static/resources.coorg.php';
	}
	
	$updated = false;
	foreach ($res[$p] as $file => $version)
	{
		$nfile = $dir.'/static/'.$file;
		if (strlen($version) == '32') // md5 hashing
		{
			$newversion = md5(file_get_contents($nfile));
			if ($newversion != $version)
			{
				$updated = true;
				$res[$p][$file] = $newversion;
			}
		}
	}

	if ($updated)
	{
		print_r($res[$p]);
	}
}

update('/', '.');

foreach (scandir('plugins') as $p)
{
	if ($p[0] == '.') continue;
	if (!is_dir('plugins/'.$p)) continue;
	echo 'Scanning ' . $p."\n";
	
	update($p, 'plugins/'.$p);
}

?>
