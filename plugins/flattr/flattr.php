<?php

class FlattrHomeRequest
{
	public function run($controller)
	{
		$class = new stdClass;
		$class->language = CoOrg::getLanguage();
		$class->tags = array();
		$class->category = 'text';
		$class->title = CoOrg::config()->get('site/title');
		$class->description = CoOrg::config()->get('site/subtitle');
		return $class;
	}
}

Flattr::request('home/index', new FlattrHomeRequest);

?>
