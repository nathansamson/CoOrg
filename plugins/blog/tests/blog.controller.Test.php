<?php

class BlogControllerTest extends CoOrgControllerTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/blog.dataset.xml';
	}

	public function setUp()
	{
		parent::setUp();
		if (UserSession::get())
		{
			UserSession::get()->delete();
		}
	}

	public function testIndex()
	{
		$this->request('blog');
		
		$this->assertVarSet('blogs');
		$this->assertRendered('latest');
	}
	
	public function testCreate()
	{
		$this->login();
		$this->request('blog/create');
		$this->assertVarSet('blog');
		$this->assertRendered('create');
	}
	
	public function testCreateNotLoggedIn()
	{
		$this->request('blog/create');
		$this->assertFlashError('You need to be logged in to create a blog');
		$this->assertRedirected('user/login');
	}
	
	public function testSave()
	{
		$this->login();
		$this->request('blog/save', array('title' => 'My Blog Title',
		                                  'text' => 'My blog contents'));

		$this->assertFlashNotice('Your blog item is saved');
		
		$year = date('Y');
		$month = date('m');
		$day = date('d');
		$this->assertRedirected('blog/show/'.$year.'/'.$month.'/'.$day.'/my-blog-title');
	}
	
	public function testSaveNotLoggedIn()
	{
		$this->request('blog/save', array('title' => 'My Blog Title',
		                                  'text' => 'My blog contents'));

		$this->assertFlashError('You need to be logged in to create a blog');
		$this->assertRedirected('user/login');
	}
	
	public function testSaveFailure()
	{
		$this->login();
		$this->request('blog/save', array('title' => '',
		                                  'text' => 'My blog contents'));

		$this->assertFlashError('Your blog item is not saved');
		$this->assertVarSet('blog');
		$this->assertRendered('create');
	}
	
	public function testShow()
	{
		$this->request('blog/show/2010/04/11/xyz');
		$this->assertVarSet('blog');
		$this->assertRendered('show');
	}
	
	public function testShowNotFound()
	{
		$this->request('blog/show/2010/04/11/not-found');
		$this->assertFlashError('Blog item is not found');
		$this->assertRendered('notfound');
	}
	
	public function testShowWithNonPaddedDates()
	{
		$this->request('blog/show/2010/4/9/blog-post');
		$this->assertVarSet('blog');
		$this->assertRendered('show');
	}
	
	private function login()
	{
		$session = new UserSession('nathan', 'nathan');
		$session->save();
	}
}

?>
