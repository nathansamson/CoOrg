{block name='title'}Create blog{/block}

{block name='content'}
	<h1>Create a blog item</h1>
	
	{form request='blog/save' instance=$blog}
		{input for=title label="Title" required}
		{input for=text type=textarea label="Blog contents" required}
		
		{input type="submit" label="Blog posten"}
	{/form}
{/block}
