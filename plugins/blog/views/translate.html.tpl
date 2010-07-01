{block name='title'}{'Translate blog'|_}{/block}

{block name='content'}
	<h1>{'Translate "%blog"'|_:$originalBlog->title|escape}</h1>
	
	<h2>{'Original'|_}</h2>
	{$originalBlog->text|format:all}
	
	{form request='blog/translateSave' instance=$translatedBlog}
		{input value=$originalBlog->year name=year}
		{input value=$originalBlog->month name=month}
		{input value=$originalBlog->day name=day}
	
		{input value=$originalBlog->ID name=id}
		{input value=$originalBlog->language name=fromLanguage}
	
		{input for=title label="Title" required class=title}
		{input for=text type=textarea label="Blog content" required size=big editor=full}
		{input for=language}
		
		{input type="submit" label="Publish post"}
	{/form}
{/block}
