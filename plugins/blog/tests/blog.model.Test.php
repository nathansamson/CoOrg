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
	
		$blog = new Blog('My Title', 'Nathan', 'My Blog contents.', 'en');
		$time = time();
		$blog->save();
		
		$blog = Blog::getBlog($year, $month, $day, $blog->ID, 'en');

		$this->assertNotNull($blog);
		$this->assertEquals('My Title', $blog->title);
		$this->assertEquals('Nathan', $blog->authorID);
		$this->assertEquals('My Blog contents.', $blog->text);
		$this->assertEquals('en', $blog->language);
		$this->assertTrue(abs($time - $blog->timePosted) <= 2);
		$this->assertNull($blog->timeEdited);
	}
	
	public function testTitleMissing()
	{
		$blog = new Blog('', 'Nathan', 'My Blog contents.', 'en');
		
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
		$blog = new Blog('Title', '', 'My Blog contents.', 'en');
		
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
		$blog = new Blog('Title', 'Nathan', '', 'en');
		
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
		$blogs = Blog::latest('en', 3);
		$this->assertEquals(3, count($blogs));
		$this->assertEquals('XYZ', $blogs[0]->title);
		$this->assertEquals('Some Blog', $blogs[1]->title);
		$this->assertEquals('Some Other Blog', $blogs[2]->title);
		
		$blogs = Blog::latest('en');
		$this->assertEquals(4, count($blogs));
		$this->assertEquals('XYZ', $blogs[0]->title);
		$this->assertEquals('Some Blog', $blogs[1]->title);
		$this->assertEquals('Some Other Blog', $blogs[2]->title);
		$this->assertEquals('Blog post', $blogs[3]->title);
	}
	
	public function testUpdate()
	{
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post', 'en');
		$blog->title = 'My Blog Post';
		$blog->text = 'Some New Text';
		
		$time = time();
		$blog->save();
		
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post', 'en');
		$this->assertEquals('nathan', $blog->authorID);
		$this->assertEquals('2010-04-09', date('Y-m-d', $blog->datePosted));
		$this->assertEquals('2010-04-09 14:20:20', date('Y-m-d H:i:s', $blog->timePosted));
		$this->assertTrue(abs($time - $blog->timeEdited) <= 2);
		$this->assertEquals('My Blog Post', $blog->title);
		$this->assertEquals('Some New Text', $blog->text);
	}
	
	public function testUpdateNoTitle()
	{
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post', 'en');
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
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post', 'en');
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

	public function testTranslate()
	{
		$blog = Blog::getBlog('2010', '4', '10', 'some-other-blog', 'en');
		$this->assertFalse($blog->translatedIn('nl'));
		$this->assertEquals(array(), $blog->translations());
		
		$t = $blog->translate('nathan', 'Vertaalde blog', 'Vertaalde inhoud', 'nl');
		$this->assertTrue($blog->translatedIn('nl'));
		$translations = $blog->translations();
		$this->assertEquals(1, count($translations));

		$tR = Blog::getBlog('2010', '4', '10', $t->ID, 'nl');
		$this->assertNotNull($tR);
		$this->assertEquals($tR, $translations['nl']);
		$this->assertEquals('Vertaalde blog', $tR->title);
		$this->assertEquals('Vertaalde inhoud', $tR->text);
		$this->assertTrue(abs($tR->timePosted - time()) <= 2);
		$this->assertEquals('nathan', $tR->authorID);
		$this->assertEquals('some-other-blog', $tR->parentID);
		$this->assertEquals('en', $tR->parentLanguage);
	}

	public function testTranslateTwice()
	{	
		$blog = Blog::getBlog('2010', '4', '10', 'some-blog', 'en');

		try
		{
			$blog->translate('nathan', 'Vertaalde blog', 'Vertaalde inhoud', 'nl');
			$this->fail('Expected exception');
		}
		catch (ValidationException $e)
		{
			$this->assertEquals('This blog is already translated in this language', $e->instance->text_error);
		}
	}
}

?>
