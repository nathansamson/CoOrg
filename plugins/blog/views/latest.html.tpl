{block name="title"}Blog{/block}

{block name="content"}
	<h1>Latest blogs</h1>
	
	{foreach $blogs as $blog}
		<article>
			<header>
				<h1><a href="{url request="blog/show" year=$blog->datePosted|date_format:'Y'
                                      month=$blog->datePosted|date_format:'m'
                                      day=$blog->datePosted|date_format:'d'
                                      id=$blog->ID}">{$blog->title}</a></h1>
				<p>By {$blog->authorID} @ {$blog->datePosted|date_format}</p>
			</header>
			{$blog->text|escape|nl2br}
		</article>
	{/foreach}
{/block}
