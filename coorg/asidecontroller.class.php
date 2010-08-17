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

abstract class BaseWidgetController
{
	private $_viewsPath;
	private $_smarty;
	private $_data = null;
	
	public function __construct($smarty, $viewsPath)
	{
		$this->_smarty = $smarty;
		$this->_viewsPath = $viewsPath;
	}
	
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
	
	final public function __get($var)
	{
		return $this->_smarty->getTemplateVars($var);
	}
	
	protected function doRender($tpl, $base = null)
	{
		$theme = CoOrg::getTheme();
		if ($theme != 'default')
		{
			$file = $this->_viewsPath.$theme.'/'.$tpl.'.html.tpl';
			if (!file_exists($file))
			{
				$file = $this->_viewsPath.'default/'.$tpl.'.html.tpl';
			}
		}
		else
		{
			$file = $this->_viewsPath.'default/'.$tpl.'.html.tpl';
		}
		if ($base)
		{
			$tpl = $this->_smarty->createTemplate('extends:'.$base.'|'.$file, $this->_data);
		}
		else
		{
			$tpl = $this->_smarty->createTemplate($file, $this->_data);
		}
		return $tpl->fetch();
	}
}

abstract class SiteWidgetController extends BaseWidgetController
{
	abstract function run($widgetParams, $request);
	abstract function preview($widgetParams);
}

abstract class AsideController extends BaseWidgetController
{
	abstract function run($widgetParams, $orient, $request);
	abstract function preview($widgetParams, $orient);
}

abstract class WidgetController extends AsideController {}

abstract class AsideConfigurableController extends AsideController
{	
	abstract function configure($widgetParams, $orient);
	
	protected function renderConfigure($tpl)
	{
		return $this->doRender($tpl, 'layout-configure.html.tpl');
	}
}

abstract class ConfigurableWidgetController extends AsideConfigurableController {}

?>
