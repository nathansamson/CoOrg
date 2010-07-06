{block name="title"}{'Blog'|_}{/block}

{block name="content"}
	{if Acl::isAllowed(UserSession::get()->username, 'blog-writer')}
	<div class="page-actions">
		{a request="blog/create"}{'New blog'|_}{/a} |
		{a request="admin/blog"}{'Admin'|_}{/a}
	</div>
	{/if}
	<h1>{'Blog'|_}</h1>
	{include file="list.html.tpl"}
{/block}
