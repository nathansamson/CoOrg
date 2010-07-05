{block name="title"}{'Languages'|_}{/block}

{block name="admin-content"}
	<h1>{'Languages'|_}</h1>
	<ul class="languages">
	{foreach $languages as $language}
		<li>
			{$language->name} ({$language->language})
			<div class="actions">
				{button request="admin/i18n/delete"
				        param_language=$language->language
				    coorgStock="delete"
					coorgConfirm='Are you sure you want to delete "%l"?'|_:$language->name}{/button}
			</div>
		</li>
	{/foreach}
	</ul>
	
	<h2>{'Install new language'|_}</h2>
	{form request="admin/i18n/save" instance=$newLanguage}
		{input for=language label="Language code" size=6}
		{input for=name label="Language name"}
		{input type=submit label="Install language"}
	{/form}
{/block}
