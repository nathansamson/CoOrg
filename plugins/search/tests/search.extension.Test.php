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

/**
 * @property primary; title String('Title', 64); required
 * @property primary; someOtherPrimary String('Primary', 64); required
 * @property language String('language', 6); required
 * @property body String('The Body'); required
 * @property identity String('Identity');
 * @property barTitle String('Title', 64);
 * @property barSomeOtherPrimary String('Primary', 64);
 * @extends Searchable SearchFooIndex @title @someOtherPrimary title identity:identity body:html :SearchBar:@barTitle:@barSomeOtherPrimary:body :language:language
*/
class SearchFoo extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public static function search($terms, $language)
	{
		return self::callStatic('SearchFoo', 'search', array($terms, $language));
	}
	
	public static function get($title, $primary)
	{
		$q = DB::prepare('SELECT * FROM SearchFoo WHERE
		                    title=:title AND someOtherPrimary=:primary');
		$q->execute(array(':title' => $title, ':primary' => $primary));
		return self::fetch($q->fetch(), 'SearchFoo');
	}
}

/**
 * @property someISAVar String('Isa var'); required
 * @property otherVar Integer('Integer'); required
*/
class SearchFooISA extends SearchFoo
{
	public static function search($terms, $language)
	{
		return self::callStatic('SearchFooISA', 'search', array($terms, $language, 'SearchFooISA'));
	}
}

/**
 * @property primary; title String('Title', 64); required
 * @property primary; someOtherPrimary String('Primary', 64); required
 * @property language String('language', 6); required
 * @property body String('The Body'); required
 * @extends Searchable SearchBarIndex @title @someOtherPrimary title body:html :language:language
*/
class SearchBar extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public static function search($terms, $language)
	{
		return self::callStatic('SearchBar', 'search', array($terms, $language));
	}
}

class SearchableTest extends CoOrgModelTest
{
	const dataset = 'search.dataset.xml';
	
	public function setUp()
	{
		parent::setUp();
	
		$bar = new SearchBar;
		$bar->title = 'Bar Title';
		$bar->someOtherPrimary = 'The Primary';
		$bar->body = 'Lorem Ipsum';
		$bar->language = 'en';
		$bar->save();
		
		$bar = new SearchBar;
		$bar->title = 'Other Bar Title';
		$bar->someOtherPrimary = 'The Primary';
		$bar->body = 'Lorem Ipsum Dolor Sit Amet and more latin';
		$bar->language = 'en';
		$bar->save();
		
		$foo = new SearchFoo;
		$foo->title = 'My Title';
		$foo->someOtherPrimary = 'other-primary';
		$foo->body = 'Somewhere over the rainbow goes, tudeludoedoe <bold>This is some text that has to be searched</bold>';
		$foo->barTitle = 'Bar Title';
		$foo->barSomeOtherPrimary = 'The Primary';
		$foo->identity = 'Keep Me As I am';
		$foo->language = 'en';
		$foo->save();
	
		$foo = new SearchFoo;
		$foo->title = 'My Title';
		$foo->someOtherPrimary = 'primary';
		$foo->body = 'Lorem Ipsum Dolor Sit Amet Title is very important. So I put title here so its very high up into the list.';
		$foo->language = 'en';
		$foo->identity = 'kEEP me';
		$foo->save();
		
		$foo = new SearchFoo;
		$foo->title = 'My Other Title';
		$foo->someOtherPrimary = 'primary';
		$foo->body = 'Aleia iacta est is not the title';
		$foo->language = 'en';
		$foo->save();
		
		
		$foo = new SearchFoo;
		$foo->title = 'Mijn andere titel';
		$foo->someOtherPrimary = 'primary';
		$foo->body = 'Dit is mijn nederlandse tekst, om te zien of het goed werkt. Put some english here (title) so it matches with title, but not when searching for english texts...';
		$foo->language = 'nl';
		$foo->save();
		
		$isa = new SearchFooISA;
		$isa->title = 'Some Dodo';
		$isa->someOtherPrimary = 'some-primary';
		$isa->body = 'I want you to search this ISA for me...';
		$isa->language = 'en';
		$isa->someISAVar = '...';
		$isa->otherVar = 10;
		$isa->save();
	}
	
