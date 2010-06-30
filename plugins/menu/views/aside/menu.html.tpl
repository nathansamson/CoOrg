<ol>
	{foreach $widgetMenu->entries->filter(CoOrg::getLanguage()) as $entry}
		<li {if $entry->url == $coorgUrl}class="current"{/if}>
			<a href="{htmlspecialchars($entry->url)}">{$entry->title|escape}</a>
		</li>
	{/foreach}
</ol>
