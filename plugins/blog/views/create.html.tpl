{block name='title'}Create blog{/block}

{block name='content'}
	<h1>Create a blog item</h1>
	
	{form request='blog/save' instance=$blog}
		{input for=title label="Title"}
		{input for=text type=textarea label="Blog contents"}
		
		{input type="submit" label="Blog posten"}
	{/form}
{/block}
