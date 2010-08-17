<?php

class FlattrBlogShowRequest
{
	public function run($controller)
	{
		$class = new stdClass;
		$class->language = $controller->blog->language;
		$class->tags = array();
		$class->category = 'text';
		$class->title = $controller->blog->title;
		$class->description = $controller->blog->text;
		return $class;
	}
}

Flattr::request('blog/show', new FlattrBlogShowRequest);

?>
