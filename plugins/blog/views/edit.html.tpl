{block name='title'}{'Edit blog'|_}{/block}

{block name='content'}
	{form request='blog/update' instance=$blog}
		{input value=$blog->datePosted|date_format:'Y' name=year}
		{input value=$blog->datePosted|date_format:'m' name=month}
		{input value=$blog->datePosted|date_format:'d' name=day}
		{input for=ID name=id}
		
		{input for=title label="Title" required}
		{input for=text label="Blog content" type=textarea required size=big editor=full}
		
		{input type=submit label="Save blog"}
	{/form}
{/block}
