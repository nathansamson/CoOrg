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
	private $_data = null;
	
	public function __construct($smarty, $viewsPath)
	{
		$this->_smarty = $smarty;
		$this->_viewsPath = $viewsPath;
	}
	
	abstract function run($widgetParams, $request);
	abstract function preview($widgetParams);
	
	protected function render($tpl)
	{
		return $this->doRender($tpl);
	}
	
	protected function renderPreview($tpl)
	{
		return $this->doRender($tpl, 'layout-preview.html.tpl');
	}

	final public function __set($var, $value)
	{
		if ($this->_data == null)
		{
			$this->_data = $this->_smarty->createData($this->_smarty);
		}
		$this->_data->assign($var, $value);
	}
	
	protected function doRender($tpl, $base = null)
	{
		if ($base)
		{
			$tpl = $this->_smarty->createTemplate('extends:'.$base.'|'.$this->_viewsPath.$tpl.'.html.tpl', $this->_data);
		}
		else
		{
			$tpl = $this->_smarty->createTemplate($this->_viewsPath.$tpl.'.html.tpl', $this->_data);
		}
		return $tpl->fetch();
	}
}

abstract class AsideConfigurableController extends AsideController
{	
	abstract function configure($widgetParams);
	
	protected function renderConfigure($tpl)
	{
		return $this->doRender($tpl, 'layout-configure.html.tpl');
	}
}

?>
