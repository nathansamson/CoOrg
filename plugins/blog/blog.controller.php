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

class BlogController extends Controller
{
	protected $_blog;

	/**
	 * @before fetchLatest $page
	*/
	public function index($page = 1)
	{
		$this->render('latest');
	}
	
	/**
	 * @before fetchLatest
	*/
	public function latest()
	{
		$this->render('latest', false, null);
	}

	/**
	 * @Acl allow blog-writer
	 * @Acl allow blog-admin
	*/
	public function create()
	{
		$this->blog = new Blog('', '', '', CoOrg::getLanguage());
		$this->render('create');
	}
	
	/**
	 * @Acl allow blog-writer
	 * @Acl allow blog-admin
	*/
	public function save($title, $text)
	{
		$blog = new Blog($title, UserSession::get()->username, $text, CoOrg::getLanguage());
		$config = BlogConfig::get();
		$blog->commentsAllowed = $config->enableComments;
		if ($config->enableCommentsFor)
		{
			$blog->commentsCloseDate = time()+60*60*24*$config->enableCommentsFor;
		}
		
		try
		{
			$blog->save();
		
			$this->notice(t('Your blog item is saved'));
			$year = date('Y', $blog->datePosted);
			$month = date('m', $blog->datePosted);
			$day = date('d', $blog->datePosted);
			$this->redirect('blog/show', $year, $month, $day, $blog->ID);
		}
		catch (ValidationException $e)
		{
			$this->blog = $blog;
			$this->error(t('Your blog item is not saved'));
			$this->render('create');
		}
	}
	
	/**
	 * @before get $year $month $day $id $language
	*/
	public function show($year, $month, $day, $id, $language = null)
	{
		$this->blog = $this->_blog;
		$helper = new BlogCommentControllerHelper($this, 'BlogComment');
		$helper->show();
		$this->render('show');
	}
	
	/**
	 * @before get $year $month $day $id $language
	 * @Acl owns $:_blog
	*/
	public function edit($year, $month, $day, $id, $language = null)
	{
		$this->openFor = BlogConfig::openForOptions();
		$this->blog = $this->_blog;
		if ($this->_blog->commentsCloseDate === null)
		{
			$this->currentOpenFor = 0;
		}
		else
		{
			$this->currentOpenFor = (int)(($this->_blog->commentsCloseDate - $this->_blog->timePosted)/(60*60*24));
		}
		$this->render('edit');
	}
	
	/**
	 * @before get $year $month $day $id $language
	 * @Acl owns $:_blog
	*/
	public function update($year, $month, $day, $id, $title, $text, $language,
	                       $commentsAllowed = false, $commentsOpenFor = null)
	{
		$this->_blog->title = $title;
		$this->_blog->text = $text;
		$this->_blog->commentsAllowed = $commentsAllowed;
		$this->_blog->commentsOpenFor = $commentsOpenFor;
		try
		{
			$this->_blog->save();
			
			$this->notice(t('Your blog item is updated'));
			if ($language == CoOrg::getLanguage())
			{
				$this->redirect('blog/show', $year, $month, $day, $this->_blog->ID);
			}
			else
			{
				$this->redirect('blog/show', $year, $month, $day, $this->_blog->ID, $language);
			}
		}
		catch (ValidationException $e)
		{
			$this->error(t('Your blog item is not saved'));
			$this->blog = $this->_blog;
			if ($this->_blog->commentsCloseDate === null)
			{
				$this->currentOpenFor = 0;
			}
			else
			{
				$this->currentOpenFor = (int)(($this->_blog->commentsCloseDate - $this->_blog->timePosted)/(60*60*24));
			}
			$this->render('edit');
		}
	}

	/**
	 * @before get $year $month $day $id $fromLanguage
	 * @Acl allow blog-translator
	 * @Acl owns $:_blog
	*/
	public function translate($year, $month, $day, $id, $fromLanguage, $toLanguage)
	{
		$this->originalBlog = $this->_blog;
		$blog = new Blog('', '', '', '');
		$blog->language = $toLanguage;
		$this->translatedBlog = $blog;
		$this->render('translate');
	}

	/**
	 * @post
	 * @before get $year $month $day $id $fromLanguage
	 * @Acl allow blog-translator
	 * @Acl owns $:_blog
	*/
	public function translateSave($year, $month, $day, $id, $fromLanguage,
	                              $title, $text, $language)
	{
		$original = $this->_blog;

		try
		{
			$t = $original->translate(UserSession::get()->username, $title, $text, $language);
			$this->notice(t('Your translation of the blog is saved'));
			if ($language == CoOrg::getLanguage())
			{
				$this->redirect('blog/show', $year, $month, $day, $t->ID);
			}
			else
			{
				$this->redirect('blog/show', $year, $month, $day, $t->ID, $language);
			}
		}
		catch (ValidationException $e)
		{
			$this->error(t('Blog translation is not saved'));
			$this->originalBlog = $original;
			$blog = new Blog($title, '', $text, '');
			$blog->language = $language;
			$this->translatedBlog = $blog;
			$this->render('translate');
		}
	}
	
	public function archive($year, $month = null)
	{
		$this->blogs = Blog::getArchive(CoOrg::getLanguage(), $year, $month);
		$this->archiveYear = $year;
		if ($month) $this->archiveMonth = $month;
		$this->render('archive');
	}
	
	protected function get($year, $month, $day, $id, $language = null)
	{
		if ($language == null) $language = CoOrg::getLanguage();
		$this->_blog = Blog::getBlog($year, $month, $day, $id, $language);
		if (!$this->_blog)
		{
			$this->error(t('Blog item is not found'));
			$this->notFound();
			return false;
		}
		return true;
	}
	
	protected function fetchLatest($page = 1)
	{
		$pager = Blog::blogs(CoOrg::getLanguage());
		$this->blogs = $pager->execute($page, 10);
		$this->blogpager = $pager;
		return true;
	}
}

?>
