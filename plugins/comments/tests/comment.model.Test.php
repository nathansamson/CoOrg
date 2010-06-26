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
 * @property primary; ID String('title', 32);
 * @property text String('Text', 128);
*/
class MeCommentMock extends DBModel
{
	public function __construct()
	{
		parent::__construct();
	}

	public static function get($ID)
	{
		$q = DB::prepare('SELECT * FROM MeCommentMock WHERE ID=:ID');
		$q->execute(array(':ID' => $ID));
		
		return self::fetch($q->fetch(), 'MeCommentMock');
	}
}

/**
 * @property mockID String('title', 32); required
*/
class MeCommentMockComment extends Comment
{
	public function __construct()
	{
		parent::__construct();
	}


	public function get($ID)
	{
		$q = DB::prepare('SELECT * FROM MeCommentMockComment NATURAL JOIN Comment WHERE Comment.ID=:ID');
		$q->execute(array(':ID' => $ID));
		
		return self::fetch($q->fetch(), 'MeCommentMockComment');
	}
}

class CommentModelTest extends CoOrgModelTest
{
	const dataset = 'comment.dataset.xml';
	
	public function testCreate()
	{
		$comment = new MeCommentMockComment;
		$comment->mock = MeCommentMock::get('me-mock');
		$comment->title = 'Some Title';
		$comment->comment = 'Some Comment';
		$comment->save();
		
		$this->assertEquals('me-mock', $comment->mockID);
		$this->assertEquals('me-mock', $comment->mock->ID);
		$rComment = MeCommentMockComment::get($comment->ID);
		$this->assertEquals('Some Title', $rComment->title);
		$this->assertEquals('Some Comment', $rComment->comment);
		$this->assertEquals('me-mock', $rComment->mockID);
		$this->assertEquals('me-mock', $rComment->mock->ID);
	}
	
	public function testAppendComment()
	{
		$mock = MeCommentMock::get('me-mock');
		$comment = new MeCommentMockComment;
		$comment->title = 'Some Title';
		$comment->comment = 'Some Comment';
		
		$mock->comments[] = $comment;
	}
	
	public function testComments()
	{
		$mock = MeCommentMock::get('other-mock');
		$comments = $mock->comments;
		$this->assertEquals(4, count($comments));
		$this->assertEquals('Second Comment', $comments[0]->title);
	}
}

?>
