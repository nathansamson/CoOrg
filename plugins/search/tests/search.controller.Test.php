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

$fooSearch = new stdClass;
$fooSearch->title = 'Foos';
$fooSearch->module = 'search';
$fooSearch->file = 'foo-search-results.html.tpl';

Searchable::registerSearch('SearchFoo', $fooSearch);


class SearchControllerTest extends CoOrgControllerTest
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
	
		$foo = new SearchFoo;
		$foo->title = 'My Title';
		$foo->someOtherPrimary = 'other-primary';
		$foo->datePrimary = '2010-01-01';
		$foo->body = 'Somewhere over the rainbow goes, tudeludoedoe <bold>This is some text that has to be searched</bold>';
		$foo->barTitle = 'Bar Title';
		$foo->barSomeOtherPrimary = 'The Primary';
		$foo->identity = 'Keep Me As I am';
		$foo->language = 'en';
		$foo->save();
		
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

	public function testIndex()
	{
		$this->request('search', array('s' => 'somewhere rainbow',
		                               'i' => array('SearchFoo')), false);

		$this->assertVarIs('searchQuery', 'somewhere rainbow');
		$this->assertVarIs('searchIncludes', array('SearchFoo'));
		$results = CoOrgSmarty::$vars['searchResults'];
		$this->assertEquals(1, count($results));
		
		$this->assertEquals('Foos', $results[0]->title);
		$this->assertEquals(1, count($results[0]));
		$this->assertRendered('searchresults');
	}
	
	public function testTagcloud()
	{
		$this->request('search/tagcloud');
		$tags = CoOrgSmarty::$vars['tagcloud'];
		$this->assertEquals(6, count($tags));
		$this->assertRendered('tagcloud');
	}
}

?>
