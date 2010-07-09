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
	$br = $smarty->_coorg_form->nobreaks ? '' : '<br />';

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
	else if (array_key_exists('nolabel', $params))
	{
		$label = null;
	}
	else
	{
		$type = 'hidden';
	}
	$disabled = array_key_exists('disabled', $params);
	$readonly = array_key_exists('readonly', $params);
	$tabindex = array_key_exists('tabindex', $params) ? $params['tabindex'] : null;
	
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
			$value = htmlspecialchars($params['value']);
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
		$class = '';
		if ($required) $class = 'required';
		if ($type == 'select')
		{
			if ($class)
			{
				$class .= ' select';
			}
			else 
			{
				$class = 'select';
			}

		}
		if ($label)
		{
			$label = '<label for="'.$id.'" '.($class ? 'class="'.$class.'"' : '' ). '>'.$label.'</label>';
		}
		if (array_key_exists('class', $params))
		{
			$class = $params['class'];
			if ($errors)
			{
				$class .= ' error';
			}
		}
		else if ($errors)
		{
			$class .= ' error';
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
			$p = '';
			if ($type == 'file' && $params['preview'] && $params['preview'] == 'image' && $value)
			{
				$pClass = '';
				if (array_key_exists('previewClass', $params))
				{
					$pClass = $params['previewClass'];
				}
				if ($pClass)
				{
					$p = '<div class="'.$pClass.'"><img src="'.$value.'" /></div>';
				}
				else
				{
					$p = '<img src="'.$value.'" />';
				}
			}
			if ($type == 'file')
			{
				$smarty->_coorg_form->file_upload = true;
				$value = null;
			}
			$input = '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" '. 'id="'.$id.'"'.
	               ($required ? ' required="required"' : '').
	               ($disabled ? ' disabled="disabled"' : '').
	               ($class ? ' class="'.$class.'"' : '').
	               ($size ? ' size="'.$size.'"' : '').
	               ($readonly ? ' readonly="readonly"' : '').
	        '/>'.$p.$br;
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
	    	/* Explantion for "$required && !$editor"
	    	 *  When required=true for an empty textarea, ckeditor does not get
	    	 * the chanche to fill the textarea with data in browsers (webkit as of this writing)
	    	 * validates the input. (and people does not get the chance to post
	    	 *  comments, blogposts and content pages).
	    	*/
	    	$input = '<textarea name="'.$name.'" '. 'id="'.$id.'" '.
	    	                         (($required && !$editor) ? 'required="required"' : '').
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
	        '/>'.$br;
	    }
	    else
	    {
	    	$input = '<select name="'.$name.'" id="'.$id.'"' .($required ? 'required="required"' : '').'>';
	    	foreach ($params['options'] as $key => $opt)
	    	{
	    		$input .= smarty_helper_function_build_option($key, $opt, $value); 
	    	}
	    	$input .= '</select>'.$br;
	    }
	    
	    if (is_string($errors))
	    {
	    	$label .= '<p class="form-error">'.$errors.'</p>';
	    }
	    else if ($errors != null)
	    {
	    	foreach ($errors as $error)
	    	{
	    		$label .= '<p class="form-error">'.$errors.'</p>';
	    	}
	    }
		return $label . $input;
	}
	else if ($type == 'submit' && $params['stock'])
	{
		$stockInfo = CoOrg::stocks($params['stock']);
		$button = '<img src="'.CoOrg::staticFile($stockInfo['img']).'"
			            alt="'.$stockInfo['alt'].'"
			            title="'.$stockInfo['title'].'"/>';
		return '<button type="submit" name="'.$name.'" value="dummy"'.
		        ($tabindex ? ' tabindex="'.$tabindex.'"' : '').
		        '>'.$button.'</button>';
	}
	else
	{
		return '<input type="'.$type.'" value="'.$value.'" name="'.$name.'" />';
	}

}

?>
