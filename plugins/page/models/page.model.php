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
 * @property primary; ID String('ID', 256); required
 * @property primary; language String(t('coorg|Language'), 6); required
 * @property created Date('Created'); required
 * @property updated Date('Updated'); required only('update')
 * @property authorID String(t('page|Author'), 24); required
 * @property lastEditorID String('Last Editor', 24); required only('update')
 * @property title String(t('page|Title'), 256); required
 * @property content String(t('page|Content')); required
 * @property writeonly; originalLanguage String('', 6);
 * @property writeonly; originalID String('', 256);
 * @extends Normalize title ID language
 * @extends Searchable PageSearchIndex @ID @language title content:html :language:language
*/
class Page extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public function beforeInsert()
	{
		$this->created = date('Y-m-d');
	}
	
	public function afterInsert()
	{
		if ($this->originalLanguage)
		{
			$original = Page::get($this->originalID, $this->originalLanguage);
			
			$insert = DB::prepare('INSERT INTO PageLanguage
			                       (page1Language, page1ID, page2Language, page2ID)
			                       VALUES(:l1, :p1, :l2, :p2)');
			foreach ($original->languages() as $language)
			{
				$insert->execute(array(
				  ':l1' => $language->language, 
				  ':p1' => $language->pageID,
				  ':l2' => $this->language,
				  ':p2' => $this->ID));
			}
			$insert->execute(array(
				':l1' => $original->language, 
				':p1' => $original->ID,
				':l2' => $this->language,
				':p2' => $this->ID));
		}
	}
	
	public function beforeUpdate()
	{
		$this->updated = date('Y-m-d');
	}
	
	public function languages()
	{
		$q = DB::prepare('SELECT * FROM PageLanguagesBidiV
		                    LEFT JOIN Language ON page2Language = language
		                   WHERE page1Language =:l AND page1ID = :ID
		                  ORDER BY language ASC');
		$q->execute(array(':l' => $this->language, ':ID' => $this->ID));
		
		$languages = array();
		foreach ($q->fetchAll() as $row)
		{
			$l = new stdClass;
			$l->language = $row['language'];
			$l->name = $row['name'];
			$l->pageID = $row['page2ID'];
			$languages[] = $l;
		}
		return $languages;
	}
	
	public function untranslated()
	{
		$q = DB::prepare('SELECT * FROM Language
		                    WHERE language NOT IN
		                     (SELECT page2Language FROM
		                      PageLanguagesBidiV WHERE
		                        page1Language = :l AND
		                        page1ID = :ID)
		                      AND language != :l
		                   ORDER BY name');
		$q->execute(array(':l' => $this->language, ':ID' => $this->ID));
		
		$l = array();
		foreach ($q->fetchAll() as $row)
		{
			$l[] = self::fetch($row, 'Language');
		}
		return $l;
	}

	public static function get($ID, $language)
	{
		$q = DB::prepare('SELECT * FROM Page WHERE ID=:ID AND language=:l');
		$q->execute(array('ID' => $ID, ':l' => $language));
		
		if ($row = $q->fetch())
		{
			return self::fetch($row, 'Page');
		}
		else
		{
			return null;
		}
	}
	
	public static function pages($language)
	{
		$pager = new PagePager('SELECT * FROM Page WHERE language=:l
		                         ORDER BY title',
		                       array(':l' => $language));
		return $pager;
	}
	
	protected function hasTranslation($language)
	{
		$q = DB::prepare('SELECT * FROM PageLanguagesBidiV
		                     WHERE page1Language =:l1 AND page1ID = :ID1
		                      AND page2Language =:l2');
		$q->execute(array(':l1' => $this->language,
		                  ':ID1' => $this->ID,
		                  ':l2' => $language));
		return $q->fetch() ? true : false;
	}
	
	protected function validate($for)
	{
		parent::validate($for);
		if ($for == 'insert')
		{
			if ($this->originalLanguage)
			{
				$original = Page::get($this->originalID, $this->originalLanguage);
				if ($original->hasTranslation($this->language))
				{
					$this->title_error = t('This page is already translated');
					throw new ValidationException($this);
				}
			}
		}
	}
}
