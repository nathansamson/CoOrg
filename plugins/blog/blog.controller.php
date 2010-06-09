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
	private $_blog;

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
	 * @Acl allow admin
	*/
	public function create()
	{
		$this->blog = new Blog('', '', '', CoOrg::getLanguage());
		$this->render('create');
	}
	
	/**
	 * @Acl allow blog-writer
	 * @Acl allow admin
	*/
	public function save($title, $text)
	{
		$blog = new Blog($title, UserSession::get()->username, $text, CoOrg::getLanguage());
		
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
		$this->render('show');
	}
	
	/**
	 * @before get $year $month $day $id $language edit
	*/
	public function edit($year, $month, $day, $id, $language = null)
	{
		$this->blog = $this->_blog;
		$this->render('edit');
	}
	
	/**
	 * @before get $year $month $day $id $language true
	*/
	public function update($year, $month, $day, $id, $title, $text, $language)
	{
		$this->_blog->title = $title;
		$this->_blog->text = $text;
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
			$this->render('edit');
		}
	}

	/**
	 * @before get $year $month $day $id $fromLanguage
	 * @Acl allow blog-writer
	 * @Acl allow blog-translator
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
	 * @Acl allow blog-writer
	 * @Acl allow blog-translator
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
	
	protected function get($year, $month, $day, $id, $language = null, $author = false)
	{
		if ($language == null)
			$language = CoOrg::getLanguage();
		$this->_blog = Blog::getBlog($year, $month, $day, $id, $language);
		if (!$this->_blog || ($author == true && $this->_blog->authorID != UserSession::get()->username))
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
