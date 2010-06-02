<?php

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
			$this->notice('Menu created');
			$this->redirect('admin', 'menu', 'edit', $name);
		}
		catch (ValidationException $e)
		{
			$this->error('Menu was not saved');
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
			$this->notice('Menu is updated');
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
