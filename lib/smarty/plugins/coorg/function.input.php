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
		$label = '<label for="'.$name.'"'.($required ? 'class="required"' : '' ). '>'.$label.'</label>';
	
		
		if ($type != 'textarea')
		{
			$input = '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" '. 'id="'.$name.'"'.
	               ($required ? 'required="required"' : '').
	        '/>';
	    }
	    else
	    {
	    	$input = '<textarea name="'.$name.'" '. 'id="'.$name.'"'.($required ? 'required="required"' : '').'>'.$value.'</textarea>';
	    }
	    if (is_string($errors))
	    {
	    	$input .= '<span class="form-error">'.$errors.'</span>';
	    }
	    else if ($errors != null)
	    {
	    	foreach ($errors as $error)
	    	{
	    		$input .= '<span class="form-error">'.$errors.'</span>';
	    	}
	    }
		return $label . $input . '<br />';
	}
	else
	{
		return '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" />';
	}

}

?>
