<?php

class CommentOwnership
{
	public function owns($user, $comment)
	{
		return ($comment->authorID == $user);
	}
}

Acl::registerOwnsClass('Comment', new CommentOwnership);

?>
