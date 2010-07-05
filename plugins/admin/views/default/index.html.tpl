{block name='title'}Admin{/block}

{block name='content'}
	{stylesheet file={'styles/admin.css'|static:'admin'}}
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