	public function testNormalSearch()
	{
		$pager = SearchBar::search('lorem latin', 'en');
		$results = $pager->execute(1, 10);
		
		$this->assertEquals(2, count($results));
		$this->assertEquals('Other Bar Title', $results[0]->title);
		$this->assertEquals('The Primary', $results[0]->someOtherPrimary);
		
		$this->assertEquals('Bar Title', $results[1]->title);
		$this->assertEquals('The Primary', $results[1]->someOtherPrimary);
	
		$pager = SearchFoo::search('title', 'en');
		$results = $pager->execute(1, 10);
		
		$this->assertEquals(3, count($results));
		$this->assertEquals('My Title', $results[0]->title);
		$this->assertEquals('primary', $results[0]->someOtherPrimary);
		
		$this->assertEquals('My Other Title', $results[1]->title);
		$this->assertEquals('primary', $results[1]->someOtherPrimary);
		
		$this->assertEquals('My Title', $results[2]->title);
		$this->assertEquals('other-primary', $results[2]->someOtherPrimary);
		
		
		$pager = SearchFoo::search('primary', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
		
		$pager = SearchFoo::search('important', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('My Title', $results[0]->title);
		$this->assertEquals('primary', $results[0]->someOtherPrimary);
		
		$pager = SearchFoo::search('rainbow', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('My Title', $results[0]->title);
		$this->assertEquals('other-primary', $results[0]->someOtherPrimary);
		
		
		$pager = SearchFoo::search('iacta aLeIA title', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(3, count($results));
		$this->assertEquals('My Other Title', $results[0]->title);
		$this->assertEquals('primary', $results[0]->someOtherPrimary);
		
		$this->assertEquals('My Title', $results[1]->title);
		$this->assertEquals('primary', $results[1]->someOtherPrimary);
		
		$this->assertEquals('My Title', $results[2]->title);
		$this->assertEquals('other-primary', $results[2]->someOtherPrimary);
		
		
		$pager = SearchFoo::search('title', 'nl');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('Mijn andere titel', $results[0]->title);
		$this->assertEquals('primary', $results[0]->someOtherPrimary);
		
		$pager = SearchFoo::search('tekst', 'nl');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('Mijn andere titel', $results[0]->title);
		$this->assertEquals('primary', $results[0]->someOtherPrimary);
		
		
		$pager = SearchFoo::search('hola dit', 'nl'); // 'Dit' is filtered out
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
		
		$pager = SearchFoo::search('sjeezel the', 'en'); // 'The' is filtered out
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
	}
	
	public function testISASearch()
	{	
		$pager = SearchFooISA::search('search', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		
		$this->assertEquals('Some Dodo', $results[0]->title);
		$this->assertEquals('some-primary', $results[0]->someOtherPrimary);
		$this->assertEquals(10, $results[0]->otherVar);
		$this->assertEquals('...', $results[0]->someISAVar);
	}
	
	public function testIdentitySearch()
	{
		$pager = SearchFoo::search('keep', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
		
		
		$pager = SearchFoo::search('Keep', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('My Title', $results[0]->title);
		$this->assertEquals('other-primary', $results[0]->someOtherPrimary);
		
		$pager = SearchFoo::search('kEEP me', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('My Title', $results[0]->title);
		$this->assertEquals('primary', $results[0]->someOtherPrimary);
	}
	
	public function testNoTerms()
	{
		try
		{
			$pager = SearchFoo::search('dit', 'nl'); // 'Dit' is filtered out
			$this->fail('Exception expected');
		}
		catch (NoSearchTermsException $e)
		{
		}
		
		try
		{
			$pager = SearchFoo::search('this', 'en'); // 'This' is filtered out
			$this->fail('Exception expected');
		}
		catch (NoSearchTermsException $e)
		{
		}
		
		try
		{
			$pager = SearchFoo::search('', 'nl');
			$this->fail('Exception expected');
		}
		catch (NoSearchTermsException $e)
		{
		}
	}
	
	public function testUpdate()
	{
		$someFoo = SearchFoo::get('My Title', 'other-primary');
		$someFoo->body = 'No more talking about the song...';
		$someFoo->save();
		
		$pager = SearchFoo::search('rainbow', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
		
		$pager = SearchFoo::search('talking song', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('My Title', $results[0]->title);
		$this->assertEquals('other-primary', $results[0]->someOtherPrimary);
	}
	
	public function testDelete()
	{
		// Look up the SIDS
		// Ok, we cant really test this, so we use implementation knowledge
		$q = DB::prepare('SELECT SID FROM SearchFooIndex
		                   WHERE title=:title AND someOtherPrimary=:primary');
		$q->execute(array(':title' => 'My Title', ':primary' => 'other-primary'));
		$SIDS = $q->fetchAll();
	
		$someFoo = SearchFoo::get('My Title', 'other-primary');
		$someFoo->delete();
		
		// See if it is completely removed
		// Ok, we cant really test this, so we use implementation knowledge
		$q = DB::prepare('SELECT * FROM SearchFooIndex
		                   WHERE title=:title AND someOtherPrimary=:primary');
		$q->execute(array(':title' => 'My Title', ':primary' => 'other-primary'));
		$this->assertFalse($q->fetch());
		
		$q = DB::prepare('SELECT * FROM SearchIndex
		                   WHERE SID=:SID');
		$q->execute(array(':SID' => $SIDS[0]['SID']));
		$this->assertFalse($q->fetch());
		
		$pager = SearchFoo::search('rainbow', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
		
		$someFoo = new SearchFoo;
		$someFoo->title = 'My Title';
		$someFoo->someOtherPrimary = 'other-primary';
		$someFoo->body = 'Empty body';
		$someFoo->language = 'en';
		$someFoo->save();
		
		$pager = SearchFoo::search('body', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
	}
	
	public function testRelationSearch()
	{
		$pager = SearchFoo::search('ipsum dolor', 'en');
		$results = $pager->execute(1, 10);
	
		$this->assertEquals(2, count($results));
		$this->assertEquals('My Title', $results[0]->title);
		$this->assertEquals('primary', $results[0]->someOtherPrimary);
		
		$this->assertEquals('My Title', $results[1]->title);
		$this->assertEquals('other-primary', $results[1]->someOtherPrimary);
	}
	
	public function testSimilarSearch()
	{
	}
}

?>
