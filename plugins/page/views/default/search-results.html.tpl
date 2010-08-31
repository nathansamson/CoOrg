{foreach $searchResults as $page}
	<article>
		<header>
			<h1><a href="{url request="page/show" page=$page->ID}">{$page->title}</a></h1>
		</header>
		{$page->content|format:text|truncate:200}
	</article>
{/foreach}
