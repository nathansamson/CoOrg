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

class AdminMenuController extends Controller
{
	private $_menu;

	/**
	 * @Acl allow admin-menu-edit
	*/
	public function index()
	{
		$this->menus = Menu::all();
		$this->newMenu = new Menu;
		$this->render('index');
	}
	
	/**
	 * @post
	 * @Acl allow admin-menu-edit
	*/
	public function save($name, $description)
	{
		$menu = new Menu;
		$menu->name = $name;
		$menu->description = $description;
		
		try
		{
			$menu->save();
			$this->notice(t('Menu created'));
			$this->redirect('admin', 'menu', 'edit', $name);
		}
		catch (ValidationException $e)
		{
			$this->error(t('Menu was not saved'));
			$this->menus = Menu::all();
			$this->newMenu = $menu;
			$this->render('index');
		}
	}
	
	/**
	 * @Acl allow admin-menu-edit
	 * @before find $name
	*/
	public function edit($name, $language = null)
	{
		$this->menu = $this->_menu;
		
		if ($language)
		{
			$adminLanguage = $language;
		}
		else
		{
			$adminLanguage = CoOrg::getLanguage();
		}
		$this->adminlanguage = $adminLanguage;
		$this->providerActionCombos = Menu::providerActionCombos($adminLanguage);
		$this->newEntry = new MenuEntry;
		$this->render('edit');
	}
	
	/**
	 * @Acl allow admin-menu-edit
	 * @before find $name
	*/
	public function update($name, $description, $language = null)
	{
		$this->_menu->description = $description;
		
		try
		{
			$this->_menu->save();
			$this->notice(t('Menu is updated'));
			if ($language)
			{
				$this->redirect('admin', 'menu', 'edit', $name, $language);
			}
			else
			{
				$this->redirect('admin', 'menu', 'edit', $name);
			}	
		}
		catch (ValidationException $e)
		{
			//$this->render('');
		}
	}
	
	/**
	 * @Acl allow admin-menu-edit
	 * @before find $name
	*/
	public function delete($name)
	{
		$this->_menu->delete();
		$this->notice(t('Deleted menu "%n"', array('n'=>$name)));
		$this->redirect('admin', 'menu');
	}
	
	protected function find($name)
	{
		$this->_menu = Menu::get($name);
		if ($this->_menu == null)
		{
			$this->error(t('Menu not found'));
			$this->redirect('admin/menu');
		}
		return $this->_menu != null;
	}
}

?>
