{block name='title'}{'Create blog'|_}{/block}

{block name='content'}
	<h1>{'Post a blog'|_}</h1>
	
	{stylesheet file={'styles/tags.css'|static:'search'}}
	
	{form request='blog/save' instance=$blog}
		{input for=title label="blog|Title" required class=title}
		{input for=text type=textarea label="Blog content" required size=big editor=full}
		
		{input for=tags type="list" initial=5 listClass=taglist 
		       label="search|Tag" autocomplete="search.xml/tagsuggest"}
		
		{input for=language}
		{input type="submit" label="coorg|Save"}
	{/form}
{/block}
