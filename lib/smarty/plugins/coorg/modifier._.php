<?php

function smarty_modifier__($text)
{
	if (func_num_args() > 1)
	{
		die('NYI');
	}
	else
	{
		return t($text);
	}
}
