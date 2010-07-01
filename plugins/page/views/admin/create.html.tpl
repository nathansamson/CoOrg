{block name="title"}{'Create new page'|_}{/block}

{block name="content"}
	<h1>{'New page'|_}</h1>
	
	{if $originalPage}
		<h2>{'Original page'|_}</h2>
		{$originalPage->content|format:all}
	{/if}
	
	{if $newPage->content}
		<h2>{'Preview'|_}</h2>
		{$newPage->content|format:all}
	{/if}
	
	{form request="admin/page/save" instance=$newPage id="newPage"}
		{input type="hidden" for="language"}
		{input type="hidden" for="originalLanguage"}
		{input type="hidden" for="originalID"}
		
		{input for=title label="page|Title" required class=title}
		{input for=content label="Content" type="textarea" size=big required editor=full}
		
		{input type=submit label="coorg|Save"}
		{input type=submit label="Preview page" name="preview"}
	{/form}
{/block}
