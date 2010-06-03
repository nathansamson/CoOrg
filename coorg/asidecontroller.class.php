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

abstract class AsideController
{

	private $_viewsPath;
	private $_smarty;
	private $_variablesSetByMe = array();
	
	public function __construct($smarty, $viewsPath)
	{
		$this->_smarty = $smarty;
		$this->_viewsPath = $viewsPath;
	}
	
	abstract function run($widgetParams, $request);
	
	protected function render($tpl)
	{
		$s = $this->_smarty->fetch($this->_viewsPath.$tpl.'.html.tpl');
		foreach ($this->_variablesSetByMe as $var)
		{
			$this->_smarty->clearAssign($var);
		}
		return $s;
	}

	final public function __set($var, $value)
	{
		if ($this->_smarty->getVariable($var) instanceof Undefined_Smarty_Variable)
		{
			$this->_variablesSetByMe[] = $var;
			$this->_smarty->assign($var, $value);
		}
		else
		{
			throw new Exception('Can not overwrite template variable!');
		}
	}
}

?>
