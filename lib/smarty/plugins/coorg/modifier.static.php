<?php

function smarty_modifier_static($param, $plugin = '__')
{
	return CoOrg::staticFile($param, $plugin);
}

?>
