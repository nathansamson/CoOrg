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

include_once dirname(__FILE__).'/form/input.php';
include_once dirname(__FILE__).'/form/textinput.php';
include_once dirname(__FILE__).'/form/textarea.php';
include_once dirname(__FILE__).'/form/select.php';
include_once dirname(__FILE__).'/form/checkbox.php';
include_once dirname(__FILE__).'/form/radiobox.php';
include_once dirname(__FILE__).'/form/submit.php';

function smarty_function_input($params, $smarty)
{
	$type = FormElement::getParameter($params, 'type', 'text');
	$label = FormElement::getParameter($params, 'label');
	if (! $label && !FormElement::getBoolParameter($params, 'nolabel'))
	{
		$type = 'hidden';
	}
	
	$object = FormElement::getObject($type);
	if ($object instanceof FileInput)
	{
		$smarty->_coorg_form->file_upload = true;
	}

	if ($object instanceof LabeledFormElement)
	{
		$object->setLabel(t($label));
		$object->setIDPrefix($smarty->_coorg_form->formID);
		
		if (FormElement::getBoolParameter($params, 'disabled')) $object->disable();
		if ($tabindex = FormElement::getParameter($params, 'tabindex')) $object->tabindex($tabindex);
	}

	if ($object instanceof IUserInput)
	{
		if ($placeholder = FormElement::getParameter($params, 'placeholder')) $object->setPlaceholder($placeholder);
		if (FormElement::getBoolParameter($params, 'readonly')) $object->readonly();
					
		if ($for = FormElement::getParameter($params, 'for'))
		{
			$object->setName($for); // This can be overriden by a name="" (see later)
			
			$instance = $smarty->_coorg_form->instance;
			$object->setObject($instance, $for);
		}
		else
		{
			$object->setValue(FormElement::getParameter($params, 'value'));
		}
	}
	
	if ($name = FormElement::getParameter($params, 'name'))
	{
		$object->setName($name);
	}
	
	$object->setSpecificParameters($params);
	return $object->render();
}

?>
