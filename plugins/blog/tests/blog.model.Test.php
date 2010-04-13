<?php

class BlogTest extends CoOrgModelTest
{
	public function __construct()
	{
		parent::__construct();
		$this->_dataset = dirname(__FILE__).'/blog.dataset.xml';
	}
	
	public function testInsert()
	{
		$year = date('Y');
		$month = date('m');
		$day = date('d');
	
		$blog = new Blog('My Title', 'Nathan', 'My Blog contents.');
		$blog->save();
		
		$blog = Blog::getBlog($year, $month, $day, $blog->ID);

		$this->assertNotNull($blog);
		$this->assertEquals('My Title', $blog->title);
		$this->assertEquals('Nathan', $blog->authorID);
		$this->assertEquals('My Blog contents.', $blog->text);
	}
	
	public function testTitleMissing()
	{
		$blog = new Blog('', 'Nathan', 'My Blog contents.');
		
		try
		{
			$blog->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Title is required', $blog->title_error);
		}
	}
	
	public function testAuthorMissing()
	{
		$blog = new Blog('Title', '', 'My Blog contents.');
		
		try
		{
			$blog->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Author is required', $blog->authorID_error);
		}
	}
	
	public function testTextMissing()
	{
		$blog = new Blog('Title', 'Nathan', '');
		
		try
		{
			$blog->save();
			$this->fail('Exception expected');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Content is required', $blog->text_error);
		}
	}
	
	public function testLatest()
	{
		$blogs = Blog::latest(2);
		$this->assertEquals(2, count($blogs));
		$this->assertEquals('XYZ', $blogs[0]->title);
		$this->assertEquals('Some Blog', $blogs[1]->title);
	}
	
	public function testUpdate()
	{
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post');
		$blog->title = 'My Blog Post';
		$blog->text = 'Some New Text';
		
		$blog->save();
		
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post');
		$this->assertEquals('nathan', $blog->authorID);
		$this->assertEquals('2010-04-09', date('Y-m-d', $blog->datePosted));
		$this->assertEquals('My Blog Post', $blog->title);
		$this->assertEquals('Some New Text', $blog->text);
	}
	
	public function testUpdateNoTitle()
	{
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post');
		$blog->title = '';
		$blog->text = 'Some New Text';
		
		try
		{
			$blog->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Title is required', $blog->title_error);
		}
	}
	
	public function testUpdateNoText()
	{
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post');
		$blog->text = '';
		
		try
		{
			$blog->save();
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('Content is required', $blog->text_error);
		}
	}
}

?>
