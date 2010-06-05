{block name='title'}{'Create blog'|_}{/block}

{block name='content'}
	<h1>{'Post a blog'|_}</h1>
	
	{form request='blog/save' instance=$blog}
		{input for=title label="Title" required class=title}
		{input for=text type=textarea label="Blog content" required size=big editor=full}
		
		{input type="submit" label="Publish post"}
	{/form}
{/block}
