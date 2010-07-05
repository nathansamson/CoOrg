{block name="title"}{'Edit %p'|_:$page->title|escape}{/block}

{block name="admin-content"}
	{if $page->untranslated()}
	<div class="page-actions">
		{form request="admin/page/create" method="get"}
			{input name="originalID" value="{$page->ID}"}
			{input name="originalLanguage" value="{$page->language}"}
			<select name="trLanguage" id="translate_langCode">
				<option val="__choose__">{'Translate this page'|_}</option>
				{foreach $page->untranslated() as $lang}
					<option value="{$lang->language}">
						{$lang->name|escape}
					</option>
				{/foreach}
			</select>
			<input type="submit" value="{'Start translation'|_}"/>
		{/form}
	</div>
	{/if}
	<h1>{'Edit %p'|_:$page->title|escape}</h1>
	{if $preview}
		<h2>{'Preview'|_}</h2>
		{$page->content|format:all}
	{/if}
	
	{form request="admin/page/update" instance=$page id="editPage"}
		{input for=ID}
		<input type="hidden" name="language" value="{CoOrg::getLanguage()}" />
		{if $redirect}
			{input value=$redirect name=redirect}
		{/if}
		
		{input for=title label="page|Title" required class=title}
		{input for=content label="Content" type="textarea" size=big required editor=full}
		
		{input type=submit label="coorg|Save"}
		{input type=submit label="Preview page" name="preview"}
	{/form}
{/block}
