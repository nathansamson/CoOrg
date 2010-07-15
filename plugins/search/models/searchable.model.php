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

class SearchPager extends Pager
{
	private $_table;

	public function __construct($query, $args, $table)
	{
		parent::__construct($query, $args);
		$this->_table = $table;
	}
	
	protected function fetch($row)
	{
		return DBModel::fetch($row, $this->_table);
	}
}

class SearchNormalizer
{
	private $_blacklist;

	protected function __construct($blacklist)
	{
		if ($blacklist)
		{
			$this->_blacklist = explode("\n", file_get_contents($blacklist));
		}
		else
		{
			$this->_blacklist = array();
		}
	}
	
	public function identity($in)
	{
		$out = array();
		foreach (explode(' ', $in) as $id)
		{
			$out[$id] = 1;
		}
		return $out;
	}
	
	public function normal($in)
	{
		return $this->calculateRelevance(explode(' ', strtolower($in)));
	}
	
	public function html($in)
	{
		return $this->normal(strip_tags($in));
	}
	
	private function calculateRelevance($terms)
	{
		$rel = array();
		foreach ($terms as $term)
		{
			$normTerm = strtolower($term);
			$normTerm = $this->stripNonAlphaNumeric($normTerm);
			$normTerm = trim($normTerm);
			if (!$this->isBlacklisted($normTerm))
			{
				if (array_key_exists($normTerm, $rel))
				{
					$rel[$normTerm]++;
				}
				else
				{
					$rel[$normTerm] = 1;
				}
			}
		}
		return $rel;
	}
	
	public function stripNonAlphaNumeric($s)
	{
		$toStrip = array(';', '.', ',', '¡', '!', '¿', '?', '\'', '"', '`', 
		                 '(', ')', '{', '}', ':', '-');
		foreach ($toStrip as $strip)
		{	
			$s = str_replace($strip, '', $s);
		}
		return $s;
	}
	
	public static function get($language)
	{
		$file = dirname(__FILE__) . '/../blacklist/' . $language . '.txt';
		if (is_file($file))
		{
			return new SearchNormalizer($file);
		}
		else
		{
			return new SearchNormalizer(null);
		}
	}
	
	private function isBlacklisted($term)
	{
		return in_array($term, $this->_blacklist);
	}
}

class NoSearchTermsException extends Exception {}

class Searchable implements IModelExtension
{
	protected $_class;
	protected $_tableIndex;
	private $_fields = array();
	private $_relations = array();
	protected $_keys = array();
	private $_language;
	
	protected $_instance;

	public function __construct($args)
	{
		$this->_class = $args[0];
		$this->_tableIndex = $args[1];
	
		$fields = array_slice($args, 2);
	
		foreach ($fields as $field)
		{
			if ($field[0] != '@' && $field[0] != ':')
			{
				if (strpos($field, ':') !== false)
				{
					list($field, $normalizer) = explode(':', $field);
				}
				else
				{
					$normalizer = 'normal';
				}
				$this->_fields[$field] = $normalizer;
			}
			else if ($field[0] == ':')
			{
				$subFields = array_slice(explode(':', $field), 1); // removes the first empty field
				$searchFields = array();
				$relationKeys = array();
				$table = array_shift($subFields);
				if ($table == 'language')
				{
					$this->_language = array_shift($subFields);
					continue;
				}
				$extension = Model::getExtension('Searchable', 'SearchBar');
				$externalKeys = $extension->_keys;
				
				foreach ($subFields as $subField)
				{
					if ($subField[0] == '@')
					{
						$relationKeys[substr($subField, 1)] = array_shift($externalKeys);
					}
					else
					{
						$searchFields[$subField] = $extension->_fields[$subField];
					}
				}
				$relation = new stdClass;
				$relation->table = $table;
				$relation->index = $extension->_tableIndex;
				$relation->searchFields = $searchFields;
				$relation->relation = $relationKeys;
				$this->_relations[] = $relation;
			}
			else
			{
				// key
				$this->_keys[] = substr($field, 1);
			}
			
		}
	}
	
