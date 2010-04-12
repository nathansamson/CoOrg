<?php

class BlogShareAside extends AsideController
{
	public function run($request, $year = null, $month = null, 
	                              $day = null, $id = null)
	{
		if ($request == 'blog/show')
		{
			return '<br />'.$id . '<- Share it!';
		}
	}
}

?>
