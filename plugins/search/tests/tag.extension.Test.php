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
 * @property language String('language', 6); required
 * @property body String('The Body'); required
 * @extends Taggable TaggingIndex @title title body:html :language:language
*/
class Tagging extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public static function get($ID)
	{
		$q = DB::prepare('SELECT * FROM Tagging WHERE title=:title');
		$q->execute(array(':title' => $ID));
		return self::fetch($q->fetch(), 'Tagging');
	}
	
	public static function search($terms, $language)
	{
		return DBModel::callStatic('Tagging', 'search', array($terms, $language));
	}
	
	public static function tagged($tag, $language)
	{
		return DBModel::callStatic('Tagging', 'tagged', array($tag, $language, 'title'));		
	}
}

class TaggingTest extends CoOrgModelTest
{
	const dataset = 'search.dataset.xml';

	public function setUp()
	{
		parent::setUp();
		$tag = new Tagging;
		$tag->title = 'Some Title';
		$tag->language = 'en';
		$tag->body = 'Some Very Long body.';
		$tag->save();
		$tag->tag('Gamma');
		$tag->tag('Alpha');
		$tag->tag('Beta');
		
		$tag = new Tagging;
		$tag->title = 'Some Other Title';
		$tag->language = 'en';
		$tag->body = 'Some Very short body.';
		$tag->save();
		$tag->tag('Aay');
		$tag->tag('Bee');
		$tag->tag('Cee');
	}
	
	public function testSearch()
	{
		$pager = Tagging::search('long body Aay Bee', 'en');
		$results = $pager->execute(1, 10);
		
		$this->assertEquals(2, count($results));
		$this->assertEquals('Some Other Title', $results[0]->title);
		$this->assertEquals('Some Title', $results[1]->title);
	}
	
	public function testTags()
	{
		$tag1 = Tagging::get('Some Title');
		$tag2 = Tagging::get('Some Other Title');
	
		$this->assertEquals(array('Alpha', 'Beta', 'Gamma'), $tag1->tags());
		$this->assertEquals(array('Aay', 'Bee', 'Cee'), $tag2->tags());
	}
	
	public function testUntag()
	{
		$tag = Tagging::get('Some Title');
		$tag->untag('Alpha');
		
		$this->assertEquals(array('Beta', 'Gamma'), $tag->tags());
		
		$pager = Tagging::search('Alpha', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
	}
	
	public function testTagTwice()
	{
		$pager = Tagging::search('Gamma body Aay Bee', 'en');
		$results = $pager->execute(1, 10);
		
		$this->assertEquals(2, count($results));
		$this->assertEquals('Some Other Title', $results[0]->title);
		$this->assertEquals('Some Title', $results[1]->title);
	
		// This is not a MultiTag (where a tag can have a value)
		// The result should stay the same
		$tag = Tagging::get('Some Title');
		for ($i = 0; $i <= 100; $i++)
		{
			$tag->tag('Gamma');
		}
		$this->assertEquals(array('Alpha', 'Beta', 'Gamma'), $tag->tags());
		
		$pager = Tagging::search('Gamma body Aay Bee', 'en');
		$results = $pager->execute(1, 10);
		
		$this->assertEquals(2, count($results));
		$this->assertEquals('Some Other Title', $results[0]->title);
		$this->assertEquals('Some Title', $results[1]->title);
	}
	
	public function testGetByTag()
	{
		$tag = Tagging::get('Some Title');
		$tag->tag('Aay');
		
		$pager = Tagging::tagged('Aay', 'en');
		$tagged = $pager->execute(1, 10);
		$this->assertEquals(2, count($tagged));
		$this->assertEquals('Some Other Title', $tagged[0]->title);
		$this->assertEquals('Some Title', $tagged[1]->title);
	}
	
	public function testDelete()
	{
		$tag = Tagging::get('Some Title');
		$tag->delete();
		
		$tag = new Tagging;
		$tag->title = 'Some Title';
		$tag->language = 'en';
		$tag->body = 'Some Very Long body.';
		$tag->save();
		$this->assertEquals(array(), $tag->tags());
	}
	
	public function testUpdate()
	{
		$tag = Tagging::get('Some Title');
		$tag->body = 'Toreador, a spanjaard heeft een snor';
		$tag->save();
		
		$this->assertEquals(array('Alpha', 'Beta', 'Gamma'), $tag->tags());
		
		$pager = Tagging::search('Toreador', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(1, count($results));
		$this->assertEquals('Some Title', $results[0]->title);
		
		
		$pager = Tagging::search('long', 'en');
		$results = $pager->execute(1, 10);
		$this->assertEquals(0, count($results));
	}
}
