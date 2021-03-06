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
 * @property primary autoincrement; ID Integer('ID');
 * @property authorID String('AuthorID', 24);
 * @property anonAuthorID Integer('Anon Author ID');
 * @property timePosted DateTime('Date posted');
 * @property title String(t('Title'), 128);
 * @property comment String(t('Comment')); required
 * @property spamStatus SpamStatus('Spam Statu'); required
 * @property spamSessionID String(t('Spam Session ID'));
*/
abstract class Comment extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function beforeInsert()
	{
		$this->timePosted = time();
	}
	
	public static function moderationQueueLength()
	{
		$q = DB::prepare('SELECT COUNT(*) AS cnt FROM Comment WHERE
		                        spamStatus=:moderation');
		$q->execute(array(':moderation' => PropertySpamStatus::UNKNOWN));
		
		$result = $q->fetch();
		return (int)$result['cnt'];
	}
	
	public static function getModerationQueue($type)
	{
		return new CommentPager('SELECT * FROM '.$type.'
		         NATURAL JOIN Comment
		         WHERE spamStatus=:moderation',
			array(':moderation' => PropertySpamStatus::UNKNOWN), $type);
	}
}

?>
