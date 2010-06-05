<ol>
	{foreach $widgetMenu->entries(CoOrg::getLanguage()) as $entry}
		<li {if $entry->url == $coorgUrl}class="current"{/if}>
			<a href="{$entry->url}">{$entry->title|escape}</a>
		</li>
	{/foreach}
</ol>
