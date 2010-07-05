{block name="content"}
	{block name="admin-tabs"}
		{stylesheet file={'styles/admin.css'|static:'admin'}}
		<nav class="admin-tabs">
			<ol>
				<li>
					{a request="admin"}{'Admin'|_}{/a}
				</li>
				{foreach $_adminTabs as $tab}	
					<li{if $tab->current} class="current"{/if}>
						<a href="{$tab->url|escape}">{$tab->name}</a>
					</li>
				{/foreach}
			</ol>
		</nav>
	{/block}
	
	{block name="admin-content"}
	{/block}
{/block}
