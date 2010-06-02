<?php

function smarty_helper_function_build_option($key, $option, $value)
{
	if (is_array($option))
	{
		$s = '<optgroup label="'.$option['label'].'">';
		foreach ($option['options'] as $k => $o)
		{
			$s .= smarty_helper_function_build_option($k, $o, $value);
		}
		$s .= '</optgroup>';
	}
	else
	{
		if ($key != $value)
		{
			$s = '<option value="'.$key.'">'.$option.'</option>';
		}
		else
		{
			$s = '<option value="'.$key.'" selected="selected">'.$option.'</option>';
		}
	}
	return $s;
}

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
		$label = t($params['label']);
	}
	else
	{
		$type = 'hidden';
	}
	$disabled = false;
	if (array_key_exists('disabled', $params))
	{
		$disabled = true;
	}
	
	if ($type != 'submit')
	{
		$required = array_key_exists('required', $params);
		if (array_key_exists('for', $params))
		{
			$forName = $params['for'];
			$name = array_key_exists('name', $params) ? $params['name'] : $forName;
			
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
			$name = $params['name'];
			$value = $params['value'];
		}
	}
	else
	{
		$value = $label;
		$name = 'xxSubmit';
	}
	
	if ($type != 'hidden' && $type != 'submit')
	{
		$form = $smarty->_coorg_form;
		$id = $form->formID ? $form->formID.'_'.$name : $name;
		$label = '<label for="'.$id.'" '.($required ? 'class="required"' : '' ). '>'.$label.'</label>';
	
		
		if ($type != 'textarea' && $type != 'select')
		{
			$input = '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" '. 'id="'.$id.'"'.
	               ($required ? ' required="required"' : '').
	               ($disabled ? ' disabled="disabled"' : '').
	        '/>';
	    }
	    else if ($type == 'textarea')
	    {
	    	$cols = 0;
	    	$rows = 0;
	    	if (array_key_exists('size', $params))
	    	{
	    		if($params['size'] == 'small')
	    		{
	    			$cols = 40;
	    			$rows = 2;
	    		}
	    	}
	    	$input = '<textarea name="'.$name.'" '. 'id="'.$id.'" '.
	    	                         ($required ? 'required="required"' : '').
	    	                         ($cols ? ' cols='.$cols : '').
	    	                         ($rows ? ' rows='.$rows : '').
	    	               '>'.$value.'</textarea>';
	    }
	    else
	    {
	    	$input = '<select name="'.$name.'" id="'.$id.'"' .($required ? 'required="required"' : '').'>';
	    	foreach ($params['options'] as $key => $opt)
	    	{
	    		$input .= smarty_helper_function_build_option($key, $opt, $value); 
	    	}
	    	$input .= '</select>';
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
