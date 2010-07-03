<?php

function smarty_function_foreign($args, $smarty)
{
	$module = $args['module'];
	$file = $args['file'];
	unset($args['module']);
	unset($args['file']);
	
	if (in_array($module, CoOrg::config()->get('enabled_plugins')))
	{
		$basepath = 'plugins/'.$module.'/views/';
	}
	else
	{
		$basepath = 'app/'.$module.'/views/';
	}
	$theme = CoOrg::getTheme();
	if ($theme != 'default')
	{	
		if (file_exists($basepath.$theme.'/'.$file))
		{
			$path = $basepath.$theme.'/';
		}
		else
		{
			$path = $basepath.'default/';
		}
	}
	else
	{
		$path = $basepath.'default/';
	}
	
	$data = $smarty->createData($smarty);
	foreach ($args as $name=>$val)
	{
		$data->assign($name, $val);
	}
	
	$tpl = $smarty->createTemplate($path.$file, $data);
	return $tpl->fetch();
}

?>
