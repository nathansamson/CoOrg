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
 * @property primary; ID String(t('Title'), 256); required
 * @property primary; datePosted Date(t('Date posted')); required
 * @property primary; language String(t('Language'), 6); required
 * @property title String(t('Title'), 256); required
 * @property authorID String(t('Author'), 64); required 
 * @property text String(t('Content')); required
 * @property timePosted DateTime('Posted'); required
 * @property timeEdited DateTime('Edited');
 * @property parentID String('Title', 256);
 * @property parentLanguage String('Parent Language', 6);
 * @extends Normalize title ID datePosted language
*/
class Blog extends DBModel
{
	public function __construct($title = null, $author = null, $text = null,
	                            $language = null, $datePosted = null)
	{
		parent::__construct();
		$this->title = $title;
		$this->authorID = $author;
		$this->text = $text;
		$this->language = $language;
		$this->datePosted = $datePosted;
	}

	public function translate($translator, $title, $text, $language)
	{
		$translation = new Blog($title, $translator, $text, $language, $this->datePosted);
		if (!$this->parentID)
		{
			$translation->parentID  = $this->ID;
			$translation->parentLanguage = $this->language;
		}
		else
		{
			$translation->parentID = $this->parentID;
			$translation->parentLanguage = $this->parentLanguage;
		}
		$translation->save();
		return $translation;
	}

	public function translatedIn($l)
	{
		return self::translatedInWithParams($this->ID, $this->datePosted_db, $l,
		                              $this->parentID ? $this->parentID : null);
	}

	public function translations()
	{
		if (!$this->parentID)
		{
			$q = DB::prepare('SELECT * FROM Blog
		                     WHERE datePosted = :postDate
		                       AND
		                           parentID=:ID
		                       AND
		                           parentLanguage=:language');
			$q->execute(array('postDate' => $this->datePosted_db,
		                  'ID' => $this->ID,
		                  ':language' => $this->language));
		}
		else
		{
			$q = DB::prepare('SELECT * FROM Blog
		                     WHERE datePosted = :postDate
		                       AND
		                          ((parentID=:parentID AND parentLanguage=:parentLanguage)
   		                             OR 
   		                           (ID=:parentID AND language=:parentLanguage))
   		                       AND NOT
   		                         ID=:ID    
		                       ');
			$q->execute(array('postDate' => $this->datePosted_db,
		                  'ID' => $this->ID,
		                  ':parentID' => $this->parentID,
		                  ':parentLanguage' => $this->parentLanguage));		
		}

		$trs = array();
		foreach ($q->fetchAll() as $row)
		{
			$trs[$row['language']] = self::fetch($row, 'Blog');
		}
		return $trs;
	}
	
	public function untranslated()
	{
		$q = DB::prepare('SELECT * FROM Language
		      WHERE language NOT IN 
		                (SELECT language FROM Blog
		                  WHERE (ID=:pID AND language=:pLanguage)
		                    OR
		                        (parentID=:pID AND parentLanguage=:pLanguage))
		      ORDER BY name');
		if (!$this->parentID)
		{
			$q->execute(array(':pID' => $this->ID,
			                  ':pLanguage' => $this->language));
		}
		else
		{
			$q->execute(array(':pID' => $this->parentID,
			                  ':pLanguage' => $this->parentLanguage));
		}
		$u = array();
		foreach ($q->fetchAll() as $row)
		{
			$u[] = self::fetch($row, 'Language');
		}
		return $u;
	}
	
	public static function getBlog($year, $month, $day, $ID, $language)
	{
		$q = DB::prepare('SELECT * FROM Blog
		                     WHERE datePosted = :postDate
		                       AND
		                           ID=:ID
		                       AND language=:language');
		$isodate = sprintf("%04d-%02d-%02d", $year, $month, $day);
		$q->execute(array('postDate' => $isodate,
		                  'ID' => $ID,
		                  'language' => $language));

		$row = $q->fetch();
		if ($row != false)
		{
			return self::fetch($row, 'Blog');
		}
		else
		{
			return null;
		}
	}
	
	public static function blogs($language)
	{
		$pager = new BlogPager(
		             'SELECT * FROM Blog
		                  WHERE language=:language
		                  ORDER BY timePosted DESC',
		             array('language' => $language));

		return $pager;
	}
	
	public static function getArchive($language, $year, $month = null)
	{
		while (strlen($year) != 4) $year ='0'.$year;
		if ($month) while (strlen($month) != 2) $month ='0'.$month;
		
		$q = DB::prepare('SELECT * FROM Blog WHERE
		                    language = :l AND
		                    YEAR(datePosted) = :year'.
		                    ($month ? ' AND MONTH(datePosted) = :month' : '').
		                    ' ORDER BY timePosted DESC');
		
		
		$params = array(':l' => $language, ':year' => $year);
		if ($month) $params[':month'] = $month;
		
		$q->execute($params);
		$a = array();
		foreach ($q->fetchAll() as $row)
		{
			$a[] = Blog::fetch($row, 'Blog');
		}
		return $a;
	}
	
	public static function getArchives($language)
	{
		$q = DB::prepare('SELECT YEAR(datePosted) AS year,
		                         MONTH(datePosted) AS month,
		                         COUNT(*) as count  FROM Blog
		         WHERE language=:lang
		         GROUP BY year, month
		         ORDER BY year DESC, month DESC');
		$q->execute(array(':lang' => $language));
		
		$archive = array();
		foreach ($q->fetchAll() as $row)
		{
			$monthInfo = new stdClass;
			$monthInfo->year = $row['year'];
			$monthInfo->month = $row['month'];
			$monthInfo->posts = $row['count'];
			$archive[] = $monthInfo;
		}
		return $archive;
	}
	
	protected function beforeInsert()
	{
		if ($this->datePosted_db == null)
			$this->datePosted = time();
		$this->timePosted = time();
	}
	
	protected function beforeUpdate()
	{
		$this->timeEdited = time();
	}

	protected function validate($for)
	{
		parent::validate($for);

		if ($for == 'insert' && $this->parentID != '')
		{
			if (self::translatedInWithParams($this->parentID, $this->datePosted_db, $this->language))
			{
				$this->text_error = t('This blog is already translated in this language');
				throw new ValidationException($this);
			}
		}
	}

	private static function translatedInWithParams($ID, $date, $language, $parentID = null)
	{
		if (!$parentID)
		{
			$q = DB::prepare('SELECT * FROM Blog
				                 WHERE datePosted = :postDate
				                   AND
				                       parentID=:ID
				                   AND language=:language');
		}
		else
		{
			$q = DB::prepare('SELECT * FROM Blog
			                    WHERE datePosted = :postDate
			                    AND (parentID=:ID OR ID=:ID)
			                    AND language =:language');
		}
		$q->execute(array(':postDate' => $date,
		                  ':ID' => $parentID ? $parentID : $ID,
		                  ':language' => $language));

		$row = $q->fetch();

		return ($row != false);
	}
}

?>