	public function search($searchString, $language, $class = null)
	{
		if (!$class)
		{
			$class = $this->_class;
		}
		$normalizer = SearchNormalizer::get($language);
		$terms = array_keys($normalizer->normal($searchString));
		if ($terms == array())
		{
			throw new NoSearchTermsException();
		}
		$args = array();
		foreach ($terms as $i=>$term)
		{
			$args[':term'.$i] = $term;
		}
		if ($this->needsIdentTerms())
		{
			$identTerms = array_keys($normalizer->identity($searchString));
			foreach ($identTerms as $i=>$term)
			{
				$args[':identTerm'.$i] = $term;
			}
		}
		else
		{
			$identTerms = null;
		}
		
		$args[':language'] = $language;
		
		if (count($this->_relations) == 0)
		{
			$q = $this->prepareQuery($terms, $identTerms, $class);
			$q .= ' GROUP BY ' . implode(',', $this->_keys) . '
			        ORDER BY SUM(relevance) DESC';
		}
		else
		{
			$qs = array($this->prepareQuery($terms, $identTerms, $class));
			foreach ($this->_relations as $rel)
			{
				$qs[] = $this->prepareForeignQuery($terms, $identTerms, $rel, $class);
			}
			$q = 'SELECT * FROM (' . implode(' UNION ALL ', $qs) . ') AS __temp 
			        GROUP BY ' . implode(',', $this->_keys) . '
		            ORDER BY SUM(relevance) DESC';
		}
		return new SearchPager($q, $args, $class);
	}
	
	public function beforeInsert() { }
	
	public function afterInsert()
	{
		$this->index();
	}
	
	public function beforeUpdate() {}
	
	public function afterUpdate()
	{
		$this->deleteIndex(false);
		$this->index();
	}
	
	public function beforeDelete()
	{
		$this->deleteIndex(true);
	}
	
	public function afterDelete() {}
	
	public function connect($i)
	{
		$this->_instance = $i;
	}
	
	public function hasMethod($name)
	{
		return $name == 'search';
	}
	
	public function hasPublicMethod($name)
	{
		return false;
	}
	
	public function properties()
	{
		return array();
	}
	
	protected function prepareQuery($terms, $identTerms, $class, $fields = null)
	{
		if (!$fields)
		{
			$fields = $this->_fields;
		}
		$wheres = $this->createWheres($fields, $terms, $identTerms);
		$q = 'SELECT ' . $this->fields($class) . ', relevance FROM ' . $class .
		       $this->joins($class) . '
		       NATURAL JOIN ' . $this->_tableIndex . '
               NATURAL JOIN SearchIndex
		       WHERE ' . $this->_class . '.' . $this->_language . '=:language 
		             AND (' . implode(' OR ', $wheres) . ')';
		
		return $q;
	}
	
	private function prepareForeignQuery($terms, $identTerms, $rel, $class)
	{
		$wheres = $this->createWheres($rel->searchFields, $terms, $identTerms);
		$joins = array();
		foreach ($rel->relation as $local => $foreign)
		{
			$joins[] = $this->_class . '.' . $local . ' = ' . $rel->index . '.' . $foreign; 
		}
	
		$q = 'SELECT ' . $this->fields($class) . ', relevance FROM ' . $class .
		      $this->joins($class) . '
		      INNER JOIN ' . $rel->index . ' ON ' . implode(' AND ', $joins) . '
		      NATURAL JOIN SearchIndex
			  WHERE ' . $this->_class . '.' . $this->_language . '=:language 
		             AND (' . implode(' OR ', $wheres) . ')';
		return $q;
	}
	
	private function fields($class)
	{
		if ($class == $this->_class)
		{
			return $this->_class . '.*';
		}
		else
		{
			$tree = Model::getISATree($class);
			$fields = array();
			foreach ($tree as $c)
			{
				$fields[] = $c . '.*';
			}
			return implode(', ', $fields);
		}
	}
	
	private function joins($class)
	{
		if ($class == $this->_class)
		{
			return '';
		}
		else
		{
			$tree = array_reverse(Model::getISATree($class));
			$joins = array();
			array_shift($tree); // Removes first class == $class
			foreach ($tree as $c)
			{
				$joins[] = 'NATURAL JOIN ' . $c;
			}
			return ' ' . implode("\n", $joins) . "\n";
		}
	}
	
