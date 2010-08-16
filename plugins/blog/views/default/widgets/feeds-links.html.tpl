{foreach $feeds as $feed}
	<link rel="alternate" type="{$feed->type}" title="{$feed->title|escape}"
	      href="{$feed->url}" />
{/foreach}
