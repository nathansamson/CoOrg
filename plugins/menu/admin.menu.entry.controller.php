<?php

class AdminMenuEntryController extends Controller
{
	private $_entry;

	/**
	 * @post
	 * @Acl allow admin-menu-edit
	*/
	public function save($menu, $language, $title, $entryID, $data)
	{
		$omenu = Menu::get($menu);
		if ($omenu == null)
		{
			$this->error('Menu not found');
			$this->redirect('admin', 'menu', $language);
			return;
		}
	
		$entry = new MenuEntry;
		$entry->menu = $menu;
		$entry->title = $title;
		$entry->data = $data;
		$entry->entryID = $entryID;
		$entry->language = $language;
		
		try
		{
			$entry->save();
			$this->notice('Menu entry added');
			if ($language == I18n::getLanguage())
			{
				$this->redirect('admin', 'menu', 'edit', $menu);
			}
			else
			{
				$this->redirect('admin', 'menu', 'edit', $menu, $language);
			}
		}
		catch (ValidationException $e)
		{
			$this->menu = $omenu;		
			$this->adminlanguage = $language;
			$this->providerActionCombos = Menu::providerActionCombos($language);
			$this->newEntry = $entry;
			$this->error(t('Entry was not saved'));
			$this->render('edit');
		}
	}
	
	/**
	 * @post
	 * @Acl allow admin-menu-edit
	 * @before find $entry
	*/
	public function delete($entry)
	{
		$this->_entry->delete();
		$this->notice('Entry is deleted');
		$this->redirect('admin', 'menu', 'edit',
		                $this->_entry->menu,
		                $this->_entry->language);
	}
	
	/**
	 * @post
	 * @Acl allow admin-menu-edit
	 * @before find $entry
	*/
	public function move($entry, $newsequence)
	{
		$this->_entry->sequence = $newsequence;
		$this->_entry->save();
		$this->notice('Entry is moved');
		$this->redirect('admin', 'menu', 'edit',
		                $this->_entry->menu,
		                $this->_entry->language);
	}
	
	protected function find ($entry)
	{
		$entry = MenuEntry::get($entry);
		if ($entry == null)
		{
			$this->error('Menu entry not found');
			$this->redirect('admin/menu');
			return false;
		}
		$this->_entry = $entry;
		return true;
	}
}
	
?>
