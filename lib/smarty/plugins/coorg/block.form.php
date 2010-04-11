<?php

function smarty_block_form($params, $content, $smarty)
{
	if ($content == null)
	{
		$form = (object)array();
		$form->brose_element = false;
		$form->instance = $params['instance'];
		
		$smarty->_coorg_form = $form;
		return;
	}
	
	$form = $smarty->_coorg_form;
	
	$request = $params['request'];
	$URL = call_user_func($smarty->_coorg_createURL, $request);
	
	return '<form method="post" action="'.$URL.'">'.$content.'</form>';
}

?>
