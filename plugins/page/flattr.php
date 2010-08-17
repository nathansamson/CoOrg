<?php

class FlattrPageShowRequest
{
	public function run($controller)
	{
		$class = new stdClass;
		$class->language = $controller->page->language;
		$class->tags = array();
		$class->category = 'text';
		$class->title = $controller->page->title;
		$class->description = $controller->page->content;
		return $class;
	}
}

Flattr::request('page/show', new FlattrPageShowRequest);

?>
