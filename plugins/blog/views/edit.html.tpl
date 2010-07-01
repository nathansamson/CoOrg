{block name='title'}{'Edit %b'|_:$blog->title}{/block}

{block name='content'}
	{if $blog->untranslated()}
	<div class="page-actions">
		{form request="blog/translate" method="get"}
			{input value=$blog->year name=year}
			{input value=$blog->month name=month}
			{input value=$blog->day name=day}
			
			{input value=$blog->ID name=id}
			{input value=$blog->language name=fromLanguage}
			
			<select name="toLanguage" id="translate_langCode">
				<option val="__choose__">{'Translate blog'|_}</option>
			{foreach $blog->untranslated() as $lang}
				<option value="{$lang->language}">
					{$lang->name|escape}
				</option>
			{/foreach}
			</select>
			
			{input type="submit" label="Translate blog"}
		{/form}
	</div>
	{/if}
	<h1>{'Edit %b'|_:$blog->title|escape}</h1>

	{form request='blog/update' instance=$blog}
		{input value=$blog->year name=year}
		{input value=$blog->month name=month}
		{input value=$blog->day name=day}
		{input for=ID name=id}
		
		{input for=language}
		{input for=title label="Title" required class=title}
		{input for=text label="Blog content" type=textarea required size=big editor=full}
		
		<h2>{'Comments'|_}</h2>
		{input for=commentsAllowed type=checkbox label="Allow comments"}
		{input name=commentsOpenFor value="$currentOpenFor" options=$openFor label="Allow comments for" type=select}
		
		{input type=submit label="Save blog"}
	{/form}
{/block}
