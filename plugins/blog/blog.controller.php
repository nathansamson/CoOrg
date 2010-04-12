<?php

class BlogController extends Controller
{

	public function index()
	{
		$this->blogs = Blog::latest(10);
		$this->render('latest');
	}

	public function create()
	{
		if (UserSession::get())
		{
			$this->blog = new Blog('', '', '');
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
	*/
	public function save($title, $text)
	{
		if (!UserSession::get())
		{
			$this->error('You need to be logged in to create a blog');
			$this->redirect('user/login');
			return;
		}
		$blog = new Blog($title, UserSession::get()->username, $text);
		
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
	
	public function show($year, $month, $day, $id)
	{
		$blog = Blog::getBlog($year, $month, $day, $id);
		if ($blog)
		{
			$this->blog = $blog;
			$this->render('show');
		}
		else
		{
			$this->error('Blog item is not found');
			$this->notFound();
		}
	}
}

?>
