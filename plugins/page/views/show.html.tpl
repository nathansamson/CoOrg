{block name="title"}{$page->title}{/block}

{block name="content"}
	{if Acl::isAllowed(UserSession::get()->username, 'admin-page-edit')}
		<span class="page-actions">
			<a href="{url request="admin/page/edit" page=$page->ID}">
				<img src="{'images/icons/edit.png'|static}" />
			</a>
		</span>
	{/if}
	<h1>{$page->title}</h1>
	{$page->content}
{/block}
