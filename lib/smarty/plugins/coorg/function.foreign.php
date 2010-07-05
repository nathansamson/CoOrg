<?php

function smarty_function_foreign($args, $smarty)
{
	$module = $args['module'];
	$file = $args['file'];
	unset($args['module']);
	unset($args['file']);
	
	$path = Controller::getTemplatePath($file, $module);
	
	$data = $smarty->createData($smarty);
	foreach ($args as $name=>$val)
	{
		$data->assign($name, $val);
	}
	
	$tpl = $smarty->createTemplate($path, $data);
	return $tpl->fetch();
}

?>
