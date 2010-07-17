<?php

class BlogOwnership
{
	public function owns($user, $blog)
	{
		return ($blog->authorID == $user || Acl::isAllowed($user, 'blog-admin'));
	}
}

Acl::registerOwnsClass('Blog', new BlogOwnerShip);


class BlogCommentOwnership
{
	public function owns($user, $comment)
	{
		return ($comment->authorID == $user);
	}
}

Acl::registerOwnsClass('BlogComment', new BlogCommentOwnership);

?>
