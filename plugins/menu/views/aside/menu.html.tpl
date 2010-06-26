<ol class="menu">
	{foreach $widgetMenu->entries(CoOrg::getLanguage()) as $entry}
		<li {if $entry->url == $coorgUrl}class="current"{/if}>
			<a href="{htmlspecialchars($entry->url)}">{$entry->title|escape}</a>
		</li>
	{/foreach}
</ol>

{stylesheet file={'styles/menu.css'|static:menu}}
