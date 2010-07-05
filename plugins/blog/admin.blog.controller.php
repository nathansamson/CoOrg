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

class AdminBlogController extends AdminBaseController
{
	protected $_adminModule = 'BlogAdminModule';

	/**
	 * @Acl allow blog-writer
	*/
	public function index($page = 1)
	{
		$this->_adminTab = 'BlogManageAdminTab';
		$blogPager = Blog::blogs(CoOrg::getLanguage());
		$this->blogs = $blogPager->execute($page, 15);
		$this->blogpager = $blogPager;
		$this->render('admin/index');
	}
	
	/**
	 * @Acl allow blog-admin
	*/
	public function config()
	{
		$this->_adminTab = 'BlogConfigureAdminTab';
		
		$this->openForOptions = BlogConfig::openForOptions();
		$this->moderationTimeOptions = BlogConfig::moderationTimeOptions();
		$this->blogConfig = BlogConfig::get();
		$this->render('admin/config');
	}
	
	/**
	 * @Acl allow blog-admin
	*/
	public function configsave($enableComments, $enableCommentsFor,
	                           $moderationEmail, $moderationTime)
	{
		$config = BlogConfig::get();
		$config->enableComments = $enableComments;
		$config->enableCommentsFor = $enableCommentsFor;
		$config->moderationEmail = $moderationEmail;
		$config->moderationTime = $moderationTime;
		try
		{
			$config->save();
		
			$this->notice(t('Saved blog configuration'));
			$this->redirect('admin/blog/config');
		}
		catch (ValidationException $e)
		{
			$this->_adminTab = 'BlogConfigureAdminTab';
		
			$this->openForOptions = BlogConfig::openForOptions();
			$this->moderationTimeOptions = BlogConfig::moderationTimeOptions();
			$this->blogConfig = $config;
			$this->error('Blog configuration not saved');
			$this->render('admin/config');
		}
	}
}
