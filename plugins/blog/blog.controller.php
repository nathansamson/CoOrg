<?php

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
		if (UserSession::get())
		{
			$this->blog = new Blog('', '', '', CoOrg::getLanguage());
			$this->render('create');
		}
		else
		{
			$this->error('You need to be logged in to create a blog');
			$this->redirect('user/login');
		}
	}
	
	/**
	 * @post
	 * @Acl allow blog-writer
	 * @Acl allow admin
	*/
	public function save($title, $text)
	{
		if (!UserSession::get())
		{
			$this->error('You need to be logged in to create a blog');
			$this->redirect('user/login');
			return;
		}
		$blog = new Blog($title, UserSession::get()->username, $text, CoOrg::getLanguage());
		
		try
		{
			$blog->save();
		
			$this->notice('Your blog item is saved');
			$year = date('Y', $blog->datePosted);
			$month = date('m', $blog->datePosted);
			$day = date('d', $blog->datePosted);
			$this->redirect('blog/show', $year, $month, $day, $blog->ID);
		}
		catch (ValidationException $e)
		{
			$this->blog = $blog;
			$this->error('Your blog item is not saved');
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
	
	public function edit($year, $month, $day, $id)
	{
		$blog = Blog::getBlog($year, $month, $day, $id, CoOrg::getLanguage());
		if ($blog && $blog->authorID == UserSession::get()->username)
		{
			$this->blog = $blog;
			$this->render('edit');
		}
		else
		{
			$this->error('Blog item is not found');
			$this->notFound();
		}
	}
	
	/**
	 * @post
	*/
	public function update($year, $month, $day, $id, $title, $text)
	{
		$blog = Blog::getBlog($year, $month, $day, $id, CoOrg::getLanguage());
		if ($blog && $blog->authorID == UserSession::get()->username)
		{
			$blog->title = $title;
			$blog->text = $text;
			try
			{
				$blog->save();
				
				$this->notice('Your blog item is updated');
				$this->redirect('blog/show', $year, $month, $day, $blog->ID);
			}
			catch (ValidationException $e)
			{
				$this->error('Your blog item is not saved');
				$this->blog = $blog;
				$this->render('edit');
			}
		}
		else
		{
			$this->error('Blog item is not found');
			$this->notFound();
		}
	}

	/**
	 * @before get $year $month $day $id $fromLanguage
	 * @Acl allow blog-writer
	 * @Acl allow blog-translator
	*/
	public function translate($year, $month, $day, $id, $fromLanguage)
	{
		$this->originalBlog = Blog::getBlog($year, $month, $day, $id, $fromLanguage);
		$this->translatedBlog = new Blog('', '', '', '');
		$this->render('translate');
	}

	/**
	 * @post
	 * @Acl allow blog-writer
	 * @Acl allow blog-translator
	*/
	public function translateSave($year, $month, $day, $id, $fromLanguage,
	                              $title, $text)
	{
		$original = Blog::getBlog($year, $month, $day, $id, $fromLanguage);

		try
		{
			$t = $original->translate(UserSession::get()->username, $title, $text, CoOrg::getLanguage());
			$this->notice('Your translation of the blog is saved');
			$this->redirect('blog/show', $year, $month, $day, $t->ID);
		}
		catch (ValidationException $e)
		{
			$this->error('Blog translation is not saved');
			$this->originalBlog = $original;
			$this->translatedBlog = new Blog($title, '', $text, '');
			$this->render('translate');
		}
	}
	
	protected function get($year, $month, $day, $id, $language = null)
	{
		if ($language == null)
			$language = CoOrg::getLanguage();
		$this->_blog = Blog::getBlog($year, $month, $day, $id, $language);
		if (!$this->_blog)
		{
			$this->error('Blog item is not found');
			$this->notFound();
			return false;
		}
		return true;
	}
}

?>
