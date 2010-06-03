{block name="title"}Edit {$page->title}{/block}

{block name="content"}
	<h1>Edit {$page->title}</h1>
	{if $preview}
		<h2>Preview</h2>
		{$page->content}
	{/if}
	
	{form request="admin/page/update" instance=$page id="editPage"}
		<input type="hidden" name="ID" value="{$page->ID}" />
		<input type="hidden" name="language" value="{CoOrg::getLanguage()}" />
		{if $redirect}
			<input type="hidden" name="redirect" value="{$redirect}" />
		{/if}
		
		{input for=title label="Title" required}
		{input for=content label="Content" type="textarea" size=big required}
		
		{input type=submit label="Save page"}
		{input type=submit label="Preview page" name="preview"}
	{/form}
{/block}
