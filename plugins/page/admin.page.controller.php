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
 * @Acl allow admin-page-edit
*/
class AdminPageController extends AdminBaseController
{
	private $_page;	
	
	protected $_adminModule = 'PageAdminModule';
	
	public function index($page = 1)
	{
		$this->_adminTab = 'ManagePagesAdminTab';
		$this->pager = Page::pages(CoOrg::getLanguage());
		$this->pages = $this->pager->execute($page, 20);
		$this->render('admin/index');
	}
	
	/**
	 * @before find $originalID $originalLanguage
	*/
	public function create($originalID = null, $originalLanguage = null, $trLanguage = null)
	{
		$this->_adminTab = 'CreatePageAdminTab';
		$page = new Page;
		$page->language = $trLanguage ? $trLanguage : CoOrg::getLanguage();
		
		if ($originalID)
		{
			$this->originalPage = $this->_page;
			$page->originalID = $originalID;
			$page->originalLanguage = $originalLanguage;
		}
		
		$this->newPage = $page;
		$this->render('admin/create');
	}
	
	public function save($title, $language, $content, $originalID = null, $originalLanguage = null, $preview = null)
	{
		$this->_adminTab = 'CreatePageAdminTab';
		$page = new Page;
		$page->title = $title;
		$page->language = $language;
		$page->content = $content;
		$page->author = UserSession::get();
		
		if ($originalID)
		{
			$page->originalID = $originalID;
			$page->originalLanguage = $originalLanguage;
		}
		
		try
		{
			if (!$preview)
			{
				$page->save();
				if (!$originalID)
				{
					$this->notice(t('New page created'));
					$this->redirect('page', 'show', $page->ID);
				}
				else
				{
					$opage = Page::get($originalID, $originalLanguage);
					$this->notice(t('Saved translation of "%o"', array('o'=>$opage->title)));
					if ($language != CoOrg::getLanguage())
					{
						$this->redirect('page', 'show', $page->ID, $language);
					}
					else
					{
						$this->redirect('page', 'show', $page->ID);
					}
				}
			}
			else
			{
				if ($originalID) $this->originalPage = Page::get($originalID, $originalLanguage);
				$this->newPage = $page;
				$this->render('admin/create');
			}
		}
		catch (ValidationException $e)
		{
			if ($originalID) $this->originalPage = Page::get($originalID, $originalLanguage);
			$this->newPage = $page;
			$this->error(t('Creating page failed'));
			$this->render('admin/create');
		}
	}
	
	/**
	 * @before find $ID
	*/
	public function edit($ID, $redirect = null)
	{
		$this->page = $this->_page;
		if ($redirect) $this->redirect = $redirect;
		$this->render('admin/edit');
	}
	
	/**
	 * @before find $ID
	*/
	public function update($ID, $title, $language, $content, $redirect = null, $preview = null)
	{
		$this->_page->title = $title;
		$this->_page->content = $content;
		$this->_page->lastEditorID = UserSession::get()->username;
		if ($preview)
		{
			$this->preview = 'true';
			$this->page = $this->_page;
			if ($redirect) $this->redirect = $redirect;
			$this->render('admin/edit');
		}
		else
		{
			try
			{
				$this->_page->save();
				$this->notice(t('Page updated'));
				if ($redirect)
				{
					$this->redirect($redirect);
				}
				else
				{
					$this->redirect('page/show', $ID);
				}
			}
			catch (ValidationException $e)
			{
				$this->page = $this->_page;
				if ($redirect) $this->redirect = $redirect;
				$this->error(t('Page is not saved'));
				$this->render('admin/edit');
			}
		}
	}
	
	/**
	 * @before find $ID
	*/
	public function delete($ID)
	{
		$this->_page->delete();
		$this->notice(t('Page is deleted'));
		$this->redirect('admin/page');
	}
	
	protected function find($ID, $language = null)
	{
		if ($ID == null) {return true;}
		$this->_page = Page::get($ID, $language ? $language : CoOrg::getLanguage());
		if (!$this->_page)
		{
			$this->error(t('Page not found'));
			$this->notfound();
			return false;
		}
		else
		{
			return true;
		}
	}
}
