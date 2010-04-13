<?php


/**
 * @primaryproperty ID String('Title', 256); required
 * @primaryproperty datePosted Date('Date posted'); required
 * @property title String('Title', 256); required
 * @property authorID String('Author', 64); required 
 * @property text String('Content'); required
 * @property timePosted DateTime('Posted'); required
 * @property timeEdited DateTime('Edited');
*/
class Blog extends DBModel
{
	public function __construct($title, $author, $text, $datePosted = null)
	{
		parent::__construct();
		$this->title = $title;
		$this->authorID = $author;
		$this->text = $text;
		$this->datePosted = $datePosted;
	}
	
	public static function getBlog($year, $month, $day, $ID)
	{
		$q = DB::prepare('SELECT * FROM Blog
		                     WHERE datePosted = :postDate
		                       AND
		                           ID=:ID');
		$isodate = sprintf("%04d-%02d-%02d", $year, $month, $day);
		$q->execute(array('postDate' => $isodate,
		                  'ID' => $ID));

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
	
	public static function latest($n)
	{
		$q = DB::prepare('SELECT * FROM Blog
		                  ORDER BY datePosted DESC
		                  LIMIT '.(int)$n);
		$q->execute();
		
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
		$this->property('datePosted')->set(time());
		$this->property('timePosted')->set(time());
	}
	
	protected function beforeUpdate()
	{
		$this->property('timeEdited')->set(time());
	}
	
	private static function produceBlog($row)
	{
		$blog = new Blog($row['title'], $row['authorID'], $row['text'], $row['datePosted']);
		$blog->property('ID')->set($row['ID']);
		$blog->property('timePosted')->set($row['timePosted']);
		$blog->property('timeEdited')->set($row['timeEdited']);
		$blog->setSaved();
		return $blog;
	}
}

?>
