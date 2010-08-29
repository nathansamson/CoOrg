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

class Taggable extends Searchable
{
	private static $_searches;

	public function __construct($args)
	{
		parent::__construct($args);
	}
	
	public function hasMethod($name)
	{
		return $name == 'tagged' || parent::hasMethod($name);
	}
	
	public function hasPublicMethod($name)
	{
		return ($name == 'tag' || $name == 'untag' || $name == 'tags' ||
		        parent::hasPublicMethod($name));
	}
	
	public function tagged($tag, $language, $orderBy = null)
	{
		$terms = array();
		$identTerms = array($tag);
	
		$args = array(':identTerm0' => $tag,
		              ':language' => $language);
	
		$q = $this->prepareQuery($terms, $identTerms, $this->_class, array('tag'));
		$q .= ' GROUP BY ' . implode(',', $this->_keys);
		if ($orderBy)
		        $q .= ' ORDER BY ' . $orderBy;
		return new SearchPager($q, $args, $this->_class);
	}
	
	public function tag($tag)
	{
		// Most simple method to prevent double tagging
		$this->deleteSingleTerm('tag', $tag);
		$this->insertTerm('tag', $tag, 1);
	}
	
	public function untag($tag)
	{
		$this->deleteSingleTerm('tag', $tag);
	}
	
	public function tags()
	{
		$args = array(':field' => 'tag');
		$q = DB::prepare('SELECT term FROM ' . $this->_tableIndex . '
		      NATURAL JOIN SearchIndex
		      WHERE field=:field AND ' . implode(' AND ', $this->fix($args)) . '
		      ORDER BY term');
		
		
		$q->execute($args);
		
		$tags = array();
		foreach ($q->fetchAll() as $row)
		{
			$tags[] = $row['term'];
		}
		return $tags;
	}
	
	public static function cloud($limit = 0)
	{
		$qs = 'SELECT * FROM 
		                         (SELECT term AS tag, COUNT(*) AS cnt FROM 
		                               SearchIndex
		                            WHERE field=:tag
		                            GROUP BY term';
		if ($limit != 0)
		{
			$qs .= ' ORDER BY COUNT(*) * COUNT(*) * COUNT(*) * RAND() DESC LIMIT ' . $limit;
		}
		$qs .=  ' ) AS tmp ORDER BY tag';
		
		$qlimits = DB::prepare('SELECT MAX(cnt) AS max, MIN(cnt) AS min FROM (SELECT COUNT(*) AS cnt FROM SearchIndex
		                               WHERE field=:tag GROUP BY term) AS tmp');
		$qlimits->execute(array(':tag' => 'tag'));
		$limits = $qlimits->fetch();
		$max = $limits['max'];
		$min = $limits['min'];
		                               
	
		$q = DB::prepare($qs);
		$q->execute(array(':tag' => 'tag'));
		
		$cloud = array();
		$treshold = ($max - $min) / 4; // (5 levels, 4 boundaries);
		foreach ($q->fetchAll() as $row)
		{
			$tag = new stdClass;
			$tag->name = $row['tag'];
			$tag->size = ($row['cnt'] - $min) ? ceil(($row['cnt'] - $min) / $treshold) : 1; // Even if min == cnt size => 1
			$cloud[] = $tag;
		}
		
		return $cloud;
	}
	
	public static function suggestTags($search)
	{
		$q = DB::prepare('SELECT term FROM SearchIndex WHERE LOWER(term) LIKE LOWER(:input) GROUP BY term ORDER BY term');
		$q->execute(array(':input' => '%'.$search.'%'));
		
		$suggestions = array();
		foreach ($q->fetchAll() as $row)
		{
			$suggestions[] = $row['term'];
		}
		return $suggestions;
	}
	
	public static function registerSearch($name, $class)
	{
		parent::registerSearch($name, $class);
		self::$_searches[$name] = $class;
	}
	
	public static function selectNodes($tag, $language)
	{
		CoOrg::loadPluginInfo('search');
		$results = array();
		foreach (self::$_searches as $table => $r)
		{
			$r->results = SearchHack::callStatic($table, 'tagged', array($tag, $language));
			$results[] = $r;
		}
		return $results;
	}
	
	protected function createWheres($fields, $terms, $identTerms)
	{
		$wheres = parent::createWheres($fields, $terms, $identTerms);
		foreach ($identTerms as $i=>$term)
		{
			$wheres[] = '(field=\'tag\' AND term=:identTerm' . $i . ')';
		}
		return $wheres;
	}
	
	protected function needsIdentTerms()
	{
		return true;
	}
	
	protected function deleteIndexQueryString($all)
	{
		$q = parent::deleteIndexQueryString($all);
		if (!$all) $q .= ' AND (NOT field = \'tag\')';
		return $q;
	}
	
	private function fix(&$args)
	{
		$wheres = array();
		foreach ($this->_keys as $key)
		{
			$wheres[] = $key . '=:' . $key;
			$db = $key . '_db';
			$args[':' . $key] = $this->_instance->$db;
		}
		return $wheres;
	}
}

?>
