<?php
/*
 * Copyright 2010 Nathan Samson <nathansamson at gmail dot com>
 *
 * This file is part of CoOrg.
 *
 * CoOrg is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

  * CoOrg is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU Affero General Public License for more details.

  * You should have received a copy of the GNU Affero General Public License
  * along with CoOrg.  If not, see <http://www.gnu.org/licenses/>.
*/

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
			$s = '<option value="'.htmlspecialchars($key).'">'.htmlspecialchars($option).'</option>';
		}
		else
		{
			$s = '<option value="'.htmlspecialchars($key).'" selected="selected">'.htmlspecialchars($option).'</option>';
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
				$value = htmlspecialchars($instance->$rawAttribute);
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
		if (array_key_exists('name', $params))
		{
			$name = $params['name'];
		}
		else
		{
			$name = 'xxSubmit';
		}
	}
	
	if ($type != 'hidden' && $type != 'submit')
	{
		$form = $smarty->_coorg_form;
		$id = $form->formID ? $form->formID.'_'.$name : $name;
		$label = '<label for="'.$id.'" '.($required ? 'class="required"' : '' ). '>'.$label.'</label>';
		if (array_key_exists('class', $params))
		{
			$class = $params['class'];
		}
		if (array_key_exists('size', $params))
		{
			if ($params['size'] == 'wide')
			{
				$size = '40';
			}
			else if ($params['size'] == 'full-wide')
			{
				$size = '60';
			}
		}
		
		if ($type != 'textarea' && $type != 'select' && $type != 'checkbox')
		{
			$input = '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" '. 'id="'.$id.'"'.
	               ($required ? ' required="required"' : '').
	               ($disabled ? ' disabled="disabled"' : '').
	               ($class ? ' class="'.$class.'"' : '').
	               ($size ? ' size="'.$size.'"' : '').
	        '/><br />';
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
	    		else if ($params['size'] == 'big')
	    		{
	    			$cols = 60;
	    			$rows = 25;
	    		}
	    		else if ($params['size'] == 'wide')
	    		{
	    			$cols = 60;
	    			$rows = 10;
	    		}
	    		else if($params['size'] == 'small-wide')
	    		{
	    			$cols = 60;
	    			$rows = 4;
	    		}
	    	}
	    	$editor = null;
	    	if (array_key_exists('editor', $params))
	    	{
	    		$editor = $params['editor'];
	    	}
	    	$input = '<textarea name="'.$name.'" '. 'id="'.$id.'" '.
	    	                         ($required ? 'required="required"' : '').
	    	                         ($cols ? ' cols='.$cols : '').
	    	                         ($rows ? ' rows='.$rows : '').
	    	                         ($editor ? ' class="'.$editor.'-editor"' : '').
	    	               '>'.$value.'</textarea>';
	    }
	    else if ($type == 'checkbox')
	    {
	    	$input = '<input type="'.$type.'" name="'.$name.'" '. 'id="'.$id.'"'.
	               ($required ? ' required="required"' : '').
	               ($disabled ? ' disabled="disabled"' : '').
	               ($value ? ' checked="checked"' : '').
	               ($class ? ' class="'.$class.'"' : '').
	        '/><br />';
	    }
	    else
	    {
	    	$input = '<select name="'.$name.'" id="'.$id.'"' .($required ? 'required="required"' : '').'>';
	    	foreach ($params['options'] as $key => $opt)
	    	{
	    		$input .= smarty_helper_function_build_option($key, $opt, $value); 
	    	}
	    	$input .= '</select><br />';
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
		return $label . $input;
	}
	else
	{
		return '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" />';
	}

}

?>
