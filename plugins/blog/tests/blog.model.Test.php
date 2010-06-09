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

class BlogTest extends CoOrgModelTest
{
	const dataset = 'blog.dataset.xml';

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
	
	public function testBlogs()
	{
		$blogPager = Blog::blogs('en');
		$blogs = $blogPager->execute(1, 3);
		$this->assertEquals(3, count($blogs));
		$this->assertEquals('XYZ', $blogs[0]->title);
		$this->assertEquals('XYZER', $blogs[1]->title);
		$this->assertEquals('Some Blog', $blogs[2]->title);
		
		$blogs = $blogPager->execute(2, 3);
		$this->assertEquals(2, count($blogs));
		$this->assertEquals('Some Other Blog', $blogs[0]->title);
		$this->assertEquals('Blog post', $blogs[1]->title);
		
		$blogs = $blogPager->execute(1, 10);
		$this->assertEquals(5, count($blogs));
		$this->assertEquals('XYZ', $blogs[0]->title);
		$this->assertEquals('XYZER', $blogs[1]->title);
		$this->assertEquals('Some Blog', $blogs[2]->title);
		$this->assertEquals('Some Other Blog', $blogs[3]->title);
		$this->assertEquals('Blog post', $blogs[4]->title);
	}
	
	public function testUpdate()
	{
		$blog = Blog::getBlog('2010', '4', '9', 'blog-post', 'en');
		$this->assertNotNull($blog);
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
		$this->assertNotNull($blog);
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
		$this->assertNotNull($blog);
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
		$this->assertNotNull($blog);
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
		
		// Translations should work in other direction
		$this->assertTrue($t->translatedIn('en'));
		$translations = $t->translations();
		$this->assertEquals(1, count($translations));
		$this->assertEquals($blog, $translations['en']);
	}

	public function testTranslateTwice()
	{	
		$blog = Blog::getBlog('2010', '4', '10', 'some-blog', 'en');
		$this->assertNotNull($blog);

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
	
	public function testTranslateChain()
	{
		$blog = Blog::getBlog('2010', '04', '10', 'translated-blog', 'nl');
		$tr = $blog->translate('nathan', 'Translation', 'Translation of the blog', 'fr');
		
		$this->assertEquals($tr->parentID, $blog->parentID);
		$this->assertEquals($tr->parentLanguage, $blog->parentLanguage);
		
		$trs = $blog->translations();
		$this->assertEquals(2, count($trs));
		$this->assertEquals('Translation', $trs['fr']->title);
		$this->assertEquals('Some Blog', $trs['en']->title);
		
		$trs = $tr->translations();
		$this->assertEquals(2, count($trs));
		$this->assertEquals('Mijn Vertaalde Blog', $trs['nl']->title);
		$this->assertEquals('Some Blog', $trs['en']->title);
		
		$trs = Blog::getBlog('2010', '04', '10', 'some-blog', 'en')->translations();
		$this->assertEquals(2, count($trs));
		$this->assertEquals('Mijn Vertaalde Blog', $trs['nl']->title);
		$this->assertEquals('Translation', $trs['fr']->title);
	}
	
	public function testUntranslated()
	{
		$blog = Blog::getBlog('2010', '04', '10', 'translated-blog', 'nl');
		$untranslated = $blog->untranslated();
		$this->assertEquals(2, count($untranslated));
		$this->assertEquals('Français', $untranslated[0]->name);
		$this->assertEquals('German', $untranslated[1]->name);
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-blog', 'en');
		$untranslated2 = $blog->untranslated();
		$this->assertEquals($untranslated, $untranslated2);
	}
}

?>
