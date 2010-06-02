<?php

function smarty_block_button($params, $contents, $smarty)
{
	if ($contents !== NULL)
	{
		$form = '<form action="'.CoOrg::createURL(explode('/', $params['request'])).'" method="post" class="normal-post-url">';
		
		foreach ($params as $key => $value)
		{
			if (strpos($key, 'param_') === 0)
			{
				$name = substr($key, 6);
				$form .= '<input type="hidden" name="'.$name.'" value="'.$value.'"/>';
			}
		}
		
		$form .= '<button type="submit">'.$contents.'</button>';
		$form .= '</form>';
		return $form;
	}
}

?>
