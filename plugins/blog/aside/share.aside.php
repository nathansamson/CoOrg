<?php

class BlogShareAside extends AsideController
{
	public function run($widgetParams, $orient,
	                    $request, $year = null, $month = null, 
	                              $day = null, $id = null)
	{
		if ($request == 'blog/show')
		{
			//return '<br />'.$id . '<- Share it!';
		}
	}
	
	public function preview($widgetParams, $orient)
	{
		return null;
	}
}

?>
