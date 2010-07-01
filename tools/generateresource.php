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
	
	$updated = false;
	foreach ($res[$p] as $file => $version)
	{
		$nfile = $dir.'/static/'.$theme.'/'.$file;
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
		$dirUpdates = true;
		print_r($res[$p]);
	}
}

$updates = false;
update('/', '.', 'default', $updates);

foreach (scandir('plugins') as $p)
{
	if ($p[0] == '.') continue;
	if (!is_dir('plugins/'.$p)) continue;
	echo 'Scanning ' . $p."\n";
	
	update($p, 'plugins/'.$p, 'default', $updates);
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
