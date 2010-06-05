{block name="title"}{'Blog'|_}{/block}

{block name="content"}
	<h1>{'Blog'|_}</h1>
	
	{foreach $blogs as $blog}
		<article>
			<header>
				<h1><a href="{url request="blog/show" year=$blog->datePosted|date_format:'Y'
                                      month=$blog->datePosted|date_format:'m'
                                      day=$blog->datePosted|date_format:'d'
                                      id=$blog->ID}">{$blog->title}</a></h1>
				<p>By {$blog->authorID} @ {$blog->datePosted|date_format}</p>
			</header>
			{$blog->text|format:text|truncate:200}
		</article>
	{/foreach}
{/block}
