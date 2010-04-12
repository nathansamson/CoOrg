{block name="title"}Blog{/block}

{block name="content"}
	<h1>Latest blogs</h1>
	
	{foreach $blogs as $blog}
		<article>
			<h1>{$blog->title}</h1>
		</article>
	{/foreach}
{/block}
