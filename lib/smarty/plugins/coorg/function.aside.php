<?php

function smarty_function_aside($params, $smarty)
{
	return CoOrg::aside($params['name'], $smarty);
}

?>
