<?php

class BlogFeedsWidget extends SiteWidgetController
{
	public function run($widgetParams,
	                    $request, $year = null, $month = null, 
	                              $day = null, $id = null)
	{
		$feeds = false;
		$controller = substr($request, 0, strpos($request, '/'));
		$admin = false;
		if ($controller == 'admin')
		{
			$admin = true;
			$subrequest = substr($request, strpos($request, '/') + 1);
			$controller = substr($subrequest, 0, strpos($subrequest, '/'));
		}
		
		$globalFeed = new stdClass;
		$globalFeed->title = t('ATOM Blog feed');
		$globalFeed->type = 'application/atom+xml';
		$globalFeed->url = CoOrg::createURL('blog.atom/latest');
		
		if ($request == 'blog/show')
		{
			$feeds = array();
			
			$feeds[] = $globalFeed;
			
			$commentsFeed = new stdClass;
			$commentsFeed->title = t('Comments');
			$commentsFeed->type = 'application/atom+xml';
			$commentsFeed->url = CoOrg::createURL(array('blog.atom/show', $year, $month, $day, $id));
			
			$feeds[] = $commentsFeed;
		}
		else if ($admin && $controller = 'blog')
		{
			$feeds = array();
			
			$feeds[] = $globalFeed;
			$commentsFeed = new stdClass;
			$commentsFeed->title = t('Unmoderated comments');
			$commentsFeed->type = 'application/atom+xml';
			$commentsFeed->url = CoOrg::createURL('blog.atom/comment/unmoderated');
			$feeds[] = $commentsFeed;
		}
		else if ($controller == 'blog' || $request == 'home/index')
		{
			$feeds = array();
			
			$feeds[] = $globalFeed;
		}
		
		if ($feeds)
		{
			$this->feeds = $feeds;
			return $this->render('widgets/feeds-links');
		}
	}
	
	public function preview($widgetParams)
	{
		return $this->renderPreview('widgets/feeds-links-preview');
	}
}

?>
