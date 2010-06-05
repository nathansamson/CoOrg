{block name="title"}{'Edit %p'|_:$page->title|escape}{/block}

{block name="content"}
	<h1>{'Edit %p'|_:$page->title|escape}</h1>
	{if $preview}
		<h2>{'Preview'|_}</h2>
		{$page->content|format:all}
	{/if}
	
	{form request="admin/page/update" instance=$page id="editPage"}
		<input type="hidden" name="ID" value="{$page->ID}" />
		<input type="hidden" name="language" value="{CoOrg::getLanguage()}" />
		{if $redirect}
			<input type="hidden" name="redirect" value="{$redirect}" />
		{/if}
		
		{input for=title label="Title" required}
		{input for=content label="Content" type="textarea" size=big required editor=full}
		
		{input type=submit label="Save page"}
		{input type=submit label="Preview page" name="preview"}
	{/form}
{/block}
