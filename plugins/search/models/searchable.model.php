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

class Searchable
{
	private $_class;
	private $_tableIndex;
	private $_fields = array();
	private $_relations = array();
	private $_keys = array();
	private $_language;
	
	private $_instance;

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
	
	public function search($searchString, $language)
	{
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
			$q = $this->prepareQuery($terms, $identTerms);
			$q .= ' GROUP BY ' . implode(',', $this->_keys) . '
			        ORDER BY SUM(relevance) DESC';
		}
		else
		{
			$qs = array($this->prepareQuery($terms, $identTerms));
			foreach ($this->_relations as $rel)
			{
				$qs[] = $this->prepareForeignQuery($terms, $identTerms, $rel);
			}
			$q = 'SELECT * FROM (' . implode(' UNION ALL ', $qs) . ') AS __temp 
			        GROUP BY ' . implode(',', $this->_keys) . '
		            ORDER BY SUM(relevance) DESC';
		}
		return new SearchPager($q, $args, $this->_class);
	}
	
	public function beforeInsert() { }
	
	public function afterInsert()
	{
		$this->index();
	}
	
	public function beforeUpdate() {}
	
	public function afterUpdate()
	{
		$this->deleteIndex();
		$this->index();
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
	
	public function properties()
	{
		return array();
	}
	
	private function prepareQuery($terms, $identTerms)
	{
		$wheres = $this->createWheres($this->_fields, $terms, $identTerms);
		$q = 'SELECT ' . $this->_class . '.*, relevance FROM ' . $this->_class . '
		       NATURAL JOIN ' . $this->_tableIndex . '
               NATURAL JOIN SearchIndex
		       WHERE ' . $this->_class . '.' . $this->_language . '=:language 
		             AND (' . implode(' OR ', $wheres) . ')';
		
		return $q;
	}
	
	private function prepareForeignQuery($terms, $identTerms, $rel)
	{
		$wheres = $this->createWheres($rel->searchFields, $terms, $identTerms);
		$joins = array();
		foreach ($rel->relation as $local => $foreign)
		{
			$joins[] = $this->_class . '.' . $local . ' = ' . $rel->index . '.' . $foreign; 
		}
	
		$q = 'SELECT ' . $this->_class . '.*, relevance FROM ' . $this->_class . '
		      INNER JOIN ' . $rel->index . ' ON ' . implode(' AND ', $joins) . '
		      NATURAL JOIN SearchIndex
			  WHERE ' . $this->_class . '.' . $this->_language . '=:language 
		             AND (' . implode(' OR ', $wheres) . ')';
		return $q;
	}
	
	private function createWheres($fields, $terms, $identTerms)
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
		$keys = array();
		$connArgs = array();
		foreach ($this->_keys as $key)
		{
			$dbName = $key.'_db';
			$connArgs[':__'.$key] = $this->_instance->$key;
		}
		$connNames = implode(',', $this->_keys);
		$connArgNames = ':__'.implode(',:__', $this->_keys);
		
		$q = DB::prepare('INSERT INTO SearchIndex (field, term, relevance) 
		                         VALUES(:field, :term, :relevance)');

		$q2 = DB::prepare('INSERT INTO '.$this->_tableIndex.' (SID, '.$connNames.')
		                       VALUES(:SID, '.$connArgNames.')');
		$languageField = $this->_language;
		$normalizer = SearchNormalizer::get($this->_instance->$languageField);
		foreach ($this->_fields as $fieldName => $func)
		{
			$terms = $normalizer->$func($this->_instance->$fieldName);
			
			foreach ($terms as $term => $relevance)
			{
				$q->execute(array(':field' => $fieldName,
				                  ':term' => $term,
				                  ':relevance' => (string)$relevance));
				$SID = DB::lastInsertID('SearchIndex');
				
				$connArgs[':SID'] = (string)$SID;
				$q2->execute($connArgs);
			}
		}
	}
	
	private function deleteIndex()
	{
		$wheres = array();
		$args = array();
		foreach ($this->_keys as $key)
		{
			$args[':__'.$key] = $this->_instance->$key;
			$wheres[] = $key . '= :__'.$key;
		}
		$where = implode(' AND ', $wheres);
		
		$q = DB::prepare('DELETE FROM SearchIndex WHERE
		                    SID IN (SELECT SID FROM ' . $this->_tableIndex . '
		                            WHERE '.$where.')');
		$q->execute($args);
	}
	
	private function needsIdentTerms()
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
