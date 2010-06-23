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

/**
 * @Acl allow admin-layout
*/
class AdminLayoutController extends Controller
{
	private $_panel;
	private $_widget;

	public function index()
	{
		$this->render('layout/index');
	}
	
	/**
	 * @before find $panelID $widgetID
	*/
	public function edit($panelID, $widgetID)
	{
		$this->editWidgetID = $widgetID;
		$this->editPanelID = $panelID;
		$this->editWidget = $this->_widget;
		$this->render('layout/index');
	}
	
	/**
	 * @before find $panelID $widgetID
	*/
	public function update($panelID, $widgetID, $_ = array())
	{
		$_['widgetID'] = $this->_widget['widgetID'];
		$this->_panel[(int)$widgetID] = $_;
		CoOrg::config()->set('aside/'.$panelID, $this->_panel);
		CoOrg::config()->save();
		$this->notice('Widget onfiguration saved');
		$this->redirect('admin/layout/edit', $panelID, $widgetID);
	}
	
	/**
	 * @post
	 * @before find $panelID $widgetID
	*/
	public function move($panelID, $widgetID, $to)
	{
		$widgetID = (int)$widgetID;
		$to = (int)$to;
		if ($widgetID < $to)
		{
			for ($i = $widgetID+1; $i <= $to; $i++)
			{
				$this->_panel[$i-1] = $this->_panel[$i];
			}
		}
		else
		{
			for ($i = $widgetID-1; $i >= $to; $i--)
			{
				$this->_panel[$i+1] = $this->_panel[$i];
			}
		}
		$this->_panel[$to] = $this->_widget;
		CoOrg::config()->set('aside/'.$panelID, $this->_panel);
		CoOrg::config()->save();
		$this->notice(t('Moved widget'));
		$this->redirect('admin/layout');
	}
	
	public function delete($panelID, $widgetID)
	{
		$panel = CoOrg::config()->get('aside/'.$panelID);
		$widgetID = (int)$widgetID;
		
		$widget = $panel[$widgetID];

		for ($i = $widgetID+1; $i < count($panel); $i++)
		{
			$panel[$i-1] = $panel[$i];
		}
		unset($panel[count($panel)-1]);
		CoOrg::config()->set('aside/'.$panelID, $panel);
		CoOrg::config()->save();
		$this->notice(t('Moved widget'));
		$this->redirect('admin/layout');
	}
	
	protected function find($panelID, $widgetID)
	{
		$this->_panel = CoOrg::config()->get('aside/'.$panelID);
		if ($this->_panel === null)
		{
			$this->error(t('Panel not found'));
			$this->redirect('admin/layout');
		}
		$widgetID = (int)$widgetID;
		if ($widgetID >= count($this->_panel))
		{
			$this->error(t('Widget not found'));
			$this->redirect('admin/layout');
		}
		
		$this->_widget = $this->_panel[$widgetID];
		return true;
	}
}
