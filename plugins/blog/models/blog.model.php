<?php


/**
 * @primaryproperty ID String('Title', 256); required
 * @primaryproperty datePosted Date('Date posted'); required
 * @primaryproperty language String('Language', 6); required
 * @property title String('Title', 256); required
 * @property authorID String('Author', 64); required 
 * @property text String('Content'); required
 * @property timePosted DateTime('Posted'); required
 * @property timeEdited DateTime('Edited');
 * @property parentID String('Title', 256);
 * @property parentLanguage String('Parent Language', 6);
*/
class Blog extends DBModel
{
	public function __construct($title, $author, $text, $language, $datePosted = null)
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
		$translation->parentID  = $this->ID;
		$translation->parentLanguage = $this->language;
		$translation->save();
		return $translation;
	}

	public function translatedIn($l)
	{
		return self::translatedInWithParams($this->ID, $this->property('datePosted')->db(), $l);
	}

	public function translations()
	{
		$q = DB::prepare('SELECT * FROM Blog
		                     WHERE datePosted = :postDate
		                       AND
		                           parentID=:ID');
		$q->execute(array('postDate' => $this->property('datePosted')->db(),
		                  'ID' => $this->ID));

		$trs = array();
		foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$trs[$row['language']] = self::produceBlog($row);
		}
		return $trs;
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

		$row = $q->fetch(PDO::FETCH_ASSOC);
		if ($row != false)
		{
			return self::produceBlog($row);
		}
		else
		{
			return null;
		}
	}
	
	public static function latest($n, $language)
	{
		$q = DB::prepare('SELECT * FROM Blog
		                  WHERE language=:language
		                  ORDER BY timePosted DESC
		                  LIMIT '.(int)$n);
		$q->execute(array('language' => $language));
		
		$blogs = array();
		foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row)
		{
			$blogs[] = self::produceBlog($row);
		}
		return $blogs;
	}
	
	protected function normalizeTitle($title)
	{
		return str_replace(' ', '-', strtolower($title));
	}
	
	protected function beforeInsert()
	{
		$this->property('ID')->set($this->normalizeTitle($this->title));
		if ($this->property('datePosted')->db() == null)
			$this->property('datePosted')->set(time());
		$this->property('timePosted')->set(time());
	}
	
	protected function beforeUpdate()
	{
		$this->property('timeEdited')->set(time());
	}

	protected function validate($for)
	{
		parent::validate($for);

		if ($for == 'insert' && $this->parentID != '')
		{
			if (self::translatedInWithParams($this->parentID, $this->property('datePosted')->db(), $this->language))
			{
				$this->text_error = 'This blog is already translated in this language';
				throw new ValidationException($this);
			}
		}
	}
	
	private static function produceBlog($row)
	{
		$blog = new Blog($row['title'], $row['authorID'], $row['text'], $row['language'], $row['datePosted']);
		$blog->property('ID')->set($row['ID']);
		$blog->property('timePosted')->set($row['timePosted']);
		$blog->property('timeEdited')->set($row['timeEdited']);
		$blog->property('parentID')->set($row['parentID']);
		$blog->property('parentLanguage')->set($row['parentLanguage']);
		$blog->setSaved();
		return $blog;
	}

	private static function translatedInWithParams($ID, $date, $language)
	{
		$q = DB::prepare('SELECT * FROM Blog
		                     WHERE datePosted = :postDate
		                       AND
		                           parentID=:ID
		                       AND language=:language');
		$q->execute(array(':postDate' => $date,
		                  ':ID' => $ID,
		                  ':language' => $language));

		$row = $q->fetch(PDO::FETCH_ASSOC);

		return ($row != false);
	}
}

?>
