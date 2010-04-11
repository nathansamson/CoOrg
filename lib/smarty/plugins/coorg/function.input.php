<?php

function smarty_function_input($params, $smarty)
{


	if (array_key_exists('type', $params))
	{
		$type = $params['type'];
	}
	else
	{
		$type = 'text';
	}
	if (array_key_exists('label', $params))
	{
		$label = $params['label'];
	}
	else
	{
		$type = 'hidden';
	}
	
	if ($type != 'submit')
	{
		$required = array_key_exists('required', $params);
		$forName = $params['for'];
		$name = $forName;
	
		$instance = $smarty->_coorg_form->instance;
		if ($type != 'password')
		{
			$rawAttribute = $forName. '_raw';
			$value = $instance->$rawAttribute;
		}
		else
		{
			$value = '';
		}
		
		$errorName = $forName . '_errors';
		$errors = $instance->$errorName;
	}
	else
	{
		$value = $label;
		$name = 'xxSubmit';
	}
	
	if ($type != 'hidden' && $type != 'submit')
	{
		$s = '<label for="'.$name.'">'.$label.'</label><input type="'.$type.'" value="'.$value.'" name="'.$name.'" '.
	               ($required ? 'required="required"' : '').
	        '/>';
	    if (is_string($errors))
	    {
	    	$s .= '<span class="form-error">'.$errors.'</span>';
	    }
	    else if ($errors != null)
	    {
	    	foreach ($errors as $error)
	    	{
	    		$s .= '<span class="form-error">'.$errors.'</span>';
	    	}
	    }
		return $s . '<br />';
	}
	else
	{
		return '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" />';
	}

}

?>
