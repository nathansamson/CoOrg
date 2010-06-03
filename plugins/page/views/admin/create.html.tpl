{block name="title"}Create new page{/block}

{block name="content"}
	<h1>New page</h1>
	
	{if $newPage->content}
		<h2>Preview</h2>
		{$newPage->content}
	{/if}
	
	{form request="admin/page/save" instance=$newPage id="newPage"}
		<input type="hidden" name="language" value="{CoOrg::getLanguage()}" />
		
		{input for=title label="Title" required}
		{input for=content label="Content" type="textarea" size=big required}
		
		{input type=submit label="Save page"}
		{input type=submit label="Preview page" name="preview"}
	{/form}
{/block}
