{block name='title'}Admin{/block}

{block name='head' append}
	<link rel="stylesheet" href="{'styles/admin.css'|static:admin}" />
{/block}

{block name='content'}
	<h1>Admin</h1>
	<ol class="modules">
	{foreach $modules as $m}
		<li>
			{assign var=url value={$m->url(UserSession::get()->user())}}
			<a href="{$url}">
				<h2>{$m->name}</h2>
				<img src="{$m->image}" alt="" />
			</a>
		</li>
	{/foreach}
	</ol>
{/block}
