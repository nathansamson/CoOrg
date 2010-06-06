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

	public function index()
	{
		$this->blogs = Blog::latest(CoOrg::getLanguage(), 10);
		$this->render('latest');
	}
	
	public function latest()
	{
		$this->blogs = Blog::latest(CoOrg::getLanguage(), 10);
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
	 * @before get $year $month $day $id
	*/
	public function show($year, $month, $day, $id)
	{
		$this->blog = $this->_blog;
		$this->render('show');
	}
	
	/**
	 * @before get $year $month $day $id null edit
	*/
	public function edit($year, $month, $day, $id)
	{
		$this->blog = $this->_blog;
		$this->render('edit');
	}
	
	/**
	 * @before get $year $month $day $id null true
	*/
	public function update($year, $month, $day, $id, $title, $text)
	{
		$this->_blog->title = $title;
		$this->_blog->text = $text;
		try
		{
			$this->_blog->save();
			
			$this->notice(t('Your blog item is updated'));
			$this->redirect('blog/show', $year, $month, $day, $this->_blog->ID);
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
	public function translate($year, $month, $day, $id, $fromLanguage)
	{
		$this->originalBlog = $this->_blog;
		$this->translatedBlog = new Blog('', '', '', '');
		$this->render('translate');
	}

	/**
	 * @post
	 * @before get $year $month $day $id $fromLanguage
	 * @Acl allow blog-writer
	 * @Acl allow blog-translator
	*/
	public function translateSave($year, $month, $day, $id, $fromLanguage,
	                              $title, $text)
	{
		$original = $this->_blog;

		try
		{
			$t = $original->translate(UserSession::get()->username, $title, $text, CoOrg::getLanguage());
			$this->notice(t('Your translation of the blog is saved'));
			$this->redirect('blog/show', $year, $month, $day, $t->ID);
		}
		catch (ValidationException $e)
		{
			$this->error(t('Blog translation is not saved'));
			$this->originalBlog = $original;
			$this->translatedBlog = new Blog($title, '', $text, '');
			$this->render('translate');
		}
	}
	
	protected function get($year, $month, $day, $id, $language = null, $author = false)
	{
		if ($language == null || $language == 'null')
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
}

?>
