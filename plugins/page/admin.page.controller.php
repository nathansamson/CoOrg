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

class AdminPageController extends Controller
{
	private $_page;	
	
	/**
	 * @Acl allow admin-page-edit
	*/
	public function index()
	{
		$this->pages = Page::pages(CoOrg::getLanguage());
		$this->render('admin/index');
	}
	
	/**
	 * @Acl allow admin-page-edit
	*/
	public function create()
	{
		$this->newPage = new Page;
		$this->render('admin/create');
	}
	
	/**
	 * @post
	 * @Acl allow admin-page-edit
	*/
	public function save($title, $language, $content, $preview = null)
	{
		$page = new Page;
		$page->title = $title;
		$page->language = $language;
		$page->content = $content;
		$page->author = UserSession::get()->username;
		try
		{
			if (!$preview)
			{
				$page->save();
				$this->notice('New page created');
				$this->redirect('page', 'show', $page->ID);
			}
			else
			{
				$this->newPage = $page;
				$this->render('admin/create');
			}
		}
		catch (ValidationException $e)
		{
			$this->newPage = $page;
			$this->error('Creating page failed');
			$this->render('admin/create');
		}
	}
	
	/**
	 * @Acl allow admin-page-edit
	 * @before find $ID
	*/
	public function edit($ID, $redirect = null)
	{
		$this->page = $this->_page;
		if ($redirect) $this->redirect = $redirect;
		$this->render('admin/edit');
	}
	
	/**
	 * @post
	 * @Acl allow admin-page-edit
	 * @before find $ID
	*/
	public function update($ID, $title, $language, $content, $redirect = null, $preview = null)
	{
		$this->_page->title = $title;
		$this->_page->content = $content;
		$this->_page->lastEditor = UserSession::get()->username;
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
				$this->notice('Page updated');
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
				$this->error('Page is not saved');
				$this->render('admin/edit');
			}
		}
	}
	
	/**
	 * @post
	 * @Acl allow admin-page-edit
	 * @before find $ID
	*/
	public function delete($ID)
	{
		$this->_page->delete();
		$this->notice('Page is deleted');
		$this->redirect('admin/page');
	}
	
	protected function find($ID)
	{
		$this->_page = Page::get($ID, CoOrg::getLanguage());
		if (!$this->_page)
		{
			$this->error('Page not found');
			$this->notfound();
			return false;
		}
		else
		{
			return true;
		}
	}
}
