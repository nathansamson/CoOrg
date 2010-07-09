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

function smarty_block_form($params, $content, $smarty)
{
	if ($content == null)
	{
		$form = (object)array();
		$form->brose_element = false;
		$form->instance = $params['instance'];
		$form->formID = array_key_exists('id', $params) ? $params['id'] : null;
		$form->nestedInstances = array();
		$form->nobreaks = array_key_exists('nobreaks', $params);
		$form->file_upload = false;
		
		$smarty->_coorg_form = $form;
		return;
	}
	
	$form = $smarty->_coorg_form;
	
	$request = $params['request'];
	$URL = call_user_func($smarty->_coorg_createURL, $request);
	
	if (array_key_exists('method', $params))
	{
		$method = $params['method'];
	}
	else
	{
		$method = 'post';
	}
	
	return '<form method="'.$method.'" action="'.$URL.'"'.
	          ($form->formID ? ' id="'.$form->formID.'"': '').
	          ($form->file_upload ? ' enctype="multipart/form-data"' : '').
	          '>'.$content.'</form>';
}

?>
