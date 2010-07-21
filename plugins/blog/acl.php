<?php

class BlogOwnership
{
	public function owns($user, $blog)
	{
		return ($blog->authorID == $user || Acl::isAllowed($user, 'blog-admin'));
	}
}

Acl::registerOwnsClass('Blog', new BlogOwnerShip);

?>
