<?php

function smarty_function_url($params, $smarty)
{
	return CoOrg::createURL($params);
}

?>