	protected function createWheres($fields, $terms, $identTerms)
	{
		$wheres = array();
		foreach ($fields as $field => $norm)
		{
			if ($norm != 'identity')
			{
				$theTerms = $terms;
				$termName = 'term';
			}
			else
			{
				$theTerms = $identTerms;
				$termName = 'identTerm';
			}
			foreach ($theTerms as $i=>$term)
			{
				$wheres[] = '(field=\'' . $field . '\' AND term=:' . $termName . $i . ')';
			}
		}
		return $wheres;
	}
	
	private function index()
	{
		$global = $this->insertGlobalIndexQuery();
		$local = $this->insertLocalIndexQuery();
		
		$languageField = $this->_language;
		$normalizer = SearchNormalizer::get($this->_instance->$languageField);
		foreach ($this->_fields as $fieldName => $func)
		{
			$terms = $normalizer->$func($this->_instance->$fieldName);
			
			foreach ($terms as $term => $relevance)
			{
				$this->insertTerm($fieldName, $term, $relevance, $global, $local);
			}
		}
	}
	
	protected function insertGlobalIndexQuery()
	{
		return DB::prepare('INSERT INTO SearchIndex (field, term, relevance) 
		                         VALUES(:field, :term, :relevance)');
	}
	
	protected function insertLocalIndexQuery()
	{
		$keys = array();
		$connArgs = array();
		foreach ($this->_keys as $key)
		{
			$dbName = $key.'_db';
			$connArgs[':__'.$key] = $this->_instance->$key;
		}
		$connNames = implode(',', $this->_keys);
		$connArgNames = implode(', ', array_keys($connArgs));
		
		$q = DB::prepare('INSERT INTO '.$this->_tableIndex.' (SID, '.$connNames.')
		                       VALUES(:SID, '.$connArgNames.')');
		
		foreach ($connArgs as $arg => $value)
		{
			$q->bindValue($arg, $value);
		}
		return $q;
	}
	
	protected function insertTerm($fieldName, $term, $relevance, $global = null, $local = null)
	{
		if (!$global)
		{
			$global = $this->insertGlobalIndexQuery();
			$local = $this->insertLocalIndexQuery();
		}
	
		$global->execute(array(':field' => $fieldName,
		                  ':term' => $term,
		                  ':relevance' => (string)$relevance));
		$SID = DB::lastInsertID('SearchIndex');
		
		$local->bindParam(':SID', $SID);
		$local->execute();
	}
	
	private function deleteIndex($all)
	{
		$args = array();
		foreach ($this->_keys as $key)
		{
			$args[':__'.$key] = $this->_instance->$key;
		}
		
		$q = DB::prepare($this->deleteIndexQueryString($all));
		$q->execute($args);
	}
	
	protected function deleteIndexQueryString($all)
	{
		$wheres = array();
		foreach ($this->_keys as $key)
		{
			$wheres[] = $key . '= :__'.$key;
		}
		$where = implode(' AND ', $wheres);
	
		return 'DELETE FROM SearchIndex WHERE
		                    SID IN (SELECT SID FROM ' . $this->_tableIndex . '
		                            WHERE '.$where.')';
	}
	
	protected function deleteSingleTerm($field, $term)
	{
		$args = array();
		foreach ($this->_keys as $key)
		{
			$args[':__'.$key] = $this->_instance->$key;
		}
		$args[':term'] = $term;
		$args[':field'] = $field;
		
		// urgh, I set it too true to not interfere with Taggable::deleteIndexQS (the only user of this function)
		// but ofcourse this is not true...
		$q = DB::prepare($this->deleteIndexQueryString(true) . ' AND term=:term AND field=:field');
		$q->execute($args);
	}
	
	protected function needsIdentTerms()
	{
		if (in_array('identity', $this->_fields))
		{
			return true;
		}
		else
		{
			foreach ($this->_relations as $rel)
			{
				if (in_array('identity', $rel->searchFields))
				{
					return true;
				}
			}
		}
		return false;
	}
}

?>
