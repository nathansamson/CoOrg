<section>
	<ol class="menu">
	{foreach $menu as $module}
		<li>
			<a href="{$module->url(UserSession::get()->user())}">
				{$module->name}
			</a>
		</li>
	{/foreach}
	</ol>
</section>
