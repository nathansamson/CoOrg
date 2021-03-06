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

class SearchController extends Controller
{
	public function index($s, $i)
	{
		$this->searchResults = Searchable::doSearch($s, 'nl', $i);
		$this->searchQuery = $s;
		$this->searchIncludes = $i;
		$this->render('searchresults');
	}
	
	public function tag($tag)
	{
		$this->searchResults = Taggable::selectNodes($tag, CoOrg::getLanguage());
		$this->render('tagresults');
	}
	
	public function tagcloud()
	{
		$this->tagcloud = Taggable::cloud();
		$this->render('tagcloud');
	}
	
	public function tagsuggest($search)
	{
		$this->suggestions = Taggable::suggestTags($search);
		$this->render('suggestions');
	}
}

?>
