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

	public function testInsert()
	{
		$year = date('Y');
		$month = date('m');
		$day = date('d');
	
		$blog = new Blog;
		$blog->title = 'My Title';
		$blog->author = User::getUserByName('nathan');
		$blog->text = 'My Blog contents.';
		$blog->language = 'en';
		$time = time();
		$blog->save();
		
		$this->assertSame((int)$year, $blog->year);
		$this->assertSame((int)$month, $blog->month);
		$this->assertSame((int)$day, $blog->day);
		$blog = Blog::getBlog($year, $month, $day, $blog->ID, 'en');

		$this->assertNotNull($blog);
		$this->assertEquals('My Title', $blog->title);
		$this->assertEquals('nathan', $blog->authorID);
		$this->assertEquals('My Blog contents.', $blog->text);
		$this->assertEquals('en', $blog->language);
		$this->assertTrue(abs($time - $blog->timePosted) <= 2);
		$this->assertNull($blog->timeEdited);
		$this->assertSame((int)$year, $blog->year);
		$this->assertSame((int)$month, $blog->month);
		$this->assertSame((int)$day, $blog->day);
	}
	
	public function testInsertDoubleNormalizedTitles()
	{
		$blog = new Blog;
		$blog->title = 'Some Title';
		$blog->text = 'Text';
		$blog->language = 'en';
		$blog->authorID = 'Nathan';
		
		$blog2 = new Blog;
		$blog2->title = $blog->title;
		$blog2->text = 'Text';
		$blog2->language = $blog->language;
		$blog2->authorID = 'Nathan';
		
		$blog->save();
		$blog2->save();
		
		// Same day, same title, same language, no title conflict
		$this->assertNotEquals($blog->ID, $blog2->ID);
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
	
	public function testGetComments()
	{
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$comments = $blog->comments;
		$this->assertEquals(1, count($comments));
		$this->assertEquals('My comment @ xyzer', $comments[0]->comment);
		$this->assertEquals('Re: XYZER', $comments[0]->title);
	}
	
	public function testAddBlogComment()
	{
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$comment = new BlogComment;
		$comment->title = 'RE: XYZ';
		$comment->author = User::getUserByName('nathan');
		$comment->comment = 'Some Comment';
		$blog->comments[] = $comment;
		
		$blog = Blog::getBlog('2010', '04', '10', 'xyzer', 'en');
		$comments = $blog->comments;
		$this->assertEquals(2, count($comments));
		$this->assertEquals('My comment @ xyzer', $comments[0]->comment);
		$this->assertEquals('Re: XYZER', $comments[0]->title);
	}
	
	public function testAllowComments()
	{
		$blog = Blog::getBlog('2010', '04', '10', 'some-blog', 'en');
		$this->assertFalse($blog->allowComments()); // Expired
		
		$blog = Blog::getBlog('2010', '04', '10', 'translated-blog', 'nl');
		$this->assertFalse($blog->allowComments()); // Never allowed
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$this->assertTrue($blog->allowComments()); // Expires in a time when (hopefully) PHP does not exists anymore
		
		$blog = Blog::getBlog('2010', '04', '9', 'blog-post', 'en');
		$this->assertTrue($blog->allowComments()); // Never expires
	}
	
	public function testSetCommentExpirationDate()
	{
		$blog = new Blog;
		$blog->title = 'My New Blog Title';
		$blog->authorID = 'nathan';
		$blog->text = '...';
		$blog->language = 'en';
		$blog->commentsAllowed = true;
		$blog->commentsOpenFor = 14; // 2 weeks
		$blog->save();
		
		$blog = Blog::getBlog($blog->year, $blog->month, $blog->day, $blog->ID, 'en');
		$this->assertTrue($blog->allowComments());
		$this->assertLessThan(5, abs($blog->commentsCloseDate - 60 * 60 * 24 * 14 - time()));
		
		$blog = new Blog;
		$blog->title = 'My New Blog Title With Unlimited comments';
		$blog->authorID = 'nathan';
		$blog->text = '...';
		$blog->language = 'en';
		$blog->commentsAllowed = true;
		$blog->commentsOpenFor = 0; //Unlimited
		$blog->save();
		$blog = Blog::getBlog($blog->year, $blog->month, $blog->day, $blog->ID, 'en');
		$this->assertTrue($blog->allowComments());
		$this->assertNull($blog->commentsCloseDate);
	}
	
	public function testUpdateCommentExpirationDate()
	{
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$blog->text = 'Update';
		$closeDate = $blog->commentsCloseDate;
		$blog->save(); // No change
		
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$this->assertEquals($closeDate, $blog->commentsCloseDate);
		
		$blog->commentsOpenFor = 0; // Unlimited
		$blog->save();
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$this->assertNull($blog->commentsCloseDate);
		
		$blog->commentsOpenFor = 14;
		$blog->save();
		$blog = Blog::getBlog('2010', '04', '10', 'some-other-blog', 'en');
		$this->assertEquals(mktime('09', '20', '20', '4', '24', '2010'), $blog->commentsCloseDate);
	}
	
	public function testEnableCommentsTestExpirationDate()
	{
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertFalse($blog->allowComments());
		$blog->text = 'Update';
		$blog->commentsOpenFor = 14;
		$blog->commentsAllowed = true;
		$blog->save();
		
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertFalse($blog->allowComments());
		$blog->commentsOpenFor = 356*30; // ~30 year
		$blog->save();
		
		$blog = Blog::getBlog('2010', '04', '11', 'xyz', 'en');
		$this->assertTrue($blog->allowComments());
	}

	public function testArchives()
	{
		$archive = Blog::getArchives('br');
		
		$this->assertEquals(7, count($archive));
		$this->assertEquals('2009', $archive[0]->year);
		$this->assertEquals('06', $archive[0]->month);
		$this->assertEquals(1, $archive[0]->posts);
		
		$this->assertEquals('2009', $archive[1]->year);
		$this->assertEquals('03', $archive[1]->month);
		$this->assertEquals(3, $archive[1]->posts);
		
		$this->assertEquals('2009', $archive[2]->year);
		$this->assertEquals('01', $archive[2]->month);
		$this->assertEquals(2, $archive[2]->posts);
		
		$this->assertEquals('2008', $archive[3]->year);
		$this->assertEquals('12', $archive[3]->month);
		$this->assertEquals(1, $archive[3]->posts);
		
		$this->assertEquals('2008', $archive[4]->year);
		$this->assertEquals('08', $archive[4]->month);
		$this->assertEquals(1, $archive[4]->posts);
		
		$this->assertEquals('2008', $archive[5]->year);
		$this->assertEquals('06', $archive[5]->month);
		$this->assertEquals(2, $archive[5]->posts);
		
		$this->assertEquals('2008', $archive[6]->year);
		$this->assertEquals('05', $archive[6]->month);
		$this->assertEquals(1, $archive[6]->posts);
	}
	
	public function testArchive()
	{
		$archive = Blog::getArchive('br', 2009, 1);
		$this->assertEquals(2, count($archive));
		$this->assertEquals('b', $archive[0]->ID);
		$this->assertEquals('a', $archive[1]->ID);
		
		$archive = Blog::getArchive('br', 2009);
		$this->assertEquals(6, count($archive));
		$this->assertEquals('abba', $archive[0]->ID);
		$this->assertEquals('ccdd', $archive[1]->ID);
	}
}

?>
