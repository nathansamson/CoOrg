{block name="title"}{'Blog'|_}{/block}

{block name="content"}
	{if Acl::isAllowed(UserSession::get()->username, 'blog-writer')}
	<div class="page-actions">
		{a request="blog/create"}{'New blog'|_}{/a} |
		{a request="admin/blog"}{'Admin'|_}{/a}
	</div>
	{/if}
	<h1>{'Blog'|_}</h1>
	{twowaypager pager=$blogpager request="blog/index" page=".*."
	             coorgPrev='Newer posts'|_
	             coorgNext='Older posts'|_}
	{foreach $blogs as $blog}
		<article>
			<header>
				<h1><a href="{url request="blog/show" year=$blog->year
                                      month=$blog->month
                                      day=$blog->day
                                      id=$blog->ID}">{$blog->title|escape}</a></h1>
				<p>By {$blog->authorID} @ {$blog->datePosted|date_format}</p>
			</header>
			{$blog->text|format:text|truncate:200}
		</article>
	{/foreach}
	{twowaypager pager=$blogpager request="blog/index" page=".*."
	             coorgPrev='Newer posts'|_
	             coorgNext='Older posts'|_}
{/block}
